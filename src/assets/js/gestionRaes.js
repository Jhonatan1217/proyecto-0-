// src/assets/js/gestionRaes.js
// Gestión exclusiva de RAEs (Resultados de Aprendizaje Esperados)
document.addEventListener('DOMContentLoaded', () => {
  (function() {
    // ===============================
    // CONFIG
    // ===============================
    const API = (window.API_RAES || (window.BASE_URL || '') + 'src/controllers/RaeController.php').replace(/\/+$/, '');
    const API_PROGRAMAS = (window.API_PROGRAMAS || (window.BASE_URL || '') + 'src/controllers/ProgramasController.php').replace(/\/+$/, '');
    const API_COMPETENCIAS = (window.API_COMPETENCIAS || (window.BASE_URL || '') + 'src/controllers/CompetenciaController.php').replace(/\/+$/, '');

    // ===============================
    // SELECTORES
    // ===============================
    const tabRaes = document.querySelector('[data-tab="raes"]');
    if (!tabRaes) {
      console.log('Pestaña de RAEs no encontrada');
      return;
    }

    const raesList = document.getElementById('raesList');
    const raesEmpty = document.getElementById('raesEmpty');
    const raeProgramFilter = document.getElementById('raeProgramFilter');
    const raeCompetencyFilter = document.getElementById('raeCompetencyFilter');
    const btnNewRae = document.getElementById('btnNewRae');
    
    // Modal elementos
    const modal = document.getElementById('modalRae');
    const backdrop = document.getElementById('modalRaeBackdrop');
    const form = modal ? modal.querySelector('#formRaeNew') : null;
    const inpProgram = modal ? modal.querySelector('#rae_program') : null;
    const inpCompetency = modal ? modal.querySelector('#rae_competency') : null;
    const inpCode = modal ? modal.querySelector('#rae_code') : null;
    const inpDesc = modal ? modal.querySelector('#rae_desc') : null;
    const btnClose = modal ? modal.querySelector('#btnCloseRae') : null;
    const btnCancel = modal ? modal.querySelector('#btnCancelRae') : null;
    const modalTitle = modal ? modal.querySelector('#modalRaeTitle') : null;

    let editingId = null;
    let allRaes = [];
    let programs = [];
    let competencies = [];
    let filteredRaes = [];
    let currentPage = 1;
    const itemsPerPage = 10;

    // ===============================
    // TOASTS
    // ===============================
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
    // API HELPERS
    // ===============================
    async function apiRequest(accion, method = 'GET', data = null) {
      let url = `${API}?accion=${accion}`;
      const options = {
        method: method,
        credentials: 'same-origin',
        headers: method !== 'GET' ? { 'Content-Type': 'application/json' } : {}
      };
      
      if (method === 'GET' && data) {
        const params = new URLSearchParams(data);
        url += '&' + params.toString();
      }
      
      if (method !== 'GET' && data) {
        options.body = JSON.stringify(data);
      }
      
      const r = await fetch(url, options);
      if (!r.ok) throw new Error(`HTTP ${r.status}`);
      return r.json();
    }

    async function apiListar() {
      return apiRequest('listar', 'GET');
    }

    async function apiListarProgramas() {
      const r = await fetch(`${API_PROGRAMAS}?accion=listar`, { 
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json' }
      });
      if (!r.ok) throw new Error(`HTTP ${r.status}`);
      return r.json();
    }

    async function apiListarCompetencias(programaId = null) {
      let url = `${API_COMPETENCIAS}?accion=listar`;
      if (programaId) url += `&id_programa=${programaId}`;
      const r = await fetch(url, { 
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json' }
      });
      if (!r.ok) throw new Error(`HTTP ${r.status}`);
      return r.json();
    }

    async function apiCrear(payload) {
      return apiRequest('crear', 'POST', payload);
    }

    async function apiActualizar(payload) {
      return apiRequest('actualizar', 'POST', payload);
    }

    async function apiCambiarEstado(id_rae, estado) {
      return apiRequest('inhabilitar', 'POST', { id_rae, estado });
    }

    // ===============================
    // UI HELPERS
    // ===============================
    function escapeHtml(s) {
      if (s === null || s === undefined) return '';
      const t = document.createElement('textarea');
      t.textContent = String(s);
      return t.innerHTML;
    }

    function renderSwitch(active, id_rae) {
      return `
        <label class="switch relative inline-flex items-center cursor-pointer select-none">
          <input type="checkbox" class="peer sr-only estado-switch" data-id="${id_rae}" ${active ? 'checked' : ''} />
          <span class="block w-11 h-6 rounded-full bg-zinc-300 peer-checked:bg-[#39A900] transition-colors"></span>
          <span class="dot absolute top-0.5 left-0.5 w-5 h-5 rounded-full bg-white shadow-md transition-transform peer-checked:translate-x-5"></span>
        </label>
      `;
    }

    function openModal(isCreate = true, data = null) {
      editingId = isCreate ? null : (data?.id_rae ?? null);
      
      // Resetear selects
      if (inpProgram) {
        inpProgram.innerHTML = '<option value="">Seleccione programa</option>';
        programs.forEach(p => {
          const selected = (!isCreate && data?.id_programa == p.id_programa) ? 'selected' : '';
          inpProgram.innerHTML += `<option value="${p.id_programa}" ${selected}>${escapeHtml(p.nombre_programa)}</option>`;
        });
        inpProgram.disabled = false;
      }

      // Cargar competencias si es edición
      if (!isCreate && data?.id_programa) {
        cargarCompetenciasPorPrograma(data.id_programa, data.id_competencia);
      } else if (inpCompetency) {
        inpCompetency.innerHTML = '<option value="">Primero seleccione un programa</option>';
        inpCompetency.disabled = true;
      }

      if (inpCode) inpCode.value = isCreate ? '' : (data?.id_rae ?? '');
      if (inpDesc) inpDesc.value = isCreate ? '' : (data?.descripcion ?? '');

      if (modalTitle) modalTitle.textContent = isCreate ? 'Nuevo RAE' : 'Editar RAE';

      modal?.classList.remove('hidden');
      backdrop?.classList.remove('hidden');
    }

    function closeModal() {
      modal?.classList.add('hidden');
      backdrop?.classList.add('hidden');
      if (form) form.reset();
      editingId = null;
      if (inpCompetency) {
        inpCompetency.innerHTML = '<option value="">Primero seleccione un programa</option>';
        inpCompetency.disabled = true;
      }
    }

    async function cargarCompetenciasPorPrograma(programaId, selectedCompetenciaId = null) {
      if (!programaId || !inpCompetency) return;
      
      try {
        const data = await apiListarCompetencias(programaId);
        const comps = Array.isArray(data) ? data : [];
        
        inpCompetency.innerHTML = '<option value="">Seleccione una competencia</option>';
        inpCompetency.disabled = false;
        
        comps.forEach(c => {
          const selected = (selectedCompetenciaId && c.id_competencia == selectedCompetenciaId) ? 'selected' : '';
          inpCompetency.innerHTML += `<option value="${c.id_competencia}" ${selected}>${escapeHtml(c.nombre_competencia || c.nombre || 'Sin nombre')}</option>`;
        });
      } catch (error) {
        console.error('Error cargando competencias:', error);
      }
    }

    function filtrarRaes() {
      if (!allRaes.length) return [];

      const programa = raeProgramFilter?.value || 'all';
      const competencia = raeCompetencyFilter?.value || 'all';

      filteredRaes = allRaes.filter(r => {
        if (programa !== 'all' && r.id_programa != programa) return false;
        if (competencia !== 'all' && r.id_competencia != competencia) return false;
        return true;
      });

      return filteredRaes;
    }

    function getCompetenciaNombre(id_competencia) {
      if (!id_competencia) return 'Sin competencia';
      const comp = competencies.find(c => c.id_competencia == id_competencia);
      return comp ? (comp.nombre_competencia || comp.nombre || 'Competencia') : 'Competencia no encontrada';
    }

    function getProgramaNombre(id_programa) {
      if (!id_programa) return 'Sin programa';
      const prog = programs.find(p => p.id_programa == id_programa);
      return prog ? prog.nombre_programa : 'Programa no encontrado';
    }

    function createCard(r) {
      const activo = r.estado == 1 || r.estado === true || String(r.estado) === '1';
      const competenciaNombre = getCompetenciaNombre(r.id_competencia);
      const programaNombre = getProgramaNombre(r.id_programa);

      const card = document.createElement('div');
      card.className = 'bg-white border border-zinc-200 rounded-2xl p-5 shadow-sm hover:shadow transition';
      card.setAttribute('data-id', r.id_rae);

      card.innerHTML = `
        <div class="flex items-start justify-between gap-4">
          <div class="flex-1">
            <div class="flex items-center gap-3 flex-wrap mb-2">
              <h3 class="text-lg font-semibold">${escapeHtml(r.descripcion || '')}</h3>
              <span class="text-sm text-zinc-500">Código: ${escapeHtml(r.id_rae || '')}</span>
            </div>
            <div class="flex items-center gap-2 text-sm flex-wrap">
              <span class="px-3 py-1 bg-zinc-100 rounded-full">${escapeHtml(programaNombre)}</span>
              <span class="px-3 py-1 bg-zinc-100 rounded-full">${escapeHtml(competenciaNombre)}</span>
            </div>
          </div>
          <div class="flex items-center gap-2">
            <button class="p-2 hover:bg-zinc-100 rounded-lg edit-btn" title="Editar">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 3l4 4-7 7H10v-4l7-7z"/>
                <path d="M3 21h18"/>
              </svg>
            </button>
            ${renderSwitch(activo, r.id_rae)}
          </div>
        </div>
      `;

      const editBtn = card.querySelector('.edit-btn');
      editBtn?.addEventListener('click', (e) => {
        e.stopPropagation();
        openModal(false, r);
      });

      const sw = card.querySelector('.estado-switch');
sw?.addEventListener('change', async (e) => {
    e.stopPropagation();
    const checked = sw.checked;
    const nuevoEstado = checked ? 1 : 0;
    
    try {
        const res = await apiCambiarEstado(r.id_rae, nuevoEstado);
        if (res?.error) {
            t.err(res.error);
            sw.checked = !checked;
        } else {
            t.ok(checked ? 'RAE activado' : 'RAE inhabilitado');
            
            window.dispatchEvent(new CustomEvent('raes:changed'));
            
            await loadRaes();
        }
    } catch (error) {
        console.error('Error cambiando estado:', error);
        t.err('No se pudo cambiar el estado.');
        sw.checked = !checked;
    }
});

      return card;
    }

    function renderList(list) {
      if (!raesList) return;

      raesList.innerHTML = '';

      if (!Array.isArray(list) || list.length === 0) {
        if (raesEmpty) {
          raesEmpty.classList.remove('hidden');
        }
        return;
      }

      if (raesEmpty) {
        raesEmpty.classList.add('hidden');
      }
      
      const start = (currentPage - 1) * itemsPerPage;
      const end = start + itemsPerPage;
      const paginatedList = list.slice(start, end);
      
      const frag = document.createDocumentFragment();
      paginatedList.forEach(r => {
        try {
          frag.appendChild(createCard(r));
        } catch (error) {
          console.error('Error creando tarjeta para RAE:', r, error);
        }
      });
      raesList.appendChild(frag);
      
      updatePagination(list.length);
    }

    // ===============================
    // CARGA DE DATOS
    // ===============================
    async function loadPrograms() {
      try {
        const data = await apiListarProgramas();
        programs = Array.isArray(data) ? data : [];

        if (raeProgramFilter) {
          raeProgramFilter.innerHTML = '<option value="all">Todos los programas</option>';
          programs.forEach(p => {
            raeProgramFilter.innerHTML += `<option value="${p.id_programa}">${escapeHtml(p.nombre_programa)}</option>`;
          });
        }

        if (inpProgram) {
          inpProgram.innerHTML = '<option value="">Seleccione programa</option>';
          programs.forEach(p => {
            inpProgram.innerHTML += `<option value="${p.id_programa}">${escapeHtml(p.nombre_programa)}</option>`;
          });
        }
      } catch (e) {
        console.error('Error cargando programas:', e);
      }
    }

    async function loadCompetencias(programaId = null) {
      try {
        const data = await apiListarCompetencias(programaId);
        competencies = Array.isArray(data) ? data : [];

        if (raeCompetencyFilter) {
          raeCompetencyFilter.innerHTML = '<option value="all">Todas las competencias</option>';
          competencies.forEach(c => {
            raeCompetencyFilter.innerHTML += `<option value="${c.id_competencia}">${escapeHtml(c.nombre_competencia || c.nombre || 'Sin nombre')}</option>`;
          });
        }
      } catch (e) {
        console.error('Error cargando competencias:', e);
      }
    }

    async function loadRaes() {
      try {
        if (raesList) {
          raesList.innerHTML = '<div class="text-center py-8 text-zinc-500">Cargando RAEs...</div>';
        }

        const data = await apiListar();
        allRaes = Array.isArray(data) ? data : [];
        
        const filtrados = filtrarRaes();
        renderList(filtrados);
        
      } catch (error) {
        console.error('Error en loadRaes:', error);
        if (raesEmpty) {
          raesEmpty.classList.remove('hidden');
          raesEmpty.innerHTML = '<div class="py-12 text-center text-red-600">Error al cargar RAEs</div>';
        }
      }
    }

    // ===============================
    // PAGINACIÓN
    // ===============================
    const raePrev = document.getElementById('raePrev');
    const raeNext = document.getElementById('raeNext');
    const raeInfo = document.getElementById('raeInfo');
    const paginationContainer = document.getElementById('raePagination');

    function updatePagination(totalItems) {
      if (!raeInfo || !paginationContainer) return;
      
      const totalPages = Math.ceil(totalItems / itemsPerPage) || 1;
      
      if (totalItems <= itemsPerPage) {
        paginationContainer.classList.add('hidden');
        return;
      }
      
      paginationContainer.classList.remove('hidden');
      raeInfo.textContent = `Página ${currentPage} de ${totalPages}`;
      
      if (raePrev) raePrev.disabled = currentPage <= 1;
      if (raeNext) raeNext.disabled = currentPage >= totalPages;
    }

    if (raePrev) {
      raePrev.addEventListener('click', () => {
        if (currentPage > 1) {
          currentPage--;
          const filtrados = filtrarRaes();
          renderList(filtrados);
        }
      });
    }

    if (raeNext) {
      raeNext.addEventListener('click', () => {
        const filtrados = filtrarRaes();
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
    if (btnNewRae) {
      btnNewRae.addEventListener('click', () => openModal(true));
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

    if (backdrop) {
      backdrop.addEventListener('click', closeModal);
    }

    if (raeProgramFilter) {
      raeProgramFilter.addEventListener('change', async () => {
        currentPage = 1;
        const programaId = raeProgramFilter.value !== 'all' ? raeProgramFilter.value : null;
        await loadCompetencias(programaId);
        await loadRaes();
      });
    }

    if (raeCompetencyFilter) {
      raeCompetencyFilter.addEventListener('change', () => {
        currentPage = 1;
        loadRaes();
      });
    }

    if (inpProgram) {
      inpProgram.addEventListener('change', function() {
        const programaId = this.value;
        if (programaId) {
          cargarCompetenciasPorPrograma(programaId);
        } else {
          if (inpCompetency) {
            inpCompetency.innerHTML = '<option value="">Primero seleccione un programa</option>';
            inpCompetency.disabled = true;
          }
        }
      });
    }

    if (form) {
    form.addEventListener('submit', async e => {
        e.preventDefault();

        const id_programa = inpProgram?.value;
        const id_competencia = inpCompetency?.value;
        const id_rae = (inpCode?.value || '').trim();
        const descripcion = (inpDesc?.value || '').trim();

        if (!id_programa) return t.warn('Seleccione un programa');
        if (!id_competencia) return t.warn('Seleccione una competencia');
        if (!id_rae) return t.warn('El código es obligatorio');
        if (!descripcion) return t.warn('La descripción es obligatoria');

        const payload = {
            id_rae: id_rae,
            id_competencia: id_competencia,
            descripcion: descripcion
        };

        try {
            let res;
            if (editingId) {
                payload.id_rae = editingId;
                payload.nuevo_id_rae = id_rae;
                res = await apiActualizar(payload);
            } else {
                res = await apiCrear(payload);
            }
            
            if (res?.error) {
                return t.err(res.error);
            }

            closeModal();
            t.ok(editingId ? 'RAE actualizado' : 'RAE creado');
            
            window.dispatchEvent(new CustomEvent('raes:changed'));
            
            await loadRaes();
            
        } catch (error) {
            console.error('Error guardando RAE:', error);
            t.err('Error al guardar');
        }
    });
    }

    // ===============================
    // INIT
    // ===============================
    Promise.all([loadPrograms(), loadCompetencias(), loadRaes()])
      .then(() => {
        console.log('Datos de RAEs cargados correctamente');
      })
      .catch(error => {
        console.error('Error en carga inicial:', error);
      });

    window.addEventListener('excel-subido-ok', () => {
      loadRaes();
    });

    window.addEventListener('programs:changed', () => {
      loadPrograms();
      loadCompetencias();
    });
  })();
});