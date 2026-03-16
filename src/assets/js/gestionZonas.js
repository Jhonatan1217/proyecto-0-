/**
 * Gestión de Zonas - usa nombre_zona (texto) + id_area como identificadores.
 * Usa ComboboxComponent centralizado con dropup para última fila.
 */
(function () {
  const BASE = window.BASE_URL || '';
  const API_URL = BASE + 'src/controllers/ZonaController.php';
  const API_AREA_URL = BASE + 'src/controllers/AreaController.php';

  let zonas = [];
  let areas = [];
  let filaEnEdicion = null;

  const Toast = Swal?.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 2500,
    timerProgressBar: true,
    background: '#fff',
    color: '#333',
    didOpen: (t) => {
      t.addEventListener('mouseenter', Swal.stopTimer);
      t.addEventListener('mouseleave', Swal.resumeTimer);
    }
  }) || { fire: (o) => alert(o?.title || '') };

  function normalizarTexto(t) {
    return String(t || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim();
  }

  function esErrorDuplicado(msg) {
    const m = String(msg || '').toLowerCase();
    return m.includes('duplicate') || m.includes('ya existe') || m.includes('1062') || m.includes('23000');
  }

  function apiFetch(url, method, body) {
    return fetch(url, { method: method || 'GET', body: body }).then(r => r.json());
  }

  /* ========== ÁREAS ========== */
  async function cargarAreas() {
    try {
      const json = await apiFetch(`${API_AREA_URL}?accion=listar`);
      if (json.status === 'success' && Array.isArray(json.data)) {
        const unicas = new Set();
        areas = json.data.filter(a => {
          const n = (a.nombre_area || '').trim();
          if (unicas.has(n)) return false;
          unicas.add(n);
          return true;
        });
        return areas;
      }
    } catch (e) { console.error('Error cargar áreas:', e); }
    return [];
  }

  async function cargarAreasParaFiltro() {
    const filtro = document.getElementById('filtroArea');
    if (!filtro) return;
    try {
      const [rZ, rA] = await Promise.all([
        fetch(`${API_URL}?accion=listar`),
        fetch(`${API_AREA_URL}?accion=listar`)
      ]);
      const jZ = await rZ.json();
      const jA = await rA.json();
      const idsConZonas = new Set((jZ.data || []).map(z => String(z.id_area)).filter(Boolean));
      const areasFilt = (jA.data || []).filter(a => idsConZonas.has(String(a.id_area)));
      filtro.innerHTML = '<option value="todas">Todas las áreas</option>';
      const unicas = new Set();
      areasFilt.forEach(a => {
        const n = (a.nombre_area || '').trim();
        if (!unicas.has(n)) {
          unicas.add(n);
          const o = document.createElement('option');
          o.value = a.id_area;
          o.textContent = n;
          filtro.appendChild(o);
        }
      });
    } catch (e) {
      console.error('Error áreas filtro:', e);
      filtro.innerHTML = '<option value="todas">Todas las áreas</option>';
    }
    enhanceFiltroArea();
  }

  function enhanceFiltroArea() {
    const select = document.getElementById('filtroArea');
    if (!select || select.dataset.cb === '1') return;
    select.dataset.cb = '1';
    if (typeof ComboboxComponent === 'undefined') return;
    ComboboxComponent.enhance({
      selector: '#filtroArea',
      dropdownClass: 'combobox-dropdown-filtro',
      optionClass: 'custom-option',
      placeholder: 'Todas las áreas',
      clearValue: 'todas',
      restoreValueOnBlurWhenEmpty: false
    });
  }

  async function cargarAreasParaModal() {
    const sel = document.getElementById('id_area');
    if (!sel) return;
    await cargarAreas();
    sel.innerHTML = '<option disabled selected value="">Seleccione un Área</option>';
    areas.forEach(a => {
      const o = document.createElement('option');
      o.value = a.id_area;
      o.textContent = (a.nombre_area || '').trim();
      sel.appendChild(o);
    });
    enhanceSelectsZona();
  }

  function enhanceSelectsZona() {
    if (typeof ComboboxComponent === 'undefined') return;
    // Select del modal "Nueva Zona": clearValue:'' para que el input aparezca vacío
    // cuando ningún área está seleccionada, mostrando el placeholder y todas las opciones al abrir.
    ComboboxComponent.enhance({
      selector: '#id_area',
      dropdownClass: 'select-zona-dropdown',
      optionClass: 'select-zona-option',
      placeholder: 'Buscar área...',
      clearValue: '',
      restoreValueOnBlurWhenEmpty: true
    });
    // Selects de filas en edición inline (distintos al modal)
    ComboboxComponent.enhance({
      selector: '.select-zona:not(#id_area)',
      dropdownClass: 'select-zona-dropdown',
      optionClass: 'select-zona-option',
      placeholder: 'Buscar área...',
      restoreValueOnBlurWhenEmpty: true
    });
  }

  /* ========== TABLA ========== */
  const ICON_PENCIL = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 21h8"/><path d="m15 5 4 4"/><path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/></svg>';

  async function cargarZonas() {
    const tbody = document.getElementById('tbodyZonas');
    if (!tbody) return;
    tbody.innerHTML = '<tr><td colspan="3" class="p-4 text-gray-500 text-center">Cargando zonas...</td></tr>';
    try {
      const json = await apiFetch(`${API_URL}?accion=listar`);
      if (json.status === 'success') {
        zonas = json.data || [];
        if (!zonas.length) {
          tbody.innerHTML = '<tr><td colspan="3" class="text-center p-4 text-gray-500">No hay zonas registradas</td></tr>';
        } else {
          renderTabla(zonas);
          aplicarFiltros();
        }
      } else {
        tbody.innerHTML = `<tr><td colspan="3" class="text-center p-4 text-red-500">${json.message || 'Error'}</td></tr>`;
      }
    } catch (e) {
      console.error('Error cargar zonas:', e);
      tbody.innerHTML = '<tr><td colspan="3" class="text-center p-4 text-red-500">Error al cargar</td></tr>';
    }
    ajustarAltoTabla();
  }

  function renderTabla(data) {
    const tbody = document.getElementById('tbodyZonas');
    if (!tbody) return;
    tbody.innerHTML = '';
    data.forEach(z => {
      const tr = document.createElement('tr');
      tr.className = 'border-b hover:bg-gray-50 transition';
      tr.dataset.id      = z.id_zona;
      tr.dataset.idArea  = z.id_area ?? '';
      tr.dataset.nombre  = z.nombre_zona ?? '';
      tr.innerHTML = `
        <td class="col-nombre font-medium text-gray-800">${z.nombre_zona || '—'}</td>
        <td class="col-area text-center">
          <span class="tag-pill">${z.nombre_area || '—'}</span>
        </td>
        <td class="col-acciones text-right">
          <div class="flex justify-end items-center gap-3">
            <button type="button" class="btn-editar-zona p-2 border rounded-lg hover:bg-gray-50 transition text-gray-600 hover:text-[#39A900]" title="Editar">
              <span class="inline-block w-5 h-5">${ICON_PENCIL}</span>
            </button>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" class="sr-only peer switch-estado-zona"
                ${Number(z.estado) === 1 ? 'checked' : ''}
                data-id="${z.id_zona}">
              <div class="w-11 h-6 bg-gray-300 rounded-full peer-checked:bg-[#39A900] transition"></div>
              <div class="absolute left-0.5 top-0.5 bg-white w-5 h-5 rounded-full transition peer-checked:translate-x-5"></div>
            </label>
          </div>
        </td>
      `;
      tbody.appendChild(tr);
    });
    bindEventosTabla();
  }

  function bindEventosTabla() {
    const tbody = document.getElementById('tbodyZonas');
    if (!tbody) return;

    tbody.querySelectorAll('.switch-estado-zona').forEach(sw => {
      const clone = sw.cloneNode(true);
      sw.replaceWith(clone);
      clone.addEventListener('change', async (e) => {
        const id     = e.target.dataset.id;
        if (!id) return;
        const estado = e.target.checked ? 1 : 0;
        const fd = new FormData();
        fd.append('accion', 'cambiar_estado');
        fd.append('id_zona', id);
        fd.append('estado', String(estado));
        try {
          const j = await apiFetch(API_URL, 'POST', fd);
          Toast.fire({ icon: j.status === 'success' ? 'success' : 'error', title: j.message || (j.status === 'success' ? 'Estado actualizado' : 'Error') });
          if (j.status === 'success') cargarZonas();
          else e.target.checked = !e.target.checked;
        } catch (err) {
          e.target.checked = !e.target.checked;
          Toast.fire({ icon: 'error', title: 'Error al cambiar estado' });
        }
      });
    });

    tbody.querySelectorAll('.btn-editar-zona').forEach(btn => {
      btn.replaceWith(btn.cloneNode(true));
    });
    tbody.querySelectorAll('.btn-editar-zona').forEach(btn => {
      btn.addEventListener('click', (e) => entrarModoEdicion(e));
    });
  }

  function entrarModoEdicion(e) {
    const row = e.target.closest('tr[data-id]');
    if (!row || row.classList.contains('editando')) return;
    if (filaEnEdicion && filaEnEdicion !== row) {
      Toast.fire({ icon: 'info', title: 'Guarda o cancela los cambios actuales.' });
      return;
    }

    const id          = row.dataset.id;
    const idArea      = row.dataset.idArea;
    const nombreActual = row.dataset.nombre;
    const z = zonas.find(x => String(x.id_zona) === String(id));
    if (!z) return;

    filaEnEdicion = row;
    row.classList.add('editando', 'bg-gray-50');

    const optsArea = areas.map(a =>
      `<option value="${a.id_area}" ${String(a.id_area) === String(idArea) ? 'selected' : ''}>${(a.nombre_area || '').trim()}</option>`
    ).join('');

    row.innerHTML = `
      <td class="col-nombre">
        <div class="cell-edit-wrap">
          <input type="text" class="cell-edit nombre-zona input-enterprise w-full"
            value="${(z.nombre_zona || '').replace(/"/g, '&quot;')}"
            maxlength="125" placeholder="Nombre de zona" />
        </div>
      </td>
      <td class="col-area">
        <div class="cell-edit-wrap">
          <select class="cell-edit area select-zona input-enterprise w-full py-2.5 text-sm" data-initial-value="${(idArea || '').replace(/"/g, '&quot;')}">${optsArea}</select>
        </div>
      </td>
      <td class="col-acciones text-right">
        <div class="acciones-edit">
          <button type="button" class="btn-guardar-zona btn-icon-check p-2 rounded-lg transition" title="Guardar" aria-label="Guardar">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
          </button>
          <button type="button" class="btn-cancelar-zona btn-icon-x p-2 rounded-lg transition" title="Cancelar" aria-label="Cancelar">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>
      </td>
    `;

    row.querySelector('.btn-cancelar-zona').addEventListener('click', () => {
      filaEnEdicion = null;
      cargarZonas();
    });

    enhanceSelectsZona();

    // Asegurar persistencia explícita para el combobox de área en modo edición
    if (typeof ComboboxComponent !== 'undefined' && typeof ComboboxComponent.setInitialValue === 'function') {
      const selAreaEdit = row.querySelector('.cell-edit.area.select-zona');
      if (selAreaEdit) {
        ComboboxComponent.setInitialValue(selAreaEdit, idArea || selAreaEdit.value);
      }
    }

    row.querySelector('.btn-guardar-zona').addEventListener('click', async () => {
      const nombreNuevo = row.querySelector('.cell-edit.nombre-zona').value.trim();
      const areaEl = row.querySelector('.cell-edit.area');
      const idAreaNueva = areaEl ? areaEl.value : undefined;
      if (!nombreNuevo || !idAreaNueva) {
        Toast.fire({ icon: 'warning', title: 'Completa todos los campos.' });
        return;
      }
      if (normalizarTexto(nombreNuevo) === normalizarTexto(z.nombre_zona) && idAreaNueva === String(idArea)) {
        Toast.fire({ icon: 'info', title: 'Sin cambios.' });
        return;
      }
      const fd = new FormData();
      fd.append('accion', 'actualizar');
      fd.append('id_zona', id);
      fd.append('nombre_zona_nueva', nombreNuevo);
      fd.append('id_area_nueva', idAreaNueva);
      try {
        const j = await apiFetch(API_URL, 'POST', fd);
        if (j.status === 'success') {
          filaEnEdicion = null;
          Toast.fire({ icon: 'success', title: 'Zona actualizada.' });
          await Promise.all([cargarZonas(), cargarAreasParaFiltro()]);
        } else {
          Toast.fire({
            icon: esErrorDuplicado(j.message) ? 'warning' : 'error',
            title: j.message || 'Error al actualizar.'
          });
        }
      } catch (err) {
        Toast.fire({ icon: 'error', title: 'Error al actualizar.' });
      }
    });
  }

  /* ========== FILTROS ========== */
  function aplicarFiltros() {
    if (!zonas.length) return;
    const filtro  = document.getElementById('filtroArea');
    const buscador = document.getElementById('buscadorZonas');
    const areaVal  = filtro?.value || 'todas';
    const term     = (buscador?.value || '').toLowerCase().trim();
    const filas    = document.querySelectorAll('#tablaZonas tbody tr');
    document.getElementById('fila-no-resultados')?.remove();
    let visibles = 0;
    filas.forEach(tr => {
      if (tr.children.length === 1) return;
      const nombre   = normalizarTexto(tr.dataset.nombre || tr.children[0]?.textContent || '');
      const areaSpan = tr.querySelector('.tag-pill, td:nth-child(2) span');
      const areaTxt  = areaSpan?.textContent || '';
      const idAreaFila = tr.dataset.idArea || '';
      const coincideArea = areaVal === 'todas' || String(idAreaFila) === String(areaVal);
      const coincideBusq = !term || nombre.includes(normalizarTexto(term)) || normalizarTexto(areaTxt).includes(normalizarTexto(term));
      const mostrar = coincideArea && coincideBusq;
      tr.style.display = mostrar ? '' : 'none';
      if (mostrar) visibles++;
    });
    if (visibles === 0) {
      const tbody = document.querySelector('#tablaZonas tbody');
      if (tbody) {
        const tr = document.createElement('tr');
        tr.id = 'fila-no-resultados';
        tr.innerHTML = '<td colspan="3" class="text-center p-4 text-gray-500">No se encontraron zonas</td>';
        tbody.appendChild(tr);
      }
    }
  }

  function ajustarAltoTabla() {
    const wrap  = document.getElementById('wrapTablaZonas');
    const tabla = document.getElementById('tablaZonas');
    if (!wrap || !tabla) return;
    const thead    = tabla.querySelector('thead');
    const firstRow = tabla.querySelector('tbody tr');
    const filas    = tabla.querySelectorAll('tbody tr').length;
    const headH    = thead    ? thead.getBoundingClientRect().height    : 44;
    const rowH     = firstRow ? firstRow.getBoundingClientRect().height : 56;
    const maxFilas = 5;
    const maxH     = headH + rowH * maxFilas;
    wrap.style.maxHeight        = filas > maxFilas ? `${Math.ceil(maxH)}px` : '';
    wrap.style.overflowY        = filas > maxFilas ? 'auto'    : 'visible';
    wrap.style.overscrollBehavior = filas > maxFilas ? 'contain' : '';
  }

  /* ========== MODAL ========== */
  function openModal() {
    const m = document.getElementById('modalZonas');
    m?.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
  }

  function closeModal() {
    const m    = document.getElementById('modalZonas');
    const form = document.getElementById('formNuevaZona');
    m?.classList.add('hidden');
    document.body.style.overflow = '';
    filaEnEdicion = null;
    if (typeof ComboboxComponent !== 'undefined') ComboboxComponent.reset();
    form?.reset();
    const w = document.querySelector('#id_areaWrap .combobox-wrapper');
    if (w && typeof w._cbUpdateInput === 'function') w._cbUpdateInput();
  }

  /* ========== INIT ========== */
  document.addEventListener('DOMContentLoaded', async () => {
    await Promise.all([cargarAreasParaFiltro(), cargarAreasParaModal(), cargarZonas()]);

    const filtro   = document.getElementById('filtroArea');
    const buscador = document.getElementById('buscadorZonas');
    if (filtro)   filtro.addEventListener('change', aplicarFiltros);
    if (buscador) buscador.addEventListener('input', aplicarFiltros);

    document.getElementById('btnAbrirModalZonas')?.addEventListener('click', openModal);
    document.getElementById('btnCerrarModalZonas')?.addEventListener('click', closeModal);
    document.getElementById('btnCancelarModalZonas')?.addEventListener('click', closeModal);
    document.getElementById('modalBackdrop')?.addEventListener('click', (e) => {
      if (e.target.id === 'modalBackdrop') closeModal();
    });

    document.getElementById('formNuevaZona')?.addEventListener('submit', async (e) => {
      e.preventDefault();
      const form       = e.target;
      const nombre_zona = form.nombre_zona?.value?.trim();
      const idAreaEl   = form.id_area;
      const id_area     = idAreaEl ? idAreaEl.value : undefined;
      const id_area_trim = (id_area || '').trim();
      if (!nombre_zona || !id_area_trim) {
        Toast.fire({ icon: 'warning', title: 'Ingresa el nombre y selecciona un área.' });
        return;
      }
      const fd = new FormData();
      fd.append('accion', 'crear');
      fd.append('nombre_zona', nombre_zona);
      fd.append('id_area', id_area_trim);
      try {
        const j = await apiFetch(API_URL, 'POST', fd);
        if (j.status === 'success') {
          Toast.fire({ icon: 'success', title: j.message || 'Zona creada.' });
          closeModal();
          await Promise.all([cargarZonas(), cargarAreasParaFiltro()]);
        } else {
          Toast.fire({
            icon: esErrorDuplicado(j.message) ? 'warning' : 'error',
            title: j.message || 'No se pudo crear.'
          });
        }
      } catch (err) {
        Toast.fire({ icon: 'error', title: 'Error al crear.' });
      }
    });

    window.addEventListener('resize', ajustarAltoTabla);

    document.getElementById('tablaZonas')?.addEventListener('mousedown', (e) => {
      const trigger = e.target.closest('.combobox-trigger');
      if (!trigger) return;
      const w = trigger.closest('.combobox-wrapper');
      if (w?.parentNode?.querySelector('.select-zona') && typeof w._cbOpen === 'function') {
        w._cbOpen(e);
        e.stopPropagation();
        e.preventDefault();
      }
    }, true);
  });
})();
