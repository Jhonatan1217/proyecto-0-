/**
 * registerTablesModal.js
 * Lógica de los modales y comboboxes de register_tables.php:
 *   1. Combobox de área / zona / grupo / competencia (cabecera y modal crear)
 *   2. Carga de programas por fallback API y filtrado de competencias por programa
 *   3. Modal de selección de RAEs por competencia
 */
(function () {
  'use strict';

  // ── Utilidades de combobox local ─────────────────────────────────────────
  function attachClearButton(input, panel, onClear) {
    const host = input.closest('.custom-combobox');
    if (!host || host.querySelector('.btn-clear-custom-combobox')) return;

    const fieldRow = document.createElement('div');
    fieldRow.className = 'custom-combobox-field';
    host.insertBefore(fieldRow, input);
    fieldRow.appendChild(input);

    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'btn-clear-custom-combobox';
    btn.setAttribute('aria-label', 'Limpiar');
    btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
    fieldRow.appendChild(btn);

    function updateClearBtn() {
      const show = !input.disabled && String(input.value || '').trim().length > 0;
      btn.classList.toggle('visible', show);
    }

    btn.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      if (panel) panel.classList.add('hidden');
      input.value = '';
      if (typeof onClear === 'function') onClear();
      else {
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));
      }
      updateClearBtn();
      if (!input.disabled) {
        queueMicrotask(() => {
          input.focus({ preventScroll: true });
        });
      }
    });

    input.addEventListener('input', updateClearBtn);
    input.addEventListener('change', updateClearBtn);
    updateClearBtn();
  }

  function initStyledCombobox({ input, panel, getItems, onSelect, getLabel, emptyText = 'Sin datos disponibles', onClear, maxVisibleRows }) {
    if (!input || !panel || typeof getItems !== 'function') return;

    const list = panel.querySelector('.custom-combobox-list');
    if (!list) return;

    if (maxVisibleRows === 6) {
      list.classList.add('custom-combobox-list--max-rows-6');
    }

    attachClearButton(input, panel, onClear);

    const normalize = (v) => String(v || '').trim().toLowerCase();

    function closePanel() { panel.classList.add('hidden'); }
    function openPanel() { if (!input.disabled) panel.classList.remove('hidden'); }

    function render(query = '') {
      const search = normalize(query);
      const items = (getItems() || []).filter((item) => {
        const label = normalize(getLabel ? getLabel(item) : item.label);
        return !search || label.includes(search);
      });

      list.innerHTML = '';

      if (!items.length) {
        const empty = document.createElement('div');
        empty.className = 'custom-combobox-empty';
        empty.textContent = emptyText;
        list.appendChild(empty);
        openPanel();
        return;
      }

      items.forEach((item) => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'custom-combobox-option';
        btn.textContent = getLabel ? getLabel(item) : item.label;
        btn.addEventListener('click', () => {
          input.value = getLabel ? getLabel(item) : item.label;
          if (typeof onSelect === 'function') onSelect(item);
          input.dispatchEvent(new Event('input', { bubbles: true }));
          input.dispatchEvent(new Event('change', { bubbles: true }));
          closePanel();
        });
        list.appendChild(btn);
      });

      openPanel();
    }

    input.addEventListener('focus', () => render(input.value));
    input.addEventListener('input', () => render(input.value));
    input.addEventListener('click', () => render(input.value));

    document.addEventListener('click', (e) => {
      const wrapper = input.closest('.custom-combobox');
      if (wrapper && !wrapper.contains(e.target)) closePanel();
    }, true);

    input._styledCombobox = { render, closePanel, openPanel };
  }

  /** Programa e instructor del modal crear: input + panel + select oculto (listado filtrable). */
  function initModalProgramaInstructorCombos() {
    const selProg   = document.getElementById('id_programa_select');
    const inpProg   = document.getElementById('id_programa_combo');
    const panelProg = document.getElementById('panelProgramaCrear');
    const selIns    = document.getElementById('nombre_instructor');
    const inpIns    = document.getElementById('id_instructor_combo');
    const panelIns  = document.getElementById('panelInstructorCrear');

    if (!selProg || !inpProg || !panelProg || !selIns || !inpIns || !panelIns) return;

    function getProgramaItems() {
      return Array.from(selProg.options)
        .filter((opt) => opt.value !== '' && !opt.disabled)
        .map((opt) => ({
          label: String(opt.textContent || '').trim(),
          id: String(opt.value || '')
        }))
        .filter((item) => item.id);
    }

    initStyledCombobox({
      input: inpProg,
      panel: panelProg,
      getItems: getProgramaItems,
      onSelect: (item) => {
        selProg.value = item.id || '';
        selProg.dispatchEvent(new Event('change', { bubbles: true }));
      },
      getLabel: (item) => item.label,
      emptyText: 'Sin programas disponibles',
      onClear: () => {
        selProg.value = '';
        selProg.dispatchEvent(new Event('change', { bubbles: true }));
      },
      maxVisibleRows: 6
    });

    function getInstructorItems() {
      return Array.from(selIns.options)
        .filter((opt) => opt.value !== '' && !opt.disabled)
        .map((opt) => ({
          label: String(opt.textContent || '').trim(),
          id: String(opt.value || '')
        }))
        .filter((item) => item.id);
    }

    initStyledCombobox({
      input: inpIns,
      panel: panelIns,
      getItems: getInstructorItems,
      onSelect: (item) => {
        selIns.value = item.id || '';
        selIns.dispatchEvent(new Event('change', { bubbles: true }));
      },
      getLabel: (item) => item.label,
      emptyText: 'Sin instructores disponibles',
      onClear: () => {
        selIns.value = '';
        selIns.dispatchEvent(new Event('change', { bubbles: true }));
      },
      maxVisibleRows: 6
    });

    function syncProgInputFromSelect() {
      const v = selProg.value;
      if (!v) {
        inpProg.value = '';
      } else {
        const opt = Array.from(selProg.options).find((o) => String(o.value) === String(v));
        inpProg.value = opt ? String(opt.textContent || '').trim() : '';
      }
      inpProg.dispatchEvent(new Event('input', { bubbles: true }));
    }

    function syncInsInputFromSelect() {
      const v = selIns.value;
      if (!v) {
        inpIns.value = '';
      } else {
        const opt = Array.from(selIns.options).find((o) => String(o.value) === String(v));
        inpIns.value = opt ? String(opt.textContent || '').trim() : '';
      }
      inpIns.dispatchEvent(new Event('input', { bubbles: true }));
    }

    selProg.addEventListener('change', syncProgInputFromSelect);
    selIns.addEventListener('change', syncInsInputFromSelect);
    syncProgInputFromSelect();
    syncInsInputFromSelect();
  }

  // ── Init comboboxes (cabecera + modal crear) ─────────────────────────────
  function initComboboxes() {
    const grupoData        = document.getElementById('listaGruposData');
    const inputGrupoFiltro = document.getElementById('inputGrupoTexto');
    const panelGrupoFiltro = document.getElementById('panelGrupoFiltro');
    const inputGrupoCrear  = document.getElementById('numero_ficha');
    const panelGrupoCrear  = document.getElementById('panelGrupoCrear');
    const selArea   = document.getElementById('id_area');
    const selZona   = document.getElementById('id_zona');
    const inpArea   = document.getElementById('id_area_combo');
    const inpZona   = document.getElementById('id_zona_combo');
    const listAreas = document.getElementById('listaAreasCombo');
    const listZonas = document.getElementById('listaZonasCombo');
    const panelArea = document.getElementById('panelAreaCrear');
    const panelZona = document.getElementById('panelZonaCrear');

    const grupos = grupoData
      ? Array.from(grupoData.options)
          .map((opt) => ({ label: String(opt.value || '').trim() }))
          .filter((item) => item.label !== '')
      : [];

    // Cabecera: número de grupo (no depender del modal para que siempre sea combobox)
    if (inputGrupoFiltro && panelGrupoFiltro) {
      initStyledCombobox({
        input: inputGrupoFiltro,
        panel: panelGrupoFiltro,
        getItems: () => grupos,
        onSelect: () => {},
        getLabel: (item) => item.label,
        maxVisibleRows: 6
      });
    }

    if (!selArea || !selZona || !inpArea || !inpZona || !listAreas || !listZonas) return;

    function findDatalistOption(listEl, value) {
      const target = String(value || '').trim().toLowerCase();
      if (!target) return null;
      return Array.from(listEl.options).find(
        (opt) => String(opt.value || '').trim().toLowerCase() === target
      ) || null;
    }

    function cargarZonasSegunArea(idArea) {
      listZonas.innerHTML = '';
      Array.from(selZona.options)
        .filter((opt) => opt.value !== '' && String(opt.dataset.area || '') === String(idArea))
        .forEach((opt) => {
          const op = document.createElement('option');
          op.value = String(opt.textContent || '').trim();
          op.dataset.id   = String(opt.value || '');
          op.dataset.area = String(opt.dataset.area || '');
          listZonas.appendChild(op);
        });
      if (inpZona._styledCombobox) inpZona._styledCombobox.render(inpZona.value);
    }

    function syncAreaFromInput() {
      const areaOpt = findDatalistOption(listAreas, inpArea.value);
      const idArea  = areaOpt ? String(areaOpt.dataset.id || '') : '';
      selArea.value    = idArea;
      inpZona.value    = '';
      selZona.value    = '';
      inpZona.disabled = !idArea;
      cargarZonasSegunArea(idArea);
    }

    function syncZonaFromInput() {
      const zonaOpt = findDatalistOption(listZonas, inpZona.value);
      selZona.value = zonaOpt ? String(zonaOpt.dataset.id || '') : '';
    }

    // Grupo (modal crear)
    initStyledCombobox({ input: inputGrupoCrear, panel: panelGrupoCrear,
      getItems: () => grupos, onSelect: () => {}, getLabel: (item) => item.label, maxVisibleRows: 6 });

    // Área (modal crear)
    initStyledCombobox({
      input: inpArea, panel: panelArea,
      getItems: () => Array.from(listAreas.options).map((opt) => ({
        label: String(opt.value || '').trim(),
        id:    String(opt.dataset.id || '')
      })),
      onSelect: (item) => {
        selArea.value    = item.id || '';
        inpZona.value    = '';
        selZona.value    = '';
        inpZona.disabled = !item.id;
        cargarZonasSegunArea(item.id || '');
      },
      getLabel: (item) => item.label,
      onClear: () => { syncAreaFromInput(); },
      maxVisibleRows: 6
    });

    // Zona (modal crear)
    initStyledCombobox({
      input: inpZona, panel: panelZona,
      getItems: () => Array.from(listZonas.options).map((opt) => ({
        label: String(opt.value || '').trim(),
        id:    String(opt.dataset.id || ''),
        area:  String(opt.dataset.area || '')
      })),
      onSelect: (item) => { selZona.value = item.id || ''; },
      getLabel: (item) => item.label,
      onClear: () => {
        selZona.value = '';
        selZona.dispatchEvent(new Event('change', { bubbles: true }));
      },
      maxVisibleRows: 6
    });

    // Competencia (modal crear)
    const selComp      = document.getElementById('id_competencia');
    const inpComp      = document.getElementById('id_competencia_combo');
    const panelComp    = document.getElementById('panelCompetenciaCrear');
    const selProgForComp = document.getElementById('id_programa_select');

    if (selComp && inpComp && panelComp) {
      function getCompetenciaItems() {
        const progVal = selProgForComp ? selProgForComp.value : '';
        return Array.from(selComp.options)
          .filter((opt) => opt.value !== '' && !opt.disabled &&
            (progVal === '' || String(opt.dataset.programa || '') === String(progVal)))
          .map((opt) => ({
            label: String(opt.textContent || '').trim(),
            id:    String(opt.value || '')
          }));
      }

      initStyledCombobox({
        input: inpComp, panel: panelComp,
        getItems: getCompetenciaItems,
        onSelect: (item) => {
          selComp.value = item.id;
          selComp.dispatchEvent(new Event('change', { bubbles: true }));
        },
        getLabel: (item) => item.label,
        emptyText: 'Sin competencias para este programa',
        onClear: () => {
          selComp.value = '';
          selComp.dispatchEvent(new Event('change', { bubbles: true }));
        },
        maxVisibleRows: 6
      });

      if (selProgForComp) {
        selProgForComp.addEventListener('change', () => {
          inpComp.value = '';
          selComp.value = '';
          selComp.dispatchEvent(new Event('change', { bubbles: true }));
          if (inpComp._styledCombobox && typeof inpComp._styledCombobox.closePanel === 'function') {
            inpComp._styledCombobox.closePanel();
          }
          inpComp.dispatchEvent(new Event('change', { bubbles: true }));
        });
      }
    }

    inpArea.addEventListener('change', syncAreaFromInput);
    inpArea.addEventListener('blur',   syncAreaFromInput);
    inpZona.addEventListener('change', syncZonaFromInput);
    inpZona.addEventListener('blur',   syncZonaFromInput);

    // Estado inicial: zona deshabilitada hasta elegir área
    inpZona.disabled  = true;
    listZonas.innerHTML = '';
  }

  // ── Fallback carga de programas + filtro competencias ───────────────────
  function initProgramasYCompetencias() {
    const base    = (window.BASE_URL || '').replace(/\/+$/, '/');
    const selProg = document.getElementById('id_programa_select');
    const selComp = document.getElementById('id_competencia');

    async function cargarProgramasFallback() {
      if (!selProg) return;
      const yaTiene = Array.from(selProg.options).some(
        (opt) => opt.value && !String(opt.textContent || '').toLowerCase().includes('sin datos disponibles')
      );
      if (yaTiene) return;

      try {
        const res  = await fetch(base + 'src/controllers/ProgramasController.php?accion=listar');
        if (!res.ok) return;
        const data = await res.json();
        const arr  = Array.isArray(data) ? data : (Array.isArray(data.data) ? data.data : []);
        if (!arr.length) return;

        const activos = arr.filter((p) => p && (String(p.estado) === '1' || p.estado === 1 || p.estado === undefined));
        const lista   = activos.length ? activos : arr;

        selProg.innerHTML = '<option value="">Ingrese el programa de formación</option>';
        lista.forEach((p) => {
          const id     = p.id_programa ?? '';
          const nombre = p.nombre_programa ?? '';
          if (!id || !nombre) return;
          const opt  = document.createElement('option');
          opt.value  = String(id);
          opt.textContent = String(nombre);
          selProg.appendChild(opt);
        });
      } catch (e) {
        console.warn('No se pudieron cargar programas (fallback):', e);
      }
    }

    function filtrarCompetenciasPorPrograma() {
      if (!selProg || !selComp) return;
      const progVal = selProg.value;
      let hayCoincidencias = false;

      for (const opt of selComp.options) {
        if (opt.value === '') { opt.hidden = false; opt.disabled = false; continue; }
        const show = progVal !== '' && String(opt.getAttribute('data-programa') ?? '') === String(progVal);
        if (show) hayCoincidencias = true;
        opt.hidden   = !show;
        opt.disabled = !show;
      }

      if (progVal !== '' && !hayCoincidencias) {
        console.warn('[Trimestralización] Sin competencias para programa', progVal);
      }

      const sel = selComp.selectedOptions[0];
      if (sel && sel.hidden) selComp.value = '';
    }

    cargarProgramasFallback();

    if (selProg) selProg.addEventListener('change', filtrarCompetenciasPorPrograma);
    filtrarCompetenciasPorPrograma();
  }

  // ── Modal RAEs por competencia ───────────────────────────────────────────
  function initModalRaes() {
    const BASE_URL = window.BASE_URL || '';
    const API_RAES = (BASE_URL + 'src/controllers/RaeController.php?accion=listar').replace(/\/+$/, '');

    const form = document.getElementById('formTrimestralizacion');
    if (!form) return;

    const selComp    = document.getElementById('id_competencia');
    const hiddenRaes = document.getElementById('id_rae_field');
    const resumenRaes = document.getElementById('textoResumenRaes');
    const btnRaes    = document.getElementById('btnSeleccionarRaes');

    const modalRaes     = document.getElementById('modalRaes');
    const backdropRaes  = document.getElementById('modalRaesBackdrop');
    const btnCerrar     = document.getElementById('btnCerrarModalRaes');
    const btnCancelar   = document.getElementById('btnCancelarRaes');
    const btnGuardar    = document.getElementById('btnGuardarRaes');
    const listaRaes     = document.getElementById('listaRaesModal');
    const chkTodos      = document.getElementById('chkRaesTodos');
    const contador      = document.getElementById('contadorRaesSeleccionadas');
    const subtitulo     = document.getElementById('subtituloModalRaes');

    if (!selComp || !btnRaes || !modalRaes) return;

    function toast(msg, type = 'info') {
      if (window.Swal) {
        Swal.fire({ toast: true, position: 'top-end', icon: type, title: msg,
          showConfirmButton: false, timer: 2200, timerProgressBar: true });
      } else {
        alert(msg);
      }
    }

    function actualizarResumen() {
      const partes = (hiddenRaes.value || '').split(',').map((v) => v.trim()).filter(Boolean);
      resumenRaes.textContent = partes.length === 0
        ? 'No hay RAEs seleccionadas.'
        : partes.length === 1 ? '1 RAE seleccionada.' : partes.length + ' RAEs seleccionadas.';
    }

    function abrirModal() {
      modalRaes.classList.remove('hidden');
      modalRaes.style.display = '';
      modalRaes.style.pointerEvents = '';
      modalRaes.style.visibility = '';
      modalRaes.querySelectorAll("[class*='inset-0']").forEach((el) => { el.style.pointerEvents = ''; });
    }

    function cerrarModal() {
      const active = document.activeElement;
      if (active && modalRaes.contains(active)) active.blur();
      modalRaes.classList.add('hidden');
      modalRaes.classList.remove('flex', 'block', 'items-center', 'justify-center');
      modalRaes.style.cssText = 'display:none;pointer-events:none;visibility:hidden;';
      modalRaes.querySelectorAll("[class*='inset-0']").forEach((el) => { el.style.pointerEvents = 'none'; });
      document.body.style.overflow = '';
      document.body.classList.remove('overflow-hidden');
    }

    function contarSeleccionadas() {
      const n = listaRaes.querySelectorAll('.chk-rae-modal:checked').length;
      contador.textContent = n === 0 ? '' : n === 1 ? '1 RAE seleccionada' : n + ' RAEs seleccionadas';
    }

    function actualizarHiddenAuto() {
      const ids = Array.from(listaRaes.querySelectorAll('.chk-rae-modal:checked')).map((ch) => ch.value);
      hiddenRaes.value = ids.join(',');
      actualizarResumen();
    }

    function aplicarTodos() {
      listaRaes.querySelectorAll('.chk-rae-modal').forEach((ch) => { ch.checked = chkTodos.checked; });
      contarSeleccionadas();
      actualizarHiddenAuto();
    }

    async function cargarRaes(idComp) {
      listaRaes.innerHTML = '<p class="text-gray-500 text-xs">Cargando RAEs...</p>';
      chkTodos.checked = false;
      contador.textContent = '';

      try {
        const resp = await fetch(API_RAES + '&id_competencia=' + encodeURIComponent(idComp));
        const data = await resp.json();
        const lista = Array.isArray(data) ? data : (data.data || []);

        if (!lista.length) {
          listaRaes.innerHTML = '<p class="text-gray-500 text-xs">No hay RAEs asociadas a esta competencia.</p>';
          hiddenRaes.value = '';
          actualizarResumen();
          return;
        }

        const previas = (hiddenRaes.value || '').split(',').map((v) => v.trim()).filter(Boolean);
        const frag    = document.createDocumentFragment();

        lista.forEach((r) => {
          const id     = r.id_rae || r.id || r.ID_RAE;
          const codigo = r.codigo_rae || r.codigo || r.codigoRAE || '';
          const desc   = r.descripcion || r.descripcion_rae || r.nombre_rae || r.nombre || '';
          if (!id) return;

          const label = document.createElement('label');
          label.className = 'flex items-start gap-2 py-1 border-b border-gray-100 last:border-b-0 cursor-pointer text-sm sm:text-xs text-gray-800';

          const chk  = document.createElement('input');
          chk.type   = 'checkbox';
          chk.value  = id;
          chk.className = 'mt-[3px] chk-rae-modal rounded border-gray-300';
          if (previas.includes(String(id))) chk.checked = true;

          const span = document.createElement('span');
          span.innerHTML = (codigo ? '<strong>' + codigo + '</strong> — ' : '') + (desc || '(sin descripción)');

          label.appendChild(chk);
          label.appendChild(span);
          frag.appendChild(label);
        });

        listaRaes.innerHTML = '';
        listaRaes.appendChild(frag);
        contarSeleccionadas();
        actualizarHiddenAuto();
      } catch (err) {
        console.error(err);
        listaRaes.innerHTML = '<p class="text-red-500 text-xs">Error al cargar las RAEs.</p>';
      }
    }

    function toggleBoton() { btnRaes.disabled = !selComp.value; }

    toggleBoton();
    actualizarResumen();

    selComp.addEventListener('change', () => {
      toggleBoton();
      hiddenRaes.value = '';
      actualizarResumen();
    });

    btnRaes.addEventListener('click', async () => {
      const idComp = selComp.value;
      if (!idComp) { toast('Primero selecciona una competencia.', 'warning'); return; }
      const opt = selComp.selectedOptions[0];
      subtitulo.textContent = opt ? (opt.textContent || '').trim() : '';
      await cargarRaes(idComp);
      abrirModal();
    });

    [btnCerrar, btnCancelar].forEach((btn) => { if (btn) btn.addEventListener('click', cerrarModal); });
    if (backdropRaes) backdropRaes.addEventListener('click', cerrarModal);
    if (btnGuardar)   btnGuardar.addEventListener('click', cerrarModal);
    if (chkTodos)     chkTodos.addEventListener('change', aplicarTodos);

    listaRaes.addEventListener('change', (e) => {
      if (e.target.classList.contains('chk-rae-modal')) {
        contarSeleccionadas();
        actualizarHiddenAuto();
      }
    });
  }

  /**
   * Rellena #nombre_instructor vía API si el PHP no trajo opciones (mismo criterio que cargarProgramasFallback).
   * Debe ejecutarse antes de initModalProgramaInstructorCombos.
   */
  async function cargarInstructoresFallback() {
    const sel = document.getElementById('nombre_instructor');
    if (!sel) return;
    const yaTiene = Array.from(sel.options).some(
      (opt) => opt.value && !String(opt.textContent || '').toLowerCase().includes('sin datos disponibles')
    );
    if (yaTiene) return;

    const base = (window.BASE_URL || '').replace(/\/+$/, '/');
    try {
      const res = await fetch(base + 'src/controllers/InstructorController.php?accion=listar');
      if (!res.ok) return;
      const data = await res.json();
      const arr = Array.isArray(data) ? data : (Array.isArray(data.data) ? data.data : []);
      if (!arr.length) return;

      const activos = arr.filter((i) => {
        const e = i.estado;
        return e === undefined || e === null || String(e) === '1' || e === 1;
      });
      const lista = activos.length ? activos : arr;

      sel.innerHTML = '<option value="">Seleccione el instructor</option>';
      lista.forEach((i) => {
        const id = i.id_instructor ?? i.id_usuario ?? '';
        const nombre = i.nombre_instructor ?? i.nombre_completo ?? '';
        const tipo = i.tipo_instructor ?? i.tipo_contrato ?? '';
        if (!id) return;
        const opt = document.createElement('option');
        opt.value = String(id);
        if (tipo) opt.setAttribute('data-tipo', String(tipo));
        opt.textContent = nombre + (tipo ? ' — ' + tipo : '');
        sel.appendChild(opt);
      });
    } catch (e) {
      console.warn('No se pudieron cargar instructores (fallback):', e);
    }
  }

  /** Selects del modal Crear trimestralización: mismo componente global que el resto del sistema */
  function initEnhanceModalTrimestralSelects() {
    if (typeof ComboboxComponent === 'undefined' || typeof ComboboxComponent.enhanceSelectStyled !== 'function') return;
    const form = document.getElementById('formTrimestralizacion');
    if (!form) return;
    ComboboxComponent.enhanceSelectStyled({
      selector: '#formTrimestralizacion select.select-styled',
      placeholder: 'Seleccione…',
      placeholderValues: [''],
    });
  }

  // ── Bootstrap ────────────────────────────────────────────────────────────
  document.addEventListener('DOMContentLoaded', async function () {
    initEnhanceModalTrimestralSelects();
    await cargarInstructoresFallback();
    initModalProgramaInstructorCombos();
    initComboboxes();
    initProgramasYCompetencias();
    initModalRaes();
  });

  /** Expuesto para registerTables.js (modal editar horario: combobox filtrable). */
  window.registerTablesModalHelpers = {
    initStyledCombobox: initStyledCombobox
  };
})();
