/**
 * Componente Combobox reutilizable con posicionamiento dinámico (dropup)
 * para evitar que el dropdown se solape con el footer en la última fila.
 * Usado por gestionZonas, gestionGrupos y filtros de área.
 */
(function (global) {
  const DROPDOWN_MAX_ITEMS = 5;
  const ITEM_HEIGHT_REM = 2.5;
  const MARGIN = 8;

  function applyDropdownPosition(wrapper, dropdown, triggerEl, dropdownMaxH) {
    const rect = triggerEl.getBoundingClientRect();
    const rem = parseFloat(getComputedStyle(document.documentElement).fontSize);
    const maxH = dropdownMaxH || DROPDOWN_MAX_ITEMS * ITEM_HEIGHT_REM * rem;
    const spaceBelow = window.innerHeight - rect.bottom;
    const spaceAbove = rect.top;
    const tbody = wrapper.closest('tbody');
    const tr = wrapper.closest('tr');
    const isLastRow = tbody && tr && tbody.lastElementChild === tr;
    const inBottomThird = rect.top >= window.innerHeight * (2 / 3);
    const needsUp = spaceBelow < maxH + MARGIN || isLastRow || inBottomThird;

    dropdown.classList.toggle('dropdown-up', needsUp);
    dropdown.classList.add('dropdown-over-table');
    dropdown.style.minWidth = rect.width + 'px';
    dropdown.style.maxHeight = maxH + 'px';
    dropdown.style.position = 'fixed';
    dropdown.style.zIndex = '9999';
    dropdown.style.left = rect.left + 'px';
    dropdown.style.marginTop = '';
    dropdown.style.marginBottom = '';

    if (needsUp) {
      const upMaxH = Math.min(maxH, Math.max(60, spaceAbove - MARGIN));
      dropdown.style.maxHeight = upMaxH + 'px';
      dropdown.style.top = '';
      dropdown.style.bottom = (window.innerHeight - rect.top + MARGIN) + 'px';
    } else {
      const downMaxH = Math.min(maxH, Math.max(60, spaceBelow - MARGIN));
      dropdown.style.maxHeight = downMaxH + 'px';
      dropdown.style.bottom = '';
      dropdown.style.top = (rect.bottom + MARGIN) + 'px';
    }
  }

  function closeAllDropdowns(sel) {
    const selector = sel || '.combobox-dropdown';
    document.querySelectorAll(selector).forEach(d => {
      d.classList.add('hidden');
      d.classList.remove('dropdown-over-table', 'dropdown-up');
      d.style.cssText = '';
      if (d._cbWrapper && d.parentNode === document.body) d._cbWrapper.appendChild(d);
    });
  }

  /**
   * Mejora un <select> a combobox con búsqueda y dropup.
   * @param {Object} opts
   * @param {string} opts.selector - Selector CSS (ej: '.select-zona')
   * @param {string} opts.dropdownClass - Clase del dropdown
   * @param {string} opts.optionClass - Clase de cada opción
   * @param {string} opts.placeholder - Placeholder del input
   * @param {string} [opts.clearValue] - Valor para "limpiar" (ej: 'todas', '')
   * @param {boolean} [opts.allowClear] - Si false, oculta la X y mantiene el chevron visible
   * @param {boolean} [opts.restoreValueOnBlurWhenEmpty] - Si true (modal/fila editar): al salir con input vacío restaura último valor. Si false (filtros): se queda en placeholder.
   * @param {boolean} [opts.inTable] - Si está dentro de tabla (usa position: fixed + dropup)
   * @param {Function} [opts.onEnhance] - Callback cuando se enhancea cada select
   */
  function enhanceCombobox(opts) {
    const selector = opts.selector || '.select-zona';
    const dropdownClass = opts.dropdownClass || 'custom-select-dropdown';
    const optionClass = opts.optionClass || 'custom-option';
    const placeholder = opts.placeholder || 'Buscar...';
    const clearValue = opts.clearValue;
    const allowClear = opts.allowClear !== false;
    const restoreValueOnBlurWhenEmpty = opts.restoreValueOnBlurWhenEmpty !== false;
    const isInTable = (el) => el.closest('table') || el.closest('[id*="wrapTabla"]');

    document.querySelectorAll(selector).forEach(select => {
      if (select.dataset.comboboxEnhanced === '1') return;
      select.dataset.comboboxEnhanced = '1';

      const container = select.parentNode;
      const wrapper = document.createElement('div');
      wrapper.className = 'combobox-wrapper';
      container.insertBefore(wrapper, select);
      wrapper.appendChild(select);

      var initialFromAttr = select.getAttribute('data-initial-value');
      wrapper.dataset.initialValue = (initialFromAttr != null ? initialFromAttr : '').trim();

      [' .select-zona-chevron', ' .filtro-area-chevron', ' .select-grupo-chevron', ' .select-usuario-chevron' ].forEach(s => {
        container.querySelectorAll(s).forEach(el => { el.style.display = 'none'; });
      });

      const triggerWrap = document.createElement('div');
      triggerWrap.className = 'combobox-trigger w-full border border-gray-300 rounded-xl bg-white hover:border-gray-400 focus-within:ring-2 focus-within:ring-[#39A900]/20 focus-within:border-[#39A900] py-2.5 text-sm';

      const input = document.createElement('input');
      input.type = 'text';
      /* Evitar autocompletado del navegador: one-time-code para que no muestre sugerencias (algunos ignoran "off") */
      input.setAttribute('autocomplete', 'one-time-code');
      input.className = 'combobox-input w-full bg-transparent py-0 border-0 focus:ring-0 text-gray-900 placeholder:text-gray-400';
      input.placeholder = placeholder;

      const btnClear = document.createElement('button');
      btnClear.type = 'button';
      btnClear.className = 'btn-clear-combobox';
      btnClear.setAttribute('aria-label', 'Limpiar');
      btnClear.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';

      const chevron = document.createElement('span');
      chevron.className = 'chevron-combobox';
      chevron.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>';

      triggerWrap.appendChild(input);
      triggerWrap.appendChild(btnClear);
      triggerWrap.appendChild(chevron);

      const dropdown = document.createElement('div');
      dropdown.className = 'combobox-dropdown ' + dropdownClass + ' hidden';
      dropdown.setAttribute('role', 'listbox');
      dropdown._cbWrapper = wrapper;

      const optionsData = () => [...select.options].filter(o => !o.disabled).map(o => ({ value: o.value, text: (o.textContent || '').trim() }));

      function renderOptions(filterText) {
        const q = (filterText || '').trim().toLowerCase();
        dropdown.innerHTML = '';
        optionsData().forEach(({ value, text }) => {
          if (q && !text.toLowerCase().includes(q)) return;
          const div = document.createElement('div');
          div.className = optionClass + (value === select.value ? ' selected' : '');
          div.textContent = text;
          div.dataset.value = value;
          div.setAttribute('role', 'option');
          div.addEventListener('click', (e) => {
            e.stopPropagation();
            // #region agent log
            fetch('http://127.0.0.1:7412/ingest/d3fa9f59-7c34-4dda-a4b8-3b180194f8ae',{method:'POST',headers:{'Content-Type':'application/json','X-Debug-Session-Id':'96d9bc'},body:JSON.stringify({sessionId:'96d9bc',runId:'select-check',hypothesisId:'H2',location:'combobox.js:optionClick',message:'option clicked',data:{selector,allowClear,value,text,inBody:dropdown.parentNode===document.body},timestamp:Date.now()})}).catch(()=>{});
            // #endregion
            wrapper._cbOptionJustSelected = true;
            wrapper._cbClearedAt = 0;
            dropdown.classList.add('hidden');
            dropdown.classList.remove('dropdown-over-table', 'dropdown-up');
            if (dropdown.parentNode === document.body) wrapper.appendChild(dropdown);
            dropdown.style.cssText = '';
            select.value = value;
            select.dispatchEvent(new Event('change', { bubbles: true }));
            input.value = (clearValue !== undefined && clearValue !== null && String(value) === String(clearValue)) ? '' : text;
            toggleClearVisibility();
            storeLastValid();
            setTimeout(() => { wrapper._cbOptionJustSelected = false; }, 150);
          });
          dropdown.appendChild(div);
        });
        dropdown.classList.toggle('hidden', dropdown.children.length === 0);
      }

      function toggleClearVisibility() {
        const hasText = (input.value || '').trim().length > 0;
        const showClear = allowClear && hasText;
        wrapper.classList.toggle('has-value', showClear);
        btnClear.classList.toggle('visible', showClear);
      }

      function storeLastValid() {
        const val = select.value;
        const opt = select.options[select.selectedIndex];
        const text = opt ? (opt.textContent || '').trim() : '';
        wrapper._cbLastValidValue = val;
        wrapper._cbLastValidText = (clearValue !== undefined && clearValue !== null && String(val) === String(clearValue)) ? '' : text;
      }

      function updateInputFromSelect() {
        if (clearValue !== undefined && clearValue !== null && String(select.value) === String(clearValue)) {
          input.value = '';
        } else {
          const opt = select.options[select.selectedIndex];
          input.value = opt ? (opt.textContent || '').trim() : '';
        }
        toggleClearVisibility();
      }

      function validateAndResetOnBlur() {
        const typed = (input.value || '').trim();
        const opts = optionsData();
        const lastVal = wrapper._cbLastValidValue;
        const beforeClear = wrapper._cbBeforeClearValue;
        const initialVal = (wrapper.dataset.initialValue || '').trim();
        const hasInitial = !!initialVal;

        if (typed === '') {
          wrapper._cbBeforeClearValue = undefined;

          // Caso especial: blur inmediatamente después de pulsar X
          if (restoreValueOnBlurWhenEmpty && wrapper._cbClearedAt && (Date.now() - wrapper._cbClearedAt) < 400) {
            if (hasInitial) {
              // En modo Editar (con valor inicial) no tocamos ni el select ni el input:
              // mantenemos el placeholder visible tras pulsar X.
              wrapper._cbClearedAt = 0;
              return;
            }
            // En Crear/Filtro mantenemos el comportamiento estándar: limpiar selección real.
            wrapper._cbClearedAt = 0;
            if (clearValue !== undefined && clearValue !== null) select.value = clearValue;
            else if (opts.length) select.value = opts[0].value;
            select.dispatchEvent(new Event('change', { bubbles: true }));
            updateInputFromSelect();
            storeLastValid();
            return;
          }

          // Modo Editar: si hay valor inicial, restaurarlo al salir con el input vacío
          if (hasInitial) {
            const optInit = opts.find(o => String(o.value) === String(initialVal));
            if (optInit) {
              select.value = optInit.value;
              select.dispatchEvent(new Event('change', { bubbles: true }));
              updateInputFromSelect();
              storeLastValid();
              return;
            }
          }

          // Resto de casos: fallback estándar
          if (restoreValueOnBlurWhenEmpty) {
            const toRestore = (beforeClear !== undefined && beforeClear !== null) ? beforeClear : lastVal;
            if (toRestore !== undefined && toRestore !== null) select.value = toRestore;
            else if (clearValue !== undefined && clearValue !== null) select.value = clearValue;
            else if (opts.length) select.value = opts[0].value;
          } else {
            if (clearValue !== undefined && clearValue !== null) select.value = clearValue;
            else if (opts.length) select.value = opts[0].value;
          }
          wrapper._cbClearedAt = 0;
          select.dispatchEvent(new Event('change', { bubbles: true }));
          updateInputFromSelect();
          storeLastValid();
          return;
        }
        const exact = opts.find(o => (o.text || '').trim() === typed);
        const single = opts.filter(o => (o.text || '').toLowerCase().startsWith(typed.toLowerCase()));
        const matched = exact || (single.length === 1 ? single[0] : null);
        if (matched) {
          select.value = matched.value;
          select.dispatchEvent(new Event('change', { bubbles: true }));
          input.value = (clearValue !== undefined && clearValue !== null && String(matched.value) === String(clearValue)) ? '' : matched.text;
          toggleClearVisibility();
          storeLastValid();
          return;
        }
        if (lastVal !== undefined && lastVal !== null) select.value = lastVal;
        else if (opts.length) select.value = opts[0].value;
        select.dispatchEvent(new Event('change', { bubbles: true }));
        updateInputFromSelect();
        storeLastValid();
      }

      updateInputFromSelect();
      storeLastValid();
      wrapper._cbUpdateInput = updateInputFromSelect;
      wrapper._cbValidateAndResetOnBlur = validateAndResetOnBlur;

      select.classList.add('sr-only');
      select.style.cssText = 'position:absolute;width:1px;height:1px;margin:-1px;padding:0;border:0;clip:rect(0,0,0,0);';
      wrapper.appendChild(triggerWrap);
      wrapper.appendChild(dropdown);

      // Contextos que usan position:fixed + applyDropdownPosition:
      // 1) dentro de <table> o wrapTabla (filas editables)
      // 2) dentro de modal-*-box que tienen overflow:hidden y recortarían el dropdown
      const inTable = isInTable(wrapper) || !!wrapper.closest(
        '.modal-usuario-box,.modal-grupo-box,.modal-zona-box,.modal-area-box,.modal-trimestre-box,.modal-enterprise-box'
      );
      const maxH = DROPDOWN_MAX_ITEMS * ITEM_HEIGHT_REM * parseFloat(getComputedStyle(document.documentElement).fontSize);

      function positionAndShow(forceShowAll) {
        const filterText = forceShowAll ? '' : input.value;
        renderOptions(filterText);
        if (dropdown.children.length === 0) return;

        if (inTable) {
          closeAllDropdowns();
          // Fijar position:fixed ANTES de appendear al body para que nunca entre en el
          // flujo normal del documento. Sin esto, durante los 2 rAFs el dropdown queda
          // como position:absolute hijo directo de <body>, extiende el alto de la página
          // y hace aparecer/desaparecer la scrollbar de Windows (layout shift).
          dropdown.style.cssText = 'position:fixed;visibility:hidden;top:-9999px;left:0;display:block;';
          document.body.appendChild(dropdown);
          requestAnimationFrame(() => {
            requestAnimationFrame(() => {
              applyDropdownPosition(wrapper, dropdown, triggerWrap, maxH);
              dropdown.style.visibility = 'visible';
              dropdown.classList.remove('hidden');
              dropdown._cbJustOpened = Date.now();
            });
          });
        } else {
          dropdown.style.maxHeight = maxH + 'px';
          dropdown.classList.remove('hidden');
          dropdown._cbJustOpened = Date.now();
        }
      }

      function openFromTrigger(ev) {
        if (ev) { ev.preventDefault(); ev.stopPropagation(); }
        // #region agent log
        fetch('http://127.0.0.1:7412/ingest/d3fa9f59-7c34-4dda-a4b8-3b180194f8ae',{method:'POST',headers:{'Content-Type':'application/json','X-Debug-Session-Id':'96d9bc'},body:JSON.stringify({sessionId:'96d9bc',runId:'select-check',hypothesisId:'H1',location:'combobox.js:openFromTrigger',message:'open requested',data:{selector,allowClear,selectValue:select.value,inputValue:input.value,hidden:dropdown.classList.contains('hidden')},timestamp:Date.now()})}).catch(()=>{});
        // #endregion
        if (dropdown.classList.contains('hidden')) {
          positionAndShow();
          setTimeout(() => { try { input.focus({ preventScroll: true }); } catch (_) { input.focus(); } }, 0);
        }
      }
      wrapper._cbOpen = openFromTrigger;

      triggerWrap.addEventListener('mousedown', (e) => { if (e.target !== input) openFromTrigger(e); });
      triggerWrap.addEventListener('click', (e) => { e.preventDefault(); e.stopPropagation(); });
      input.addEventListener('focus', (e) => {
        closeAllDropdowns();
        positionAndShow();
        e?.preventDefault?.();
      });
      input.addEventListener('blur', () => {
        setTimeout(() => {
          if (dropdown.contains(document.activeElement)) return;
          if (wrapper._cbOptionJustSelected) return;
          // #region agent log
          fetch('http://127.0.0.1:7412/ingest/d3fa9f59-7c34-4dda-a4b8-3b180194f8ae',{method:'POST',headers:{'Content-Type':'application/json','X-Debug-Session-Id':'96d9bc'},body:JSON.stringify({sessionId:'96d9bc',runId:'select-check',hypothesisId:'H3',location:'combobox.js:inputBlur',message:'blur handling',data:{selector,allowClear,inputValue:input.value,selectValue:select.value,dropdownHidden:dropdown.classList.contains('hidden')},timestamp:Date.now()})}).catch(()=>{});
          // #endregion
          if (!dropdown.classList.contains('hidden')) {
            dropdown.classList.add('hidden');
            if (dropdown.parentNode === document.body && wrapper) wrapper.appendChild(dropdown);
          }
          if (typeof wrapper._cbValidateAndResetOnBlur === 'function') wrapper._cbValidateAndResetOnBlur();
        }, 120);
      });
      input.addEventListener('input', () => { renderOptions(input.value); toggleClearVisibility(); });
      input.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
          dropdown.classList.add('hidden');
          if (dropdown.parentNode === document.body) wrapper.appendChild(dropdown);
          input.blur();
        }
      });

      btnClear.addEventListener('click', (e) => {
        e.stopPropagation();
        e.preventDefault();
        wrapper._cbBeforeClearValue = select.value;

        const hasInitial = !!(wrapper.dataset.initialValue || '').trim();
        // Modo Editar con persistencia (ej: editar zona): la X sólo limpia el input y muestra placeholder,
        // sin tocar el value real del select. Si luego sales en blanco, el blur restaurará el initialValue.
        if (restoreValueOnBlurWhenEmpty && hasInitial) {
          wrapper._cbClearedAt = Date.now();
          input.value = '';
          toggleClearVisibility();
          dropdown.classList.add('hidden');
          if (dropdown.parentNode === document.body && wrapper) wrapper.appendChild(dropdown);
          positionAndShow(true);
          setTimeout(() => { try { input.focus({ preventScroll: true }); } catch (_) { input.focus(); } }, 0);
          return;
        }

        // Comportamiento estándar (Crear / Filtros): la X limpia realmente la selección
        if (restoreValueOnBlurWhenEmpty) wrapper._cbClearedAt = Date.now();
        input.value = '';
        if (clearValue !== undefined && clearValue !== null) {
          select.value = clearValue;
          select.dispatchEvent(new Event('change', { bubbles: true }));
        } else {
          const first = Array.from(select.options).find(o => !o.disabled && o.value);
          if (first) { select.value = first.value; select.dispatchEvent(new Event('change', { bubbles: true })); }
        }
        dropdown.classList.add('hidden');
        if (dropdown.parentNode === document.body) wrapper.appendChild(dropdown);
        toggleClearVisibility();
        positionAndShow(true);
        setTimeout(() => { try { input.focus({ preventScroll: true }); } catch (_) { input.focus(); } }, 0);
      });

      select.addEventListener('change', () => {
        // #region agent log
        fetch('http://127.0.0.1:7412/ingest/d3fa9f59-7c34-4dda-a4b8-3b180194f8ae',{method:'POST',headers:{'Content-Type':'application/json','X-Debug-Session-Id':'96d9bc'},body:JSON.stringify({sessionId:'96d9bc',runId:'select-check',hypothesisId:'H4',location:'combobox.js:selectChange',message:'select change',data:{selector,allowClear,newValue:select.value},timestamp:Date.now()})}).catch(()=>{});
        // #endregion
        updateInputFromSelect();
      });

      if (opts.onEnhance) opts.onEnhance(select, wrapper);
    });

    if (!global._comboboxDocClick) {
      global._comboboxDocClick = true;
      document.addEventListener('click', (e) => {
        document.querySelectorAll('.combobox-dropdown').forEach(d => {
          if (d.classList.contains('hidden')) return;
          const w = d._cbWrapper;
          if (!w || w.contains(e.target) || d.contains(e.target)) return;
          if (d._cbJustOpened && (Date.now() - d._cbJustOpened) < 250) return;
          d.classList.add('hidden');
          if (d.parentNode === document.body && w) w.appendChild(d);
          d.style.cssText = '';
          const inp = w?.querySelector('.combobox-input');
          if (inp && typeof w._cbValidateAndResetOnBlur === 'function') w._cbValidateAndResetOnBlur();
          else if (inp && typeof w._cbUpdateInput === 'function') w._cbUpdateInput();
        });
      }, true);
    }
  }

  function resetComboboxes() {
    closeAllDropdowns('.combobox-dropdown');
  }

  function getWrapper(selectOrSelector) {
    var el = typeof selectOrSelector === 'string'
      ? document.querySelector(selectOrSelector)
      : selectOrSelector;
    if (!el) return null;
    return el.closest('.combobox-wrapper') || null;
  }

  function setInitialValue(selectOrSelector, value) {
    var w = getWrapper(selectOrSelector);
    if (w) w.dataset.initialValue = (value != null ? String(value) : '').trim();
  }

  function clearInitialValue(selectOrSelector) {
    setInitialValue(selectOrSelector, '');
  }

  global.ComboboxComponent = {
    enhance: enhanceCombobox,
    reset: resetComboboxes,
    closeAll: closeAllDropdowns,
    setInitialValue: setInitialValue,
    clearInitialValue: clearInitialValue
  };
})(typeof window !== 'undefined' ? window : this);
