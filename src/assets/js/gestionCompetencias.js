// src/assets/js/gestionCompetencias.js
// Gestión exclusiva de Competencias
document.addEventListener('DOMContentLoaded', () => {
  (function() {
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

    function escapeHtml(s) {
      if (s === null || s === undefined) return '';
      const t = document.createElement('textarea');
      t.textContent = String(s);
      return t.innerHTML;
    }

    function renderSwitch(active) {
      return `
        <label class="switch relative inline-flex items-center cursor-pointer select-none">
          <input type="checkbox" class="peer sr-only" ${active ? 'checked' : ''} />
          <span class="block w-11 h-6 rounded-full bg-zinc-300 peer-checked:bg-[#39A900] transition-colors"></span>
          <span class="dot absolute top-0.5 left-0.5 w-5 h-5 rounded-full bg-white shadow-md transition-transform peer-checked:translate-x-5"></span>
        </label>
      `;
    }

    function renderRaesList(raes, competenciaId) {
      if (!raes || raes.length === 0) {
        return `
          <div class="text-sm text-zinc-500 italic py-2">
            No hay RAEs asociados a esta competencia
          </div>
        `;
      }

      let html = '<div class="space-y-2 mt-3">';
      raes.forEach(rae => {
        html += `
          <div class="flex items-center justify-between bg-zinc-50 p-3 rounded-lg border border-zinc-200">
            <div class="flex-1">
              <div class="flex items-center gap-2">
                <span class="text-xs font-medium text-zinc-500">${escapeHtml(rae.id_rae || rae.codigo || '')}</span>
                <span class="text-sm">${escapeHtml(rae.descripcion || '')}</span>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <button class="p-1 hover:bg-zinc-200 rounded edit-rae-btn" data-id="${escapeHtml(rae.id_rae)}" title="Editar RAE">
                <img src="src/assets/img/pencil-line.svg" alt="Editar" class="w-3 h-3">
              </button>
            </div>
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
        
        container.innerHTML = '<div class="text-sm text-zinc-500 py-2">Cargando RAEs...</div>';
        container.style.display = 'block';
        
        if (!raesPorCompetencia[competenciaId]) {
          raesPorCompetencia[competenciaId] = await apiListarRaesPorCompetencia(competenciaId);
        }
        
        container.innerHTML = renderRaesList(raesPorCompetencia[competenciaId], competenciaId);
        
      } else {
        button.innerHTML = `
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="6 9 12 15 18 9"></polyline>
          </svg>
          Ver RAEs (${raesPorCompetencia[competenciaId]?.length || 0})
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
      if (!id_programa) return 'Sin programa';
      const programa = programs.find(p => parseInt(p.id_programa) === parseInt(id_programa));
      return programa ? programa.nombre_programa : 'Programa no encontrado';
    }

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
            <div class="flex items-center gap-3 flex-wrap mb-2">
              <h3 class="text-lg font-semibold">${escapeHtml(nombreCompetencia)}</h3>
              <span class="text-sm text-zinc-500">Código: ${escapeHtml(codigoCompetencia)}</span>
            </div>
            <p class="text-sm text-zinc-600 mb-3">${escapeHtml(c.descripcion || nombreCompetencia)}</p>
            <div class="flex items-center gap-4 text-sm">
              <span class="px-3 py-1 bg-zinc-100 rounded-full">${escapeHtml(programaNombre)}</span>
              <span class="text-zinc-500">Horas: ${c.horas || 0}</span>
            </div>
            
            <button class="flex items-center gap-1 text-sm text-[#0a3a57] hover:text-[#052433] mt-3 toggle-raes-btn">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="6 9 12 15 18 9"></polyline>
              </svg>
              Ver RAEs (${raesCount})
            </button>
            
            <div class="raes-container mt-2" style="display: none;"></div>
          </div>
          
          <div class="flex items-center gap-2">
            <button class="p-2 hover:bg-zinc-100 rounded-lg edit-btn" title="Editar">
              <img src="src/assets/img/pencil-line.svg" alt="Editar" class="w-4 h-4">
            </button>
            ${renderSwitch(activo)}
          </div>
        </div>
      `;

      const editBtn = card.querySelector('.edit-btn');
      editBtn?.addEventListener('click', (e) => {
        e.stopPropagation();
        openModal(false, c);
      });

      const sw = card.querySelector('input[type="checkbox"]');
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
            c.estado = nuevoEstado;
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
        
        if (raesPorCompetencia[c.id_competencia]) {
          const count = raesPorCompetencia[c.id_competencia].length;
          toggleBtn.innerHTML = `
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
            ${raesContainer.style.display === 'none' ? 'Ver' : 'Ocultar'} RAEs (${count})
          `;
        }
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
      
      const frag = document.createDocumentFragment();
      list.forEach(c => {
        try {
          frag.appendChild(createCard(c));
        } catch (error) {
          console.error('Error creando tarjeta:', error);
        }
      });
      competenciesList.appendChild(frag);
    }

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
        const filtrados = filtrarCompetencias();
        renderList(filtrados);
      });
    }

    if (competencySearch) {
      competencySearch.addEventListener('input', () => {
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

    const cpPrev = document.getElementById('cpPrev');
    const cpNext = document.getElementById('cpNext');
    const cpInfo = document.getElementById('cpInfo');
    
    let currentPage = 1;
    const itemsPerPage = 10;

    function updatePagination() {
      if (!cpInfo) return;
      const totalPages = Math.ceil(filteredCompetencies.length / itemsPerPage) || 1;
      cpInfo.textContent = `Página ${currentPage} de ${totalPages}`;
      
      if (cpPrev) cpPrev.disabled = currentPage <= 1;
      if (cpNext) cpNext.disabled = currentPage >= totalPages;
    }

    if (cpPrev) {
      cpPrev.addEventListener('click', () => {
        if (currentPage > 1) {
          currentPage--;
          const start = (currentPage - 1) * itemsPerPage;
          const end = start + itemsPerPage;
          renderList(filteredCompetencies.slice(start, end));
          updatePagination();
        }
      });
    }

    if (cpNext) {
      cpNext.addEventListener('click', () => {
        const totalPages = Math.ceil(filteredCompetencies.length / itemsPerPage) || 1;
        if (currentPage < totalPages) {
          currentPage++;
          const start = (currentPage - 1) * itemsPerPage;
          const end = start + itemsPerPage;
          renderList(filteredCompetencies.slice(start, end));
          updatePagination();
        }
      });
    }

    Promise.all([loadPrograms(), loadCompetencies()]);

    window.addEventListener('excel-subido-ok', () => {
      loadCompetencies();
    });

    window.addEventListener('programs:changed', () => {
      loadPrograms();
    });
  })();
});