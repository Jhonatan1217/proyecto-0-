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
   * @param {string} [opts.clearValue] - Valor para "limpiar" (ej: 'todas')
   * @param {boolean} [opts.inTable] - Si está dentro de tabla (usa position: fixed + dropup)
   * @param {Function} [opts.onEnhance] - Callback cuando se enhancea cada select
   */
  function enhanceCombobox(opts) {
    const selector = opts.selector || '.select-zona';
    const dropdownClass = opts.dropdownClass || 'custom-select-dropdown';
    const optionClass = opts.optionClass || 'custom-option';
    const placeholder = opts.placeholder || 'Buscar...';
    const clearValue = opts.clearValue;
    const isInTable = (el) => el.closest('table') || el.closest('[id*="wrapTabla"]');

    document.querySelectorAll(selector).forEach(select => {
      if (select.dataset.comboboxEnhanced === '1') return;
      select.dataset.comboboxEnhanced = '1';

      const container = select.parentNode;
      const wrapper = document.createElement('div');
      wrapper.className = 'combobox-wrapper';
      container.insertBefore(wrapper, select);
      wrapper.appendChild(select);

      [' .select-zona-chevron', ' .filtro-area-chevron', ' .select-grupo-chevron', ' .select-usuario-chevron' ].forEach(s => {
        container.querySelectorAll(s).forEach(el => { el.style.display = 'none'; });
      });

      const triggerWrap = document.createElement('div');
      triggerWrap.className = 'combobox-trigger w-full border border-gray-300 rounded-xl bg-white hover:border-gray-400 focus-within:ring-2 focus-within:ring-[#39A900]/20 focus-within:border-[#39A900] py-2.5 text-sm';

      const input = document.createElement('input');
      input.type = 'text';
      input.autocomplete = 'off';
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
            select.value = value;
            select.dispatchEvent(new Event('change', { bubbles: true }));
            input.value = (clearValue !== undefined && clearValue !== null && String(value) === String(clearValue)) ? '' : text;
            dropdown.classList.add('hidden');
            if (dropdown.parentNode === document.body) wrapper.appendChild(dropdown);
            toggleClearVisibility();
          });
          dropdown.appendChild(div);
        });
        dropdown.classList.toggle('hidden', dropdown.children.length === 0);
      }

      function toggleClearVisibility() {
        const hasText = (input.value || '').trim().length > 0;
        wrapper.classList.toggle('has-value', hasText);
        btnClear.classList.toggle('visible', hasText);
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
      updateInputFromSelect();
      wrapper._cbUpdateInput = updateInputFromSelect;

      select.classList.add('sr-only');
      select.style.cssText = 'position:absolute;width:1px;height:1px;margin:-1px;padding:0;border:0;clip:rect(0,0,0,0);';
      wrapper.appendChild(triggerWrap);
      wrapper.appendChild(dropdown);

      const inTable = isInTable(wrapper);
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

      select.addEventListener('change', updateInputFromSelect);

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
          if (inp && !(inp.value || '').trim() && typeof w._cbUpdateInput === 'function') w._cbUpdateInput();
        });
      }, true);
    }
  }

  function resetComboboxes() {
    closeAllDropdowns('.combobox-dropdown');
  }

  global.ComboboxComponent = {
    enhance: enhanceCombobox,
    reset: resetComboboxes,
    closeAll: closeAllDropdowns
  };
})(typeof window !== 'undefined' ? window : this);
