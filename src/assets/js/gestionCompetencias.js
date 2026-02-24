// src/assets/js/gestionCompetencias.js
// Gestión exclusiva de Competencias
document.addEventListener('DOMContentLoaded', () => {
  (function () {
    // ===============================
    // CONFIG
    // ===============================
    const API = (window.API_COMPETENCIAS || (window.BASE_URL || '') + 'src/controllers/CompetenciaController.php').replace(/\/+$/, '');
    const API_PROGRAMAS = (window.API_PROGRAMAS || (window.BASE_URL || '') + 'src/controllers/ProgramasController.php').replace(/\/+$/, '');
    const API_RAES = (window.API_RAES || (window.BASE_URL || '') + 'src/controllers/RaeController.php').replace(/\/+$/, '');

    // ===============================
    // SELECTORES
    // ===============================
    const tabCompetencies = document.querySelector('[data-tab="competencies"]');
    if (!tabCompetencies) {
      console.log('Pestaña de competencias no encontrada');
      return;
    }

    const competenciesList = document.getElementById('competenciesList');
    const competenciesEmpty = document.getElementById('competenciesEmpty');
    const competencyProgramFilter = document.getElementById('competencyProgramFilter');
    const competencySearch = document.getElementById('competencySearch');
    const btnNewCompetency = document.getElementById('btnNewCompetency');

    if (!competenciesList) {
      console.error('No se encontró el elemento competenciesList');
      return;
    }

    const modal = document.getElementById('modalCompetency');
    const backdrop = document.getElementById('modalCompetencyBackdrop');
    const form = modal ? modal.querySelector('#formCompetencyNew') : null;
    const inpProgram = modal ? modal.querySelector('#cp_program') : null;
    const inpCode = modal ? modal.querySelector('#cp_code') : null;
    const inpName = modal ? modal.querySelector('#cp_name') : null;
    const btnClose = modal ? modal.querySelector('#btnCloseCompetency') : null;
    const btnCancel = modal ? modal.querySelector('#btnCancelCompetency') : null;
    const modalTitle = modal ? modal.querySelector('#titleCompetency') : null;

    let editingId = null;
    let allCompetencies = [];
    let programs = [];
    let filteredCompetencies = [];
    let raesPorCompetencia = {};
    let currentPage = 1;
    const itemsPerPage = 10;

    const Toast = Swal.mixin({
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 2500,
      timerProgressBar: true
    });

    const t = {
      ok: m => Toast.fire({ icon: 'success', title: m }),
      warn: m => Toast.fire({ icon: 'warning', title: m }),
      err: m => Toast.fire({ icon: 'error', title: m })
    };

    // ===============================
    // FUNCIONES API
    // ===============================
    async function apiListar() {
      try {
        const r = await fetch(`${API}?accion=listar`, {
          credentials: 'same-origin',
          headers: { 'Accept': 'application/json' }
        });
        if (!r.ok) throw new Error(`HTTP ${r.status}`);
        const data = await r.json();
        return data;
      } catch (error) {
        console.error('Error en apiListar:', error);
        throw error;
      }
    }

    async function apiListarProgramas() {
      try {
        const r = await fetch(`${API_PROGRAMAS}?accion=listar`, {
          credentials: 'same-origin',
          headers: { 'Accept': 'application/json' }
        });
        if (!r.ok) throw new Error(`HTTP ${r.status}`);
        const data = await r.json();
        return data;
      } catch (error) {
        console.error('Error cargando programas:', error);
        return [];
      }
    }

    async function apiListarRaesPorCompetencia(id_competencia) {
      try {
        const r = await fetch(`${API_RAES}?accion=porCompetencia&id_competencia=${encodeURIComponent(id_competencia)}`, {
          credentials: 'same-origin',
          headers: { 'Accept': 'application/json' }
        });
        if (!r.ok) throw new Error(`HTTP ${r.status}`);
        const data = await r.json();
        return Array.isArray(data) ? data : [];
      } catch (error) {
        console.error(`Error cargando RAEs para competencia ${id_competencia}:`, error);
        return [];
      }
    }

    async function apiCrear(payload) {
      const r = await fetch(`${API}?accion=crear`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify(payload),
      });
      return r.json();
    }

    async function apiActualizar(payload) {
      const r = await fetch(`${API}?accion=actualizar`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify(payload),
      });
      return r.json();
    }

    async function apiCambiarEstado(id_competencia, estado) {
      const payload = {
        id_competencia: id_competencia,
        estado: estado
      };
      const r = await fetch(`${API}?accion=inhabilitar`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      return r.json();
    }

    // ===============================
    // FUNCIONES AUXILIARES
    // ===============================
    function escapeHtml(s) {
      if (s === null || s === undefined) return '';
      const t = document.createElement('textarea');
      t.textContent = String(s);
      return t.innerHTML;
    }

    function renderSwitch(active, id_competencia) {
      return `
        <label class="switch relative inline-flex items-center cursor-pointer select-none">
          <input type="checkbox" class="peer sr-only estado-switch" data-id="${id_competencia}" ${active ? 'checked' : ''} />
          <span class="block w-11 h-6 rounded-full bg-zinc-300 peer-checked:bg-[#39A900] transition-colors"></span>
          <span class="dot absolute top-0.5 left-0.5 w-5 h-5 rounded-full bg-white shadow-md transition-transform peer-checked:translate-x-5"></span>
        </label>
      `;
    }

    function renderRaesList(raes, competenciaId) {
      if (!raes || raes.length === 0) {
        return `
          <div class="text-sm text-zinc-500 italic py-3 px-3 bg-zinc-50 rounded-lg border border-zinc-200">
            No hay RAEs asociados a esta competencia
          </div>
        `;
      }

      let html = '<div class="space-y-2 mt-3">';
      raes.forEach(rae => {
        html += `
          <div class="flex items-start justify-between bg-white p-3 rounded-lg border border-zinc-200 hover:border-[#0a3a57] transition">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-1">
                <span class="text-xs font-medium text-[#0a3a57] bg-[#39a900] bg-opacity-10 px-2 py-0.5 rounded-full">${escapeHtml(rae.id_rae || rae.codigo || '')}</span>
                <span class="text-xs text-zinc-400">•</span>
                <span class="text-sm text-zinc-700">${escapeHtml(rae.descripcion || '')}</span>
              </div>
            </div>
            <button class="p-1 hover:bg-zinc-100 rounded edit-rae-btn ml-2 flex-shrink-0" data-id="${escapeHtml(rae.id_rae)}" title="Editar RAE">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 3l4 4-7 7H10v-4l7-7z"/>
                <path d="M3 21h18"/>
              </svg>
            </button>
          </div>
        `;
      });
      html += '</div>';

      return html;
    }

    async function toggleRaes(competenciaId, button, container) {
      if (container.style.display === 'none' || container.style.display === '') {
        button.innerHTML = `
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="18 15 12 9 6 15"></polyline>
          </svg>
          Ocultar RAEs
        `;

        container.innerHTML = '<div class="text-sm text-zinc-500 py-4 text-center bg-zinc-50 rounded-lg border border-zinc-200">Cargando RAEs...</div>';
        container.style.display = 'block';

        if (!raesPorCompetencia[competenciaId]) {
          raesPorCompetencia[competenciaId] = await apiListarRaesPorCompetencia(competenciaId);
        }

        container.innerHTML = renderRaesList(raesPorCompetencia[competenciaId], competenciaId);

      } else {
        const count = raesPorCompetencia[competenciaId]?.length || 0;
        button.innerHTML = `
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="6 9 12 15 18 9"></polyline>
          </svg>
          ${count} RAEs
        `;
        container.style.display = 'none';
      }
    }

    function openModal(isCreate = true, data = null) {
      editingId = isCreate ? null : (data?.id_competencia ?? null);

      if (inpProgram) {
        inpProgram.innerHTML = '<option value="">Seleccione un programa</option>';
        programs.forEach(p => {
          const selected = (!isCreate && data?.id_programa == p.id_programa) ? 'selected' : '';
          inpProgram.innerHTML += `<option value="${p.id_programa}" ${selected}>${escapeHtml(p.nombre_programa)}</option>`;
        });
      }

      if (inpCode) inpCode.value = isCreate ? '' : (data?.id_competencia ?? data?.codigo ?? '');
      if (inpName) inpName.value = isCreate ? '' : (data?.nombre_competencia ?? data?.nombre ?? '');

      if (modalTitle) modalTitle.textContent = isCreate ? 'Nueva Competencia' : 'Editar Competencia';

      modal?.classList.remove('hidden');
      backdrop?.classList.remove('hidden');
    }

    function closeModal() {
      modal?.classList.add('hidden');
      backdrop?.classList.add('hidden');
      if (form) form.reset();
      editingId = null;
    }

    function filtrarCompetencias() {
      if (!allCompetencies.length) return [];

      const programa = competencyProgramFilter?.value || 'all';
      const busqueda = competencySearch?.value.toLowerCase() || '';

      filteredCompetencies = allCompetencies.filter(c => {
        if (programa !== 'all') {
          const programaId = parseInt(programa);
          const compProgramaId = parseInt(c.id_programa);
          if (compProgramaId !== programaId) return false;
        }

        if (busqueda) {
          const nombre = (c.nombre_competencia || c.nombre || '').toLowerCase();
          const codigo = (c.id_competencia || c.codigo || '').toLowerCase();
          return nombre.includes(busqueda) || codigo.includes(busqueda);
        }

        return true;
      });

      return filteredCompetencies;
    }

    function getProgramaNombre(id_programa) {
      if (!id_programa) return 'Programa de Formación';
      const programa = programs.find(p => parseInt(p.id_programa) === parseInt(id_programa));
      return programa ? programa.nombre_programa : 'Programa de Formación';
    }

    // ===============================
    // TARJETA DE COMPETENCIA
    // ===============================
    function createCard(c) {
      const activo = String(c.estado) === '1' || String(c.estado).toLowerCase() === 'true' || c.estado === true;
      const programaNombre = getProgramaNombre(c.id_programa);
      const nombreCompetencia = c.nombre_competencia || c.nombre || 'Sin nombre';
      const codigoCompetencia = c.id_competencia || c.codigo || 'Sin código';

      const raesCount = raesPorCompetencia[c.id_competencia]?.length || 0;

      const card = document.createElement('div');
      card.className = 'bg-white border border-zinc-200 rounded-2xl p-5 shadow-sm hover:shadow transition';
      card.setAttribute('data-id', c.id_competencia);

      card.innerHTML = `
        <div class="flex items-start justify-between gap-4">
          <div class="flex-1">
            <div class="mb-3">
              <h3 class="text-xl font-semibold text-zinc-800 mb-1">${escapeHtml(nombreCompetencia)}</h3>
              <div class="flex items-center gap-2 text-sm text-zinc-600 flex-wrap">
                <span>Código: ${escapeHtml(codigoCompetencia)}</span>
                <span class="text-zinc-300">|</span>
                <span>${escapeHtml(programaNombre)}</span>
                <span class="text-zinc-300">|</span>
                <span class="flex items-center gap-1">
                  <span class="w-2 h-2 rounded-full ${activo ? 'bg-[#39A900]' : 'bg-zinc-400'}"></span>
                  ${activo ? 'Activo' : 'Inactivo'}
                </span>
              </div>
            </div>
            
            <div class="flex items-center justify-between mt-2">
              <button class="flex items-center gap-2 text-sm text-[#0a3a57] hover:text-[#052433] font-medium toggle-raes-btn">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
                ${raesCount} RAEs
              </button>
              
              <div class="flex items-center gap-2">
                <button class="p-2 hover:bg-zinc-100 rounded-lg edit-btn" title="Editar">
                  <img src="src/assets/img/pencil-line.svg" alt="Editar" class="w-4 h-4">
                </button>
                ${renderSwitch(activo, c.id_competencia)}
              </div>
            </div>
            
            <div class="raes-container mt-3" style="display: none;"></div>
          </div>
        </div>
      `;

      const editBtn = card.querySelector('.edit-btn');
      editBtn?.addEventListener('click', (e) => {
        e.stopPropagation();
        openModal(false, c);
      });

      const sw = card.querySelector('.estado-switch');
      sw?.addEventListener('change', async (e) => {
        e.stopPropagation();
        const checked = sw.checked;
        const nuevoEstado = checked ? 1 : 0;

        try {
          const res = await apiCambiarEstado(c.id_competencia, nuevoEstado);
          if (res?.error) {
            t.err(res.error);
            sw.checked = !checked;
          } else {
            t.ok(checked ? 'Competencia activada' : 'Competencia inhabilitada');
            await loadCompetencies();
          }
        } catch (error) {
          console.error('Error cambiando estado:', error);
          t.err('No se pudo cambiar el estado.');
          sw.checked = !checked;
        }
      });

      const toggleBtn = card.querySelector('.toggle-raes-btn');
      const raesContainer = card.querySelector('.raes-container');

      toggleBtn?.addEventListener('click', async (e) => {
        e.stopPropagation();
        await toggleRaes(c.id_competencia, toggleBtn, raesContainer);
      });

      return card;
    }

    function renderList(list) {
      if (!competenciesList) return;

      competenciesList.innerHTML = '';

      if (!Array.isArray(list) || list.length === 0) {
        if (competenciesEmpty) {
          competenciesEmpty.classList.remove('hidden');
          competenciesEmpty.innerHTML = `
            <div class="py-12 text-center text-zinc-500">
              No hay competencias que coincidan con el filtro seleccionado.
            </div>
          `;
        }
        return;
      }

      if (competenciesEmpty) {
        competenciesEmpty.classList.add('hidden');
      }

      const start = (currentPage - 1) * itemsPerPage;
      const end = start + itemsPerPage;
      const paginatedList = list.slice(start, end);

      const frag = document.createDocumentFragment();

      Promise.all(paginatedList.map(c =>
        apiListarRaesPorCompetencia(c.id_competencia).then(raes => {
          raesPorCompetencia[c.id_competencia] = raes;
        })
      )).then(() => {
        paginatedList.forEach(c => {
          try {
            frag.appendChild(createCard(c));
          } catch (error) {
            console.error('Error creando tarjeta:', error);
          }
        });
        competenciesList.appendChild(frag);
        updatePagination(list.length);
      });
    }

    // ===============================
    // CARGA DE DATOS
    // ===============================
    async function loadPrograms() {
      try {
        const data = await apiListarProgramas();
        programs = Array.isArray(data) ? data : [];

        if (competencyProgramFilter) {
          competencyProgramFilter.innerHTML = '<option value="all">Todos los programas</option>';
          programs.forEach(p => {
            competencyProgramFilter.innerHTML += `<option value="${p.id_programa}">${escapeHtml(p.nombre_programa)}</option>`;
          });
        }

        if (inpProgram) {
          inpProgram.innerHTML = '<option value="">Seleccione un programa</option>';
          programs.forEach(p => {
            inpProgram.innerHTML += `<option value="${p.id_programa}">${escapeHtml(p.nombre_programa)}</option>`;
          });
        }
      } catch (e) {
        console.error('Error cargando programas:', e);
      }
    }

    async function loadCompetencies() {
      try {
        if (competenciesList) {
          competenciesList.innerHTML = '<div class="text-center py-8 text-zinc-500">Cargando competencias...</div>';
        }

        const data = await apiListar();
        allCompetencies = Array.isArray(data) ? data : [];

        raesPorCompetencia = {};

        const filtrados = filtrarCompetencias();
        renderList(filtrados);

      } catch (error) {
        console.error('Error en loadCompetencies:', error);
        if (competenciesEmpty) {
          competenciesEmpty.classList.remove('hidden');
          competenciesEmpty.innerHTML = '<div class="py-12 text-center text-red-600">Error al cargar competencias.</div>';
        }
      }
    }

    // ===============================
    // PAGINACIÓN
    // ===============================
    const cpPrev = document.getElementById('cpPrev');
    const cpNext = document.getElementById('cpNext');
    const cpInfo = document.getElementById('cpInfo');
    const paginationContainer = document.getElementById('competenciasPagination');

    function updatePagination(totalItems) {
      if (!cpInfo || !paginationContainer) return;

      const totalPages = Math.ceil(totalItems / itemsPerPage) || 1;

      if (totalItems <= itemsPerPage) {
        paginationContainer.classList.add('hidden');
        return;
      }

      paginationContainer.classList.remove('hidden');
      cpInfo.textContent = `Página ${currentPage} de ${totalPages} · ${totalItems} items`;

      if (cpPrev) cpPrev.disabled = currentPage <= 1;
      if (cpNext) cpNext.disabled = currentPage >= totalPages;
    }

    if (cpPrev) {
      cpPrev.addEventListener('click', () => {
        if (currentPage > 1) {
          currentPage--;
          const filtrados = filtrarCompetencias();
          renderList(filtrados);
        }
      });
    }

    if (cpNext) {
      cpNext.addEventListener('click', () => {
        const filtrados = filtrarCompetencias();
        const totalPages = Math.ceil(filtrados.length / itemsPerPage) || 1;
        if (currentPage < totalPages) {
          currentPage++;
          renderList(filtrados);
        }
      });
    }

    // ===============================
    // EVENTOS
    // ===============================
    if (btnNewCompetency) {
      btnNewCompetency.addEventListener('click', () => openModal(true));
    }

    if (btnClose) {
      btnClose.addEventListener('click', closeModal);
    }

    if (btnCancel) {
      btnCancel.addEventListener('click', (e) => {
        e.preventDefault();
        closeModal();
      });
    }

    if (competencyProgramFilter) {
      competencyProgramFilter.addEventListener('change', () => {
        currentPage = 1;
        const filtrados = filtrarCompetencias();
        renderList(filtrados);
      });
    }

    if (competencySearch) {
      competencySearch.addEventListener('input', () => {
        currentPage = 1;
        const filtrados = filtrarCompetencias();
        renderList(filtrados);
      });
    }

    if (form) {
      form.addEventListener('submit', async e => {
        e.preventDefault();

        const id_programa = inpProgram?.value;
        const id_competencia = (inpCode?.value || '').trim();
        const nombre_competencia = (inpName?.value || '').trim();

        if (!id_programa) return t.warn('Seleccione un programa');
        if (!id_competencia) return t.warn('El código es obligatorio');
        if (!nombre_competencia) return t.warn('El nombre es obligatorio');

        const payload = {
          id_competencia: id_competencia,
          id_programa: id_programa,
          nombre_competencia: nombre_competencia
        };

        try {
          let res;
          if (editingId) {
            payload.id_competencia = editingId;
            payload.nuevo_id_competencia = id_competencia;
            res = await apiActualizar(payload);
          } else {
            res = await apiCrear(payload);
          }

          if (res?.error) {
            return t.err(res.error);
          }

          closeModal();
          t.ok(editingId ? 'Competencia actualizada' : 'Competencia creada');
          
          // SOLO UNA LLAMADA - la más simple
          currentPage = 1;
          await loadCompetencies();

        } catch (error) {
          console.error('Error guardando competencia:', error);
          t.err('Error al guardar');
        }
      });
    }

    if (backdrop) {
      backdrop.addEventListener('click', closeModal);
    }

    // ===============================
    // EVENTOS GLOBALES
    // ===============================
    window.addEventListener('excel-subido-ok', () => {
      loadCompetencies();
    });

    window.addEventListener('programs:changed', () => {
      loadPrograms();
    });

    window.addEventListener('competencias:changed', () => {
      console.log('Evento competencias:changed recibido - recargando');
      loadCompetencies();
    });

    window.addEventListener('raes:changed', () => {
      console.log('Evento raes:changed recibido - recargando competencias');
      raesPorCompetencia = {};
      loadCompetencies();
    });

    // ===============================
    // INICIO
    // ===============================
    Promise.all([loadPrograms(), loadCompetencies()]);

  })();
});