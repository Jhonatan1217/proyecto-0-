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
   * @param {boolean} [opts.simpleSelect] - Si true: solo dropdown, sin input ni botón limpiar (para opciones predefinidas)
   * @param {boolean} [opts.inTable] - Si está dentro de tabla (usa position: fixed + dropup)
   * @param {Function} [opts.onEnhance] - Callback cuando se enhancea cada select
   */
  function enhanceCombobox(opts) {
    const selector = opts.selector || '.select-zona';
    const dropdownClass = opts.dropdownClass || 'custom-select-dropdown';
    const optionClass = opts.optionClass || 'custom-option';
    const placeholder = opts.placeholder || 'Buscar...';
    const clearValue = opts.clearValue;
    const simpleSelect = opts.simpleSelect === true;
    const isInTable = (el) => el.closest('table') || el.closest('[id*="wrapTabla"]');

    document.querySelectorAll(selector).forEach(select => {
      if (select.dataset.comboboxEnhanced === '1') return;
      select.dataset.comboboxEnhanced = '1';

      const container = select.parentNode;
      const wrapper = document.createElement('div');
      wrapper.className = 'combobox-wrapper' + (simpleSelect ? ' combobox-simple' : '');
      container.insertBefore(wrapper, select);
      wrapper.appendChild(select);

      [' .select-zona-chevron', ' .filtro-area-chevron', ' .select-grupo-chevron', ' .select-usuario-chevron' ].forEach(s => {
        container.querySelectorAll(s).forEach(el => { el.style.display = 'none'; });
      });

      const triggerWrap = document.createElement('div');
      triggerWrap.className = 'combobox-trigger w-full border border-gray-300 rounded-xl bg-white hover:border-gray-400 focus-within:ring-2 focus-within:ring-[#39A900]/20 focus-within:border-[#39A900] py-2.5 text-sm flex items-center justify-between gap-2';

      let input, btnClear;
      const spanDisplay = document.createElement('span');
      spanDisplay.className = 'combobox-display truncate flex-1 text-left text-gray-900';

      const chevron = document.createElement('span');
      chevron.className = 'chevron-combobox shrink-0 text-gray-400';
      chevron.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>';

      if (simpleSelect) {
        triggerWrap.appendChild(spanDisplay);
        triggerWrap.appendChild(chevron);
      } else {
        input = document.createElement('input');
        input.type = 'text';
        input.autocomplete = 'off';
        input.className = 'combobox-input w-full bg-transparent py-0 border-0 focus:ring-0 text-gray-900 placeholder:text-gray-400 flex-1 min-w-0';
        input.placeholder = placeholder;

        const iconSlot = document.createElement('div');
        iconSlot.className = 'combobox-icon-slot';

        const iconChevron = document.createElement('span');
        iconChevron.className = 'combobox-icon-chevron';
        iconChevron.setAttribute('aria-hidden', 'true');
        iconChevron.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>';

        btnClear = document.createElement('button');
        btnClear.type = 'button';
        btnClear.className = 'btn-clear-combobox';
        btnClear.setAttribute('aria-label', 'Limpiar');
        btnClear.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';

        iconSlot.appendChild(iconChevron);
        iconSlot.appendChild(btnClear);

        triggerWrap.appendChild(input);
        triggerWrap.appendChild(iconSlot);
      }

      const dropdown = document.createElement('div');
      dropdown.className = 'combobox-dropdown ' + dropdownClass + ' hidden';
      dropdown.setAttribute('role', 'listbox');
      dropdown._cbWrapper = wrapper;

      const optionsData = () => [...select.options].filter(o => !o.disabled).map(o => ({ value: o.value, text: (o.textContent || '').trim() }));

      function renderOptions(filterText) {
        const q = simpleSelect ? '' : (filterText || '').trim().toLowerCase();
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
            if (!simpleSelect) {
              input.value = (clearValue !== undefined && clearValue !== null && String(value) === String(clearValue)) ? '' : text;
              toggleClearVisibility();
              input.blur();
              closeDropdownOnly();
            } else {
              updateDisplayFromSelect();
              dropdown.classList.add('hidden');
              dropdown.classList.remove('dropdown-over-table', 'dropdown-up');
              dropdown.style.cssText = '';
              if (dropdown.parentNode === document.body) wrapper.appendChild(dropdown);
              wrapper.classList.remove('combobox-open');
            }
          });
          dropdown.appendChild(div);
        });
        dropdown.classList.toggle('hidden', dropdown.children.length === 0);
      }

      function toggleClearVisibility() {
        if (simpleSelect || !input) return;
        const hasText = (input.value || '').trim().length > 0;
        wrapper.classList.toggle('has-value', hasText);
      }

      function updateDisplayFromSelect() {
        const opt = select.options[select.selectedIndex];
        const text = opt ? (opt.textContent || '').trim() : '';
        if (simpleSelect && spanDisplay) {
          spanDisplay.textContent = text;
        }
      }

      function updateInputFromSelect() {
        if (simpleSelect) {
          updateDisplayFromSelect();
        } else {
          if (clearValue !== undefined && clearValue !== null && String(select.value) === String(clearValue)) {
            input.value = '';
          } else {
            const opt = select.options[select.selectedIndex];
            input.value = opt ? (opt.textContent || '').trim() : '';
          }
          toggleClearVisibility();
        }
      }
      updateInputFromSelect();
      wrapper._cbUpdateInput = updateInputFromSelect;
      wrapper._cbSelect = select;
      wrapper._cbClearValue = clearValue;

      select.classList.add('sr-only');
      select.style.cssText = 'position:absolute;width:1px;height:1px;margin:-1px;padding:0;border:0;clip:rect(0,0,0,0);';
      wrapper.appendChild(triggerWrap);
      wrapper.appendChild(dropdown);

      const inTable = isInTable(wrapper);
      const maxH = DROPDOWN_MAX_ITEMS * ITEM_HEIGHT_REM * parseFloat(getComputedStyle(document.documentElement).fontSize);

      function positionAndShow(forceShowAll) {
        const filterText = forceShowAll ? '' : (input ? input.value : '');
        renderOptions(filterText);
        if (dropdown.children.length === 0) return;
        wrapper._cbValueOnOpen = select.value;

        if (inTable) {
          closeAllDropdowns();
          dropdown.style.cssText = 'position:fixed;visibility:hidden;top:-9999px;left:0;display:block;';
          document.body.appendChild(dropdown);
          requestAnimationFrame(() => {
            requestAnimationFrame(() => {
              applyDropdownPosition(wrapper, dropdown, triggerWrap, maxH);
              dropdown.style.visibility = 'visible';
              dropdown.classList.remove('hidden');
              dropdown._cbJustOpened = Date.now();
              wrapper.classList.add('combobox-open');
            });
          });
        } else {
          dropdown.style.maxHeight = maxH + 'px';
          dropdown.classList.remove('hidden');
          dropdown._cbJustOpened = Date.now();
          wrapper.classList.add('combobox-open');
        }
      }

      function closeDropdownOnly() {
        dropdown.classList.add('hidden');
        dropdown.classList.remove('dropdown-over-table', 'dropdown-up');
        dropdown.style.cssText = '';
        if (dropdown.parentNode === document.body) wrapper.appendChild(dropdown);
        wrapper.classList.remove('combobox-open');
      }

      function openFromTrigger(ev) {
        if (ev) { ev.preventDefault(); ev.stopPropagation(); }
        if (dropdown.classList.contains('hidden')) {
          positionAndShow();
          if (!simpleSelect && input) {
            setTimeout(() => { try { input.focus({ preventScroll: true }); } catch (_) { input.focus(); } }, 0);
          }
        } else if (simpleSelect) {
          closeDropdownOnly();
        }
      }
      wrapper._cbOpen = openFromTrigger;

      triggerWrap.addEventListener('mousedown', (e) => {
        if (simpleSelect || e.target !== input) openFromTrigger(e);
      });
      triggerWrap.addEventListener('click', (e) => { e.preventDefault(); e.stopPropagation(); });

      if (!simpleSelect && input) {
        input.addEventListener('focus', (e) => {
          closeAllDropdowns();
          positionAndShow();
          e?.preventDefault?.();
        });
        input.addEventListener('blur', () => {
          requestAnimationFrame(() => {
            if (wrapper._cbValueOnOpen === undefined) return;
            const isEmpty = select.value === '' || (clearValue !== undefined && clearValue !== null && String(select.value) === String(clearValue));
            if (isEmpty) {
              select.value = wrapper._cbValueOnOpen;
              updateInputFromSelect();
            }
          });
        });
        input.addEventListener('input', () => { renderOptions(input.value); toggleClearVisibility(); });
        input.addEventListener('keydown', (e) => {
          if (e.key === 'Escape') {
            if (wrapper._cbValueOnOpen !== undefined) {
              const isEmpty = select.value === '' || (clearValue !== undefined && clearValue !== null && String(select.value) === String(clearValue));
              if (isEmpty) {
                select.value = wrapper._cbValueOnOpen;
                updateInputFromSelect();
              }
            }
            closeDropdownOnly();
            input.blur();
          }
        });
      }

      if (!simpleSelect && btnClear) {
        btnClear.addEventListener('click', (e) => {
          e.preventDefault();
          e.stopPropagation();
          // Guardar el valor original ANTES de limpiar, para poder restaurarlo al salir
          const savedValue = wrapper._cbValueOnOpen !== undefined
            ? wrapper._cbValueOnOpen
            : select.value;
          closeDropdownOnly();
          input.value = '';
          select.value = (clearValue !== undefined && clearValue !== null) ? clearValue : '';
          select.dispatchEvent(new Event('change', { bubbles: true }));
          toggleClearVisibility();
          requestAnimationFrame(() => {
            positionAndShow(true);
            // positionAndShow sobreescribiría _cbValueOnOpen con ''; lo restauramos al original
            wrapper._cbValueOnOpen = savedValue;
            requestAnimationFrame(() => {
              try { input.focus({ preventScroll: true }); } catch (_) { input.focus(); }
            });
          });
        });
      }

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
          d.classList.remove('dropdown-over-table', 'dropdown-up');
          d.style.cssText = '';
          if (d.parentNode === document.body && w) w.appendChild(d);
          w.classList.remove('combobox-open');
          const sel = w._cbSelect;
          const clearVal = w._cbClearValue;
          if (sel && w._cbValueOnOpen !== undefined) {
            const isEmpty = sel.value === '' || (clearVal !== undefined && clearVal !== null && String(sel.value) === String(clearVal));
            if (isEmpty) {
              sel.value = w._cbValueOnOpen;
              if (typeof w._cbUpdateInput === 'function') w._cbUpdateInput();
            }
          } else {
            const inp = w?.querySelector('.combobox-input');
            if (inp && !(inp.value || '').trim() && typeof w._cbUpdateInput === 'function') w._cbUpdateInput();
          }
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
