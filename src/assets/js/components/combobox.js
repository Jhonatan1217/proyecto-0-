/**
 * Componente Combobox reutilizable con posicionamiento dinámico (dropup)
 * y Empty State según Design System (DESIGN_SYSTEM.md).
 * Usado por gestionZonas, gestionGrupos y filtros de área.
 */
(function (global) {
  const DROPDOWN_MAX_ITEMS = 5;
  const ITEM_HEIGHT_REM = 2.5;
  const MARGIN = 8;

  const EMPTY_PLACEHOLDER = 'Sin registros disponibles';
  const EMPTY_DROPDOWN_MESSAGE = 'No se encontraron opciones.';

  const MODAL_IDS = '#modalRae,#modalCompetency,#modalProgram,#modalEditarHorario';
  const TABLE_OR_MODAL_SELECTOR = 'table,[id*="wrapTabla"],.modal-usuario-box,.modal-grupo-box,.modal-zona-box,.modal-area-box,.modal-trimestre-box,.modal-enterprise-box,#modalProgram,#modalCompetency,#modalRae,#modalCrearLanding,#modalEditarHorario';

  function cbFireChange(select) {
    select.dispatchEvent(new Event('change', { bubbles: true }));
  }

  function cbFocusInput(input) {
    setTimeout(() => { try { input.focus({ preventScroll: true }); } catch (_) { input.focus(); } }, 0);
  }

  function optionsFromSelect(select) {
    return [...select.options].filter(o => !o.disabled).map(o => ({ value: o.value, text: (o.textContent || '').trim() }));
  }

  function applySrOnly(select) {
    select.classList.add('sr-only');
    select.style.cssText = 'position:absolute;width:1px;height:1px;margin:-1px;padding:0;border:0;clip:rect(0,0,0,0);';
  }

  function appendEmptyDropdownMessage(dropdown, optionClass) {
    const msg = document.createElement('div');
    msg.className = optionClass + ' combobox-empty-message';
    msg.textContent = EMPTY_DROPDOWN_MESSAGE;
    dropdown.appendChild(msg);
  }

  /** Mismo orden que el antiguo closeAllDropdowns (closeAll / reset global). */
  function cbResetDropdownNode(d) {
    const w = d._cbWrapper;
    d.classList.add('hidden');
    d.classList.remove('dropdown-over-table', 'dropdown-up');
    d.style.cssText = '';
    if (w) {
      w.classList.remove('cb-dropdown-open');
      if (d.parentNode === document.body) w.appendChild(d);
    }
  }

  /** Mismo orden que el listener de clic en documento (fuera del combobox). */
  function cbResetDropdownOutsideClick(d, w) {
    d.classList.add('hidden');
    w.classList.remove('cb-dropdown-open');
    if (d.parentNode === document.body && w) w.appendChild(d);
    d.style.cssText = '';
    d.classList.remove('dropdown-over-table', 'dropdown-up');
  }

  function applyDropdownPosition(wrapper, dropdown, triggerEl, dropdownMaxH, forceDropup) {
    const rect = triggerEl.getBoundingClientRect();
    const rem = parseFloat(getComputedStyle(document.documentElement).fontSize);
    const maxH = dropdownMaxH || DROPDOWN_MAX_ITEMS * ITEM_HEIGHT_REM * rem;
    const spaceBelow = window.innerHeight - rect.bottom;
    const spaceAbove = rect.top;
    const tbody = wrapper.closest('tbody');
    const tr = wrapper.closest('tr');
    const isLastRow = tbody && tr && tbody.lastElementChild === tr;
    const inBottomThird = rect.top >= window.innerHeight * (2 / 3);
    const inAcademicosModal = !!(wrapper.closest && wrapper.closest(MODAL_IDS));
    const needsUp = !!forceDropup || spaceBelow < maxH + MARGIN || (!inAcademicosModal && (isLastRow || inBottomThird));

    dropdown.classList.toggle('dropdown-up', needsUp);
    dropdown.classList.add('dropdown-over-table');
    const panelW = Math.min(rect.width, window.innerWidth - 2 * MARGIN);
    let leftPx = rect.left;
    if (leftPx + panelW > window.innerWidth - MARGIN) leftPx = Math.max(MARGIN, window.innerWidth - MARGIN - panelW);
    if (leftPx < MARGIN) leftPx = MARGIN;
    dropdown.style.minWidth = panelW + 'px';
    dropdown.style.maxWidth = panelW + 'px';
    dropdown.style.boxSizing = 'border-box';
    dropdown.style.maxHeight = maxH + 'px';
    dropdown.style.position = 'fixed';
    dropdown.style.zIndex = wrapper.closest('#modalEditarHorario') ? '1000000' : '9999';
    dropdown.style.left = leftPx + 'px';
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
    document.querySelectorAll(sel || '.combobox-dropdown').forEach(cbResetDropdownNode);
  }

  function isInTableOrModal(el) {
    if (!el) return false;
    return !!el.closest(TABLE_OR_MODAL_SELECTOR);
  }

  function ensureComboboxOutsideClick() {
    if (global._comboboxDocClick) return;
    global._comboboxDocClick = true;
    document.addEventListener('click', (e) => {
      document.querySelectorAll('.combobox-dropdown').forEach(d => {
        if (d.classList.contains('hidden')) return;
        const w = d._cbWrapper;
        if (!w || w.contains(e.target) || d.contains(e.target)) return;
        if (d._cbJustOpened && (Date.now() - d._cbJustOpened) < 250) return;
        cbResetDropdownOutsideClick(d, w);
        const inp = w.querySelector('.combobox-input');
        if (inp && typeof w._cbValidateAndResetOnBlur === 'function') w._cbValidateAndResetOnBlur();
        else if (typeof w._cbUpdateInput === 'function') w._cbUpdateInput();
      });
    }, true);
  }

  function openDropdownFixed(wrapper, dropdown, triggerEl, maxH, forceDropup, onOpen) {
    closeAllDropdowns();
    dropdown.style.cssText = 'position:fixed;visibility:hidden;top:-9999px;left:0;display:block;';
    document.body.appendChild(dropdown);
    requestAnimationFrame(() => {
      requestAnimationFrame(() => {
        applyDropdownPosition(wrapper, dropdown, triggerEl, maxH, forceDropup);
        dropdown.style.visibility = 'visible';
        dropdown.classList.remove('hidden');
        dropdown._cbJustOpened = Date.now();
        onOpen();
      });
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
   * @param {boolean} [opts.forceDropup] - Si true, el listado abre siempre hacia arriba (útil al final de un modal).
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
    const forceDropup = opts.forceDropup === true;
    const maxItems =
      opts.maxDropdownItems != null && Number(opts.maxDropdownItems) > 0
        ? Number(opts.maxDropdownItems)
        : DROPDOWN_MAX_ITEMS;
    const isClearVal = (val) => clearValue !== undefined && clearValue !== null && String(val) === String(clearValue);

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

      [' .select-zona-chevron', ' .filtro-area-chevron', ' .select-grupo-chevron', ' .select-usuario-chevron'].forEach(s => {
        container.querySelectorAll(s).forEach(el => { el.style.display = 'none'; });
      });

      const triggerWrap = document.createElement('div');
      triggerWrap.className = 'combobox-trigger w-full border border-gray-300 rounded-xl bg-white py-2.5 text-sm';

      const input = document.createElement('input');
      input.type = 'text';
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

      const optionsData = () => optionsFromSelect(select);
      const hasEmptyOption = () => [...select.options].some(o => String(o.value) === '');

      function setEmptyState(isEmpty) {
        wrapper.classList.toggle('combobox-empty', !!isEmpty);
        input.readOnly = !!isEmpty;
        input.disabled = !!isEmpty;
        input.placeholder = isEmpty ? EMPTY_PLACEHOLDER : placeholder;
        if (isEmpty) {
          input.value = '';
          btnClear.classList.remove('visible');
          btnClear.disabled = true;
        } else {
          btnClear.disabled = false;
        }
      }

      function getOptionByText(exactText) {
        const opts = optionsData();
        const t = (exactText || '').trim();
        if (!t) return null;
        const exact = opts.find(o => o.text === t);
        if (exact) return exact;
        const singleStarts = opts.filter(o => o.text.toLowerCase().startsWith(t.toLowerCase()));
        return singleStarts.length === 1 ? singleStarts[0] : null;
      }

      function storeLastValid() {
        const val = select.value;
        const opt = select.options[select.selectedIndex];
        const text = opt ? (opt.textContent || '').trim() : '';
        wrapper._cbLastValidValue = val;
        wrapper._cbLastValidText = isClearVal(val) ? '' : text;
      }

      function validateAndResetOnClose() {
        const typed = (input.value || '').trim();
        const opts = optionsData();
        const lastVal = wrapper._cbLastValidValue;
        const beforeClear = wrapper._cbBeforeClearValue;
        const matched = getOptionByText(typed);
        if (typed === '') {
          if (restoreValueOnBlurWhenEmpty && beforeClear !== undefined && beforeClear !== null) {
            select.value = beforeClear;
            wrapper._cbBeforeClearValue = undefined;
            cbFireChange(select);
          } else if (clearValue !== undefined && clearValue !== null) {
            select.value = clearValue;
            wrapper._cbBeforeClearValue = undefined;
            cbFireChange(select);
          } else {
            if (lastVal !== undefined && lastVal !== null) select.value = lastVal;
            else if (opts.length) select.value = opts[0].value;
            cbFireChange(select);
          }
          updateInputFromSelect();
          storeLastValid();
          return;
        }
        if (matched) {
          select.value = matched.value;
          cbFireChange(select);
          updateInputFromSelect();
          storeLastValid();
          return;
        }
        const doReset = () => {
          if (lastVal !== undefined && lastVal !== null) select.value = lastVal;
          else if (opts.length) select.value = opts[0].value;
          cbFireChange(select);
          updateInputFromSelect();
          storeLastValid();
        };
        input.style.transition = 'opacity 0.12s ease';
        input.style.opacity = '0';
        setTimeout(() => { doReset(); input.style.opacity = '1'; }, 120);
      }

      function renderOptions(filterText) {
        const all = optionsData();
        const q = (filterText || '').trim().toLowerCase();
        dropdown.innerHTML = '';

        if (!all.length) {
          setEmptyState(true);
          appendEmptyDropdownMessage(dropdown, optionClass);
          dropdown.classList.remove('hidden');
          wrapper.classList.add('cb-dropdown-open');
          return;
        }

        setEmptyState(false);

        all.forEach(({ value, text }) => {
          if (q && !text.toLowerCase().includes(q)) return;
          const div = document.createElement('div');
          div.className = optionClass + (value === select.value ? ' selected' : '');
          div.textContent = text;
          div.dataset.value = value;
          div.setAttribute('role', 'option');
          div.addEventListener('click', (e) => {
            e.stopPropagation();
            wrapper._cbOptionJustSelected = true;
            wrapper._cbClearedAt = 0;
            dropdown.classList.add('hidden');
            dropdown.classList.remove('dropdown-over-table', 'dropdown-up');
            wrapper.classList.remove('cb-dropdown-open');
            if (dropdown.parentNode === document.body) wrapper.appendChild(dropdown);
            dropdown.style.cssText = '';
            select.value = value;
            cbFireChange(select);
            input.value = isClearVal(value) ? '' : text;
            toggleClearVisibility();
            storeLastValid();
            setTimeout(() => { wrapper._cbOptionJustSelected = false; }, 150);
          });
          dropdown.appendChild(div);
        });

        if (!dropdown.children.length) appendEmptyDropdownMessage(dropdown, optionClass);
        dropdown.classList.remove('hidden');
      }

      function toggleClearVisibility() {
        const hasText = (input.value || '').trim().length > 0;
        const showClear = allowClear && hasText;
        wrapper.classList.toggle('has-value', showClear);
        btnClear.classList.toggle('visible', showClear);
      }

      function updateInputFromSelect() {
        const val = select.value;
        if (val === '' || isClearVal(val)) input.value = '';
        else {
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

          if (restoreValueOnBlurWhenEmpty && wrapper._cbClearedAt && (Date.now() - wrapper._cbClearedAt) < 400) {
            if (hasInitial) {
              wrapper._cbClearedAt = 0;
              return;
            }
            wrapper._cbClearedAt = 0;
            if (clearValue !== undefined && clearValue !== null) select.value = clearValue;
            else if (opts.length) select.value = opts[0].value;
            cbFireChange(select);
            updateInputFromSelect();
            storeLastValid();
            return;
          }

          if (hasInitial) {
            const optInit = opts.find(o => String(o.value) === String(initialVal));
            if (optInit) {
              select.value = optInit.value;
              cbFireChange(select);
              updateInputFromSelect();
              storeLastValid();
              return;
            }
          }

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
          cbFireChange(select);
          updateInputFromSelect();
          storeLastValid();
          return;
        }
        const exact = opts.find(o => (o.text || '').trim() === typed);
        const single = opts.filter(o => (o.text || '').toLowerCase().startsWith(typed.toLowerCase()));
        const matched = exact || (single.length === 1 ? single[0] : null);
        if (matched) {
          select.value = matched.value;
          cbFireChange(select);
          input.value = isClearVal(matched.value) ? '' : matched.text;
          toggleClearVisibility();
          storeLastValid();
          return;
        }
        if (lastVal !== undefined && lastVal !== null) select.value = lastVal;
        else if (opts.length) select.value = opts[0].value;
        cbFireChange(select);
        updateInputFromSelect();
        storeLastValid();
      }

      updateInputFromSelect();
      storeLastValid();
      wrapper._cbUpdateInput = updateInputFromSelect;
      wrapper._cbValidateAndResetOnBlur = validateAndResetOnBlur;

      applySrOnly(select);
      wrapper.appendChild(triggerWrap);
      wrapper.appendChild(dropdown);

      const useFixedDropdown = isInTableOrModal(wrapper);
      const maxH = maxItems * ITEM_HEIGHT_REM * parseFloat(getComputedStyle(document.documentElement).fontSize);

      function closeDropdownLocal() {
        dropdown.classList.add('hidden');
        dropdown.style.cssText = '';
        wrapper.classList.remove('cb-dropdown-open');
        dropdown.classList.remove('dropdown-over-table', 'dropdown-up');
        if (dropdown.parentNode === document.body && wrapper) wrapper.appendChild(dropdown);
      }

      function applyNativeDisabledState() {
        const off = select.disabled;
        wrapper.classList.toggle('combobox-native-disabled', off);
        triggerWrap.setAttribute('aria-disabled', off ? 'true' : 'false');
        if (off) {
          closeDropdownLocal();
          input.disabled = true;
          input.readOnly = true;
          input.setAttribute('tabindex', '-1');
          btnClear.disabled = true;
          btnClear.classList.remove('visible');
          wrapper.classList.remove('has-value');
        } else {
          input.removeAttribute('tabindex');
          const all = optionsData();
          if (!all.length) setEmptyState(true);
          else {
            setEmptyState(false);
            input.disabled = false;
            input.readOnly = false;
            btnClear.disabled = false;
          }
          updateInputFromSelect();
          toggleClearVisibility();
          storeLastValid();
        }
      }

      function positionAndShow(forceShowAll) {
        if (select.disabled) return;
        storeLastValid();
        renderOptions(forceShowAll ? '' : input.value);
        if (dropdown.children.length === 0) return;

        const markOpen = () => { wrapper.classList.add('cb-dropdown-open'); };

        if (useFixedDropdown) {
          openDropdownFixed(wrapper, dropdown, triggerWrap, maxH, forceDropup, markOpen);
        } else {
          dropdown.style.maxHeight = maxH + 'px';
          dropdown.classList.remove('hidden');
          dropdown._cbJustOpened = Date.now();
          markOpen();
        }
      }

      function openFromTrigger(ev) {
        if (select.disabled) return;
        if (ev) { ev.preventDefault(); ev.stopPropagation(); }
        if (dropdown.classList.contains('hidden')) {
          positionAndShow();
          cbFocusInput(input);
        }
      }
      wrapper._cbOpen = openFromTrigger;

      triggerWrap.addEventListener('mousedown', (e) => {
        if (select.disabled) return;
        if (e.target !== input) openFromTrigger(e);
      });
      triggerWrap.addEventListener('click', (e) => { e.preventDefault(); e.stopPropagation(); });
      input.addEventListener('focus', (e) => {
        if (select.disabled) {
          try { input.blur(); } catch (_) {}
          e?.preventDefault?.();
          return;
        }
        closeAllDropdowns();
        positionAndShow();
        e?.preventDefault?.();
      });
      input.addEventListener('blur', () => {
        setTimeout(() => {
          if (dropdown.contains(document.activeElement)) return;
          if (wrapper._cbOptionJustSelected) return;
          if (!dropdown.classList.contains('hidden')) {
            dropdown.classList.add('hidden');
            dropdown.style.cssText = '';
            wrapper.classList.remove('cb-dropdown-open');
            if (dropdown.parentNode === document.body && wrapper) wrapper.appendChild(dropdown);
          }
          if (typeof wrapper._cbValidateAndResetOnBlur === 'function') wrapper._cbValidateAndResetOnBlur();
          if (typeof wrapper._cbValidateAndResetOnClose === 'function') wrapper._cbValidateAndResetOnClose();
        }, 120);
      });
      input.addEventListener('input', () => {
        if (select.disabled) return;
        renderOptions(input.value);
        toggleClearVisibility();
      });
      input.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
          dropdown.classList.add('hidden');
          dropdown.style.cssText = '';
          wrapper.classList.remove('cb-dropdown-open');
          if (dropdown.parentNode === document.body) wrapper.appendChild(dropdown);
          if (typeof wrapper._cbValidateAndResetOnClose === 'function') wrapper._cbValidateAndResetOnClose();
          input.blur();
        }
      });

      btnClear.addEventListener('click', (e) => {
        if (select.disabled) return;
        e.stopPropagation();
        e.preventDefault();
        wrapper._cbBeforeClearValue = select.value;

        const hasInitial = !!(wrapper.dataset.initialValue || '').trim();
        if (restoreValueOnBlurWhenEmpty && hasInitial) {
          wrapper._cbClearedAt = Date.now();
          input.value = '';
          toggleClearVisibility();
          dropdown.classList.add('hidden');
          if (dropdown.parentNode === document.body && wrapper) wrapper.appendChild(dropdown);
          positionAndShow(true);
          cbFocusInput(input);
          return;
        }

        if (restoreValueOnBlurWhenEmpty) wrapper._cbClearedAt = Date.now();
        input.value = '';
        if (clearValue !== undefined && clearValue !== null) {
          select.value = clearValue;
          cbFireChange(select);
        } else if (hasEmptyOption()) {
          select.value = '';
          cbFireChange(select);
        } else {
          input.value = '';
          toggleClearVisibility();
          dropdown.classList.add('hidden');
          if (dropdown.parentNode === document.body) wrapper.appendChild(dropdown);
          dropdown.style.cssText = '';
          positionAndShow(true);
          cbFocusInput(input);
          return;
        }
        dropdown.classList.add('hidden');
        if (dropdown.parentNode === document.body) wrapper.appendChild(dropdown);
        dropdown.style.cssText = '';
        updateInputFromSelect();
        toggleClearVisibility();
        positionAndShow(true);
        cbFocusInput(input);
      });

      select.addEventListener('change', () => {
        updateInputFromSelect();
        applyNativeDisabledState();
      });

      try {
        const mo = new MutationObserver(() => applyNativeDisabledState());
        mo.observe(select, { attributes: true, attributeFilter: ['disabled'] });
      } catch (_) {}

      applyNativeDisabledState();

      if (opts.onEnhance) opts.onEnhance(select, wrapper);
    });
  }

  /**
   * Mejora un <select> a desplegable custom: mismo diseño y dropup que el combobox,
   * sin búsqueda ni botón X. Para listas fijas (jornada, modalidad, cargo, etc.).
   * @param {boolean} [opts.forceDropup] - Si true, fuerza apertura hacia arriba cuando usa posición fija.
   * @param {string|string[]} [opts.placeholderValues] - Valores del select que se muestran en gris (ej. '' y 'all' para filtros).
   * @param {number} [opts.maxDropdownItems] - Máximo de filas visibles en el panel (por defecto 5).
   * @param {boolean} [opts.allowClear] - Si true, muestra botón X para volver a la opción vacía (placeholder).
   */
  function enhanceSelectStyled(opts) {
    const selector = opts.selector || '.select-styled';
    const dropdownClass = opts.dropdownClass || 'custom-select-dropdown';
    const optionClass = opts.optionClass || 'custom-option';
    const placeholder = (opts.placeholder != null && opts.placeholder !== '') ? opts.placeholder : 'Seleccione...';
    const forceDropup = opts.forceDropup === true;
    const allowClear = opts.allowClear === true;
    const rawNeutral = opts.placeholderValues;
    const neutralValues = rawNeutral != null && rawNeutral !== ''
      ? (Array.isArray(rawNeutral) ? rawNeutral : [rawNeutral]).map(v => String(v))
      : [''];

    document.querySelectorAll(selector).forEach(select => {
      if (select.dataset.comboboxEnhanced === '1') return;
      select.dataset.comboboxEnhanced = '1';

      const maxItems =
        opts.maxDropdownItems != null && Number(opts.maxDropdownItems) > 0
          ? Number(opts.maxDropdownItems)
          : DROPDOWN_MAX_ITEMS;
      const remPx = parseFloat(getComputedStyle(document.documentElement).fontSize);
      const maxH = maxItems * ITEM_HEIGHT_REM * remPx;

      const container = select.parentNode;
      const wrapper = document.createElement('div');
      wrapper.className = 'combobox-wrapper select-styled-wrapper';
      container.insertBefore(wrapper, select);
      wrapper.appendChild(select);

      container.querySelectorAll('.select-grupo-chevron, .select-usuario-chevron').forEach(el => { el.style.display = 'none'; });

      const triggerWrap = document.createElement('div');
      triggerWrap.className = 'combobox-trigger w-full border border-gray-300 rounded-xl bg-white hover:border-gray-400 focus-within:ring-2 focus-within:ring-[#39A900]/20 focus-within:border-[#39A900] py-2.5 text-sm';
      triggerWrap.setAttribute('tabindex', '0');
      const triggerText = document.createElement('span');
      triggerText.className = 'select-styled-trigger-text';
      triggerText.style.cssText = 'padding-left:0.75rem;flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;text-align:left';
      let btnClearStyled = null;
      const chevron = document.createElement('span');
      chevron.className = 'chevron-combobox';
      chevron.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>';
      triggerWrap.appendChild(triggerText);
      if (allowClear) {
        btnClearStyled = document.createElement('button');
        btnClearStyled.type = 'button';
        btnClearStyled.className = 'btn-clear-combobox';
        btnClearStyled.setAttribute('aria-label', 'Limpiar');
        btnClearStyled.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
        triggerWrap.appendChild(btnClearStyled);
      }
      triggerWrap.appendChild(chevron);

      const dropdown = document.createElement('div');
      dropdown.className = 'combobox-dropdown ' + dropdownClass + ' hidden';
      dropdown.setAttribute('role', 'listbox');
      dropdown._cbWrapper = wrapper;

      const optionsData = () => optionsFromSelect(select);

      function updateTriggerText() {
        const opt = select.options[select.selectedIndex];
        const displayText = opt ? (opt.textContent || '').trim() : '';
        const val = String(select.value ?? '');
        const isNeutral = neutralValues.includes(val);
        triggerText.textContent = displayText || placeholder;
        triggerText.style.color = isNeutral ? '#9ca3af' : '#111827';
        if (allowClear && btnClearStyled) {
          const showClear = !isNeutral;
          btnClearStyled.classList.toggle('visible', showClear);
          wrapper.classList.toggle('has-value', showClear);
        }
      }

      function renderOptions() {
        dropdown.innerHTML = '';
        optionsData().forEach(({ value, text }) => {
          const div = document.createElement('div');
          div.className = optionClass + (value === select.value ? ' selected' : '');
          div.textContent = text;
          div.dataset.value = value;
          div.setAttribute('role', 'option');
          div.addEventListener('click', (e) => {
            e.stopPropagation();
            select.value = value;
            cbFireChange(select);
            updateTriggerText();
            dropdown.classList.add('hidden');
            dropdown.classList.remove('dropdown-over-table', 'dropdown-up');
            wrapper.classList.remove('cb-dropdown-open');
            if (dropdown.parentNode === document.body) wrapper.appendChild(dropdown);
            dropdown.style.cssText = '';
          });
          dropdown.appendChild(div);
        });
        dropdown.classList.toggle('hidden', dropdown.children.length === 0);
      }

      updateTriggerText();
      applySrOnly(select);
      wrapper.appendChild(triggerWrap);
      wrapper.appendChild(dropdown);

      const inTable = isInTableOrModal(wrapper);

      function positionAndShow() {
        renderOptions();
        if (dropdown.children.length === 0) return;
        if (inTable) {
          openDropdownFixed(wrapper, dropdown, triggerWrap, maxH, forceDropup, () => {
            wrapper.classList.add('cb-dropdown-open');
          });
        } else {
          dropdown.style.maxHeight = maxH + 'px';
          dropdown.classList.remove('hidden');
          dropdown._cbJustOpened = Date.now();
          wrapper.classList.add('cb-dropdown-open');
        }
      }

      function toggleFromTrigger(ev) {
        if (ev) { ev.preventDefault(); ev.stopPropagation(); }
        if (!dropdown.classList.contains('hidden')) {
          dropdown.classList.add('hidden');
          wrapper.classList.remove('cb-dropdown-open');
          dropdown.classList.remove('dropdown-over-table', 'dropdown-up');
          if (dropdown.parentNode === document.body) wrapper.appendChild(dropdown);
          dropdown.style.cssText = '';
          wrapper._cbSkipToggleFocusOpen = true;
          setTimeout(() => {
            if (wrapper._cbSkipToggleFocusOpen) wrapper._cbSkipToggleFocusOpen = false;
          }, 0);
          return;
        }
        positionAndShow();
      }

      wrapper._cbUpdateInput = updateTriggerText;

      triggerWrap.addEventListener('mousedown', (e) => {
        if (allowClear && e.target.closest && e.target.closest('.btn-clear-combobox')) return;
        e.preventDefault();
        toggleFromTrigger(e);
      });
      triggerWrap.addEventListener('click', (e) => { e.preventDefault(); e.stopPropagation(); });
      if (allowClear && btnClearStyled) {
        btnClearStyled.addEventListener('click', (e) => {
          e.preventDefault();
          e.stopPropagation();
          const emptyOpt = [...select.options].find((o) => neutralValues.includes(String(o.value)));
          if (emptyOpt) select.value = emptyOpt.value;
          else if (select.options.length) select.value = select.options[0].value;
          cbFireChange(select);
          updateTriggerText();
          dropdown.classList.add('hidden');
          dropdown.classList.remove('dropdown-over-table', 'dropdown-up');
          wrapper.classList.remove('cb-dropdown-open');
          if (dropdown.parentNode === document.body) wrapper.appendChild(dropdown);
          dropdown.style.cssText = '';
        });
      }
      triggerWrap.addEventListener('focus', () => {
        if (wrapper._cbSkipToggleFocusOpen) {
          wrapper._cbSkipToggleFocusOpen = false;
          return;
        }
        closeAllDropdowns();
        positionAndShow();
      });
      triggerWrap.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
          dropdown.classList.add('hidden');
          wrapper.classList.remove('cb-dropdown-open');
          if (dropdown.parentNode === document.body) wrapper.appendChild(dropdown);
          dropdown.style.cssText = '';
          triggerWrap.blur();
        }
      });

      select.addEventListener('change', updateTriggerText);
    });
  }

  ensureComboboxOutsideClick();

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
    enhanceSelectStyled: enhanceSelectStyled,
    reset: resetComboboxes,
    closeAll: closeAllDropdowns,
    setInitialValue: setInitialValue,
    clearInitialValue: clearInitialValue
  };
})(typeof window !== 'undefined' ? window : this);
