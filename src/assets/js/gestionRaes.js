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
    const btnNewRae = document.querySelector('[data-tab="raes"] button.bg-\\[\\#0a3a57\\]');
    
    // Modal elementos
    const modal = document.getElementById('modalRae');
    const backdrop = document.getElementById('modalRaeBackdrop');
    const form = modal ? modal.querySelector('#formRaeNew') : null;
    const inpCompetency = modal ? modal.querySelector('#rae_competency') : null;
    const inpCode = modal ? modal.querySelector('#rae_code') : null;
    const inpDesc = modal ? modal.querySelector('#rae_desc') : null;
    const btnClose = modal ? modal.querySelector('#btnCloseRae') : null;
    const btnCancel = modal ? modal.querySelector('#btnCancelRae') : null;

    let editingId = null;
    let allRaes = [];
    let programs = [];
    let competencies = [];
    let filteredRaes = [];

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
    async function apiListar() {
      try {
        const r = await fetch(`${API}?accion=listar`, { 
          credentials: 'same-origin',
          headers: { 'Accept': 'application/json' }
        });
        if (!r.ok) throw new Error(`HTTP ${r.status}`);
        const data = await r.json();
        console.log('RAEs recibidos:', data);
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
        console.log('Programas recibidos:', data);
        return data;
      } catch (error) {
        console.error('Error cargando programas:', error);
        return [];
      }
    }

    async function apiListarCompetencias(programaId = null) {
      try {
        let url = `${API_COMPETENCIAS}?accion=listar`;
        if (programaId) url += `&id_programa=${programaId}`;
        const r = await fetch(url, { 
          credentials: 'same-origin',
          headers: { 'Accept': 'application/json' }
        });
        if (!r.ok) throw new Error(`HTTP ${r.status}`);
        const data = await r.json();
        console.log('Competencias recibidas:', data);
        return data;
      } catch (error) {
        console.error('Error cargando competencias:', error);
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

    async function apiCambiarEstado(id_rae, estado) {
      const payload = {
        id_rae: id_rae,
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
    // UI HELPERS
    // ===============================
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

    function openModal(isCreate = true, data = null) {
      editingId = isCreate ? null : (data?.id_rae ?? null);
      
      // Cargar competencias en el select
      if (inpCompetency) {
        inpCompetency.innerHTML = '<option value="">Seleccione una competencia</option>';
        competencies.forEach(c => {
          const selected = (!isCreate && data?.id_competencia == c.id_competencia) ? 'selected' : '';
          inpCompetency.innerHTML += `<option value="${c.id_competencia}" ${selected}>${escapeHtml(c.nombre_competencia || c.nombre || 'Sin nombre')}</option>`;
        });
      }

      if (inpCode) inpCode.value = isCreate ? '' : (data?.id_rae ?? data?.codigo ?? '');
      if (inpDesc) inpDesc.value = isCreate ? '' : (data?.descripcion ?? '');

      modal?.classList.remove('hidden');
      backdrop?.classList.remove('hidden');
    }

    function closeModal() {
      modal?.classList.add('hidden');
      backdrop?.classList.add('hidden');
      if (form) form.reset();
      editingId = null;
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

    function createCard(r) {
      const activo = r.estado == 1 || r.estado === true || String(r.estado) === '1';
      const competenciaNombre = getCompetenciaNombre(r.id_competencia);

      const card = document.createElement('div');
      card.className = 'bg-white border border-zinc-200 rounded-2xl p-5 shadow-sm hover:shadow transition';
      card.setAttribute('data-id', r.id_rae);

      card.innerHTML = `
        <div class="flex items-start justify-between gap-4">
          <div class="flex-1">
            <div class="flex items-center gap-3 flex-wrap mb-2">
              <h3 class="text-lg font-semibold">${escapeHtml(r.descripcion || '')}</h3>
              <span class="text-sm text-zinc-500">Código: ${escapeHtml(r.id_rae || r.codigo || '')}</span>
            </div>
            <div class="flex items-center gap-2 text-sm">
              <span class="px-3 py-1 bg-zinc-100 rounded-full">${escapeHtml(competenciaNombre)}</span>
              <span class="px-3 py-1 bg-zinc-100 rounded-full">${escapeHtml(r.nombre_programa || 'Sin programa')}</span>
            </div>
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
        openModal(false, r);
      });

      const sw = card.querySelector('input[type="checkbox"]');
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
            t.ok(nuevoEstado ? 'RAE activado' : 'RAE inhabilitado');
            r.estado = nuevoEstado;
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
      console.log('Renderizando lista de RAEs:', list.length);
      
      if (!raesList) {
        console.error('raesList no encontrado');
        return;
      }

      raesList.innerHTML = '';

      if (!Array.isArray(list) || list.length === 0) {
        if (raesEmpty) {
          raesEmpty.classList.remove('hidden');
          raesEmpty.innerHTML = `
            <div class="py-12 text-center text-zinc-500">
              No hay RAE que coincidan con los filtros seleccionados.
            </div>
          `;
        }
        return;
      }

      if (raesEmpty) {
        raesEmpty.classList.add('hidden');
      }
      
      const frag = document.createDocumentFragment();
      list.forEach(r => {
        try {
          frag.appendChild(createCard(r));
        } catch (error) {
          console.error('Error creando tarjeta para RAE:', r, error);
        }
      });
      raesList.appendChild(frag);
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

        console.log('Programas cargados:', programs.length);
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

        // También actualizar el select del modal
        if (inpCompetency) {
          inpCompetency.innerHTML = '<option value="">Seleccione una competencia</option>';
          competencies.forEach(c => {
            inpCompetency.innerHTML += `<option value="${c.id_competencia}">${escapeHtml(c.nombre_competencia || c.nombre || 'Sin nombre')}</option>`;
          });
        }

        console.log('Competencias cargadas:', competencies.length);
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
        console.log('Datos recibidos en loadRaes:', data);
        
        allRaes = Array.isArray(data) ? data : [];
        console.log('RAEs procesados:', allRaes.length);
        
        const filtrados = filtrarRaes();
        renderList(filtrados);
        
      } catch (error) {
        console.error('Error en loadRaes:', error);
        if (raesEmpty) {
          raesEmpty.classList.remove('hidden');
          raesEmpty.innerHTML = '<div class="py-12 text-center text-red-600">Error al cargar RAEs. Verifique la conexión.</div>';
        }
        if (raesList) {
          raesList.innerHTML = '';
        }
      }
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

    if (raeProgramFilter) {
      raeProgramFilter.addEventListener('change', async () => {
        const programaId = raeProgramFilter.value !== 'all' ? raeProgramFilter.value : null;
        await loadCompetencias(programaId);
        await loadRaes();
      });
    }

    if (raeCompetencyFilter) {
      raeCompetencyFilter.addEventListener('change', () => {
        loadRaes();
      });
    }

    if (form) {
      form.addEventListener('submit', async e => {
        e.preventDefault();

        const id_competencia = inpCompetency?.value;
        const id_rae = (inpCode?.value || '').trim();
        const descripcion = (inpDesc?.value || '').trim();

        if (!id_competencia) return t.warn('Seleccione una competencia');
        if (!id_rae) return t.warn('El código es obligatorio');
        if (!descripcion) return t.warn('La descripción es obligatoria');

        const payload = {
          id_rae: id_rae,
          id_competencia: id_competencia,
          descripcion: descripcion
        };

        console.log('Enviando payload:', payload);

        try {
          let res;
          if (editingId) {
            payload.id_rae = editingId;
            payload.nuevo_id_rae = id_rae;
            res = await apiActualizar(payload);
          } else {
            res = await apiCrear(payload);
          }
          
          console.log('Respuesta del servidor:', res);
          
          if (res?.error) {
            return t.err(res.error);
          }

          closeModal();
          t.ok(editingId ? 'RAE actualizado' : 'RAE creado');
          await loadRaes();
          
        } catch (error) {
          console.error('Error guardando RAE:', error);
          t.err('Error al guardar');
        }
      });
    }

    // Cerrar modal al hacer clic en el backdrop
    if (backdrop) {
      backdrop.addEventListener('click', closeModal);
    }

    // ===============================
    // PAGINACIÓN
    // ===============================
    const raePrev = document.getElementById('raePrev');
    const raeNext = document.getElementById('raeNext');
    const raeInfo = document.getElementById('raeInfo');
    
    let currentPage = 1;
    const itemsPerPage = 10;

    function updatePagination() {
      if (!raeInfo) return;
      const totalPages = Math.ceil(filteredRaes.length / itemsPerPage) || 1;
      raeInfo.textContent = `Página ${currentPage} de ${totalPages}`;
      
      if (raePrev) raePrev.disabled = currentPage <= 1;
      if (raeNext) raeNext.disabled = currentPage >= totalPages;
    }

    if (raePrev) {
      raePrev.addEventListener('click', () => {
        if (currentPage > 1) {
          currentPage--;
          const start = (currentPage - 1) * itemsPerPage;
          const end = start + itemsPerPage;
          renderList(filteredRaes.slice(start, end));
          updatePagination();
        }
      });
    }

    if (raeNext) {
      raeNext.addEventListener('click', () => {
        const totalPages = Math.ceil(filteredRaes.length / itemsPerPage) || 1;
        if (currentPage < totalPages) {
          currentPage++;
          const start = (currentPage - 1) * itemsPerPage;
          const end = start + itemsPerPage;
          renderList(filteredRaes.slice(start, end));
          updatePagination();
        }
      });
    }

    // ===============================
    // INIT
    // ===============================
    console.log('Inicializando gestión de RAEs...');
    
    Promise.all([loadPrograms(), loadCompetencias(), loadRaes()])
      .then(() => {
        console.log('Datos cargados correctamente');
      })
      .catch(error => {
        console.error('Error en carga inicial:', error);
      });

    window.addEventListener('excel-subido-ok', () => {
      console.log('Excel subido, recargando RAEs...');
      loadRaes();
    });

    window.addEventListener('programs:changed', () => {
      console.log('Programas cambiados, recargando...');
      loadPrograms();
      loadCompetencias();
    });
  })();
});