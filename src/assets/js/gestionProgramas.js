// src/assets/js/gestionProgramas.js
// Gestión exclusiva de Programas
document.addEventListener('DOMContentLoaded', () => {
  (function () {
    // ===============================
    // CONFIG
    // ===============================
    const API = (window.API_PROGRAMAS || (window.BASE_URL || '') + 'src/controllers/ProgramasController.php').replace(/\/+$/, '');

    // ===============================
    // SELECTORES
    // ===============================
    const tabPrograms = document.querySelector('[data-tab="programs"]');
    if (!tabPrograms) return;

    const grid = document.getElementById('programsGrid');
    const emptyBox = document.getElementById('programsEmpty');
    const modal = document.getElementById('modalProgram');
    const backdrop = document.getElementById('modalProgramBackdrop');

    const form = modal ? modal.querySelector('#formProgramNew') : null;
    const inpCode = modal ? modal.querySelector('#pg_code') : null;
    const inpName = modal ? modal.querySelector('#pg_name') : null;
    const inpDesc = modal ? modal.querySelector('#pg_desc') : null;
    const inpHours = modal ? modal.querySelector('#pg_hours') : null;
    const btnClose = modal ? modal.querySelector('#btnCloseProgram') : null;
    const btnCancel = modal ? modal.querySelector('#btnCancelProgram') : null;
    const btnNew = document.getElementById('btnNewProgram');
    const modalTitle = modal ? (modal.querySelector('#modalProgramTitle') || modal.querySelector('[data-modal-title]') || modal.querySelector('.modal-title')) : null;

    // Filtros de programas
    const programTypeFilter = document.getElementById('programTypeFilter');
    const programSearchInput = document.getElementById('programSearchInput');

    let editingId = null;
    let allPrograms = [];

    // ===============================
    // SWEETALERT TOASTS
    // ===============================
    const Toast = Swal.mixin({
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 2500,
      timerProgressBar: true,
      background: '#fff',
      color: '#333',
      didOpen: toast => {
        toast.addEventListener('mouseenter', Swal.stopTimer);
        toast.addEventListener('mouseleave', Swal.resumeTimer);
      }
    });

    const t = {
      ok: m => Toast.fire({ icon: 'success', title: m || 'Operación exitosa' }),
      warn: m => Toast.fire({ icon: 'warning', title: m || 'Revisa los campos' }),
      err: m => Toast.fire({ icon: 'error', title: m || 'Error en la operación' }),
      info: m => Toast.fire({ icon: 'info', title: m || 'Información' })
    };

    // ===============================
    // NOTIFICADOR
    // ===============================
    function notifyProgramsChanged(detail) {
      try {
        window.dispatchEvent(new CustomEvent('programs:changed', { detail }));
      } catch (_) { }
    }

    // ===============================
    // API HELPERS
    // ===============================
    async function apiListar() {
      const r = await fetch(`${API}?accion=listar`, { credentials: 'same-origin' });
      if (!r.ok) throw new Error(`HTTP ${r.status}`);
      return r.json();
    }

    async function apiAgregar(payload) {
      const r = await fetch(`${API}?accion=agregar`, {
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

    async function apiActivar(id_programa) {
      const fd = new FormData();
      fd.append('id_programa', id_programa);
      const r = await fetch(`${API}?accion=activar`, { method: 'POST', body: fd });
      return r.json();
    }

    async function apiInhabilitar(id_programa) {
      const fd = new FormData();
      fd.append('id_programa', id_programa);
      const r = await fetch(`${API}?accion=inhabilitar`, { method: 'POST', body: fd });
      return r.json();
    }

    // ===============================
    // UI HELPERS
    // ===============================
    function openModal(isCreate = true, data = null) {
      editingId = isCreate ? null : (data?.id_programa ?? null);

      if (inpCode) inpCode.value = isCreate ? '' : (data?.id_programa ?? '');
      if (inpName) inpName.value = isCreate ? '' : (data?.nombre_programa ?? '');
      if (inpDesc) inpDesc.value = isCreate ? '' : (data?.descripcion ?? '');
      if (inpHours) inpHours.value = isCreate ? '' : (data?.duracion ?? '');

      if (inpCode) inpCode.disabled = false;
      if (modalTitle) modalTitle.textContent = isCreate ? 'Nuevo Programa' : 'Editar Programa';

      if (!isCreate && form) {
        form.dataset.originalId = data?.id_programa ?? '';
        form.dataset.originalName = data?.nombre_programa ?? '';
        form.dataset.originalDesc = data?.descripcion ?? '';
        form.dataset.originalHours = data?.duracion ?? '';
      } else if (form) {
        delete form.dataset.originalId;
        delete form.dataset.originalName;
        delete form.dataset.originalDesc;
        delete form.dataset.originalHours;
      }

      modal?.classList.remove('hidden');
      backdrop?.classList.remove('hidden');

      modal.classList.add('animate-modal');
      backdrop.classList.add('animate-backdrop');
      setTimeout(() => {
        modal.classList.remove('animate-modal');
        backdrop.classList.remove('animate-backdrop');
      }, 300);
    }

    function closeModal() {
      modal?.classList.add('hidden');
      backdrop?.classList.add('hidden');
      form?.reset();
      editingId = null;
      if (inpCode) inpCode.disabled = false;
      if (modalTitle) modalTitle.textContent = 'Nuevo Programa';
    }

    function escapeHtml(s) {
      const t = document.createElement('textarea');
      t.textContent = String(s ?? '');
      return t.innerHTML;
    }

    function formatHours(h) {
      const n = Number(h);
      return Number.isFinite(n) ? `${n} horas` : `${h}`;
    }

    function renderSwitch(active) {
      return `
        <label class="switch relative inline-flex items-center cursor-pointer select-none" title="Cambiar estado" aria-label="Cambiar estado">
          <input type="checkbox" class="peer sr-only" ${active ? 'checked' : ''} />
          <span class="block w-11 h-6 rounded-full bg-zinc-300 peer-checked:bg-[#39A900] transition-colors duration-300 ease-out ring-1 ring-inset ring-zinc-300 peer-checked:ring-[#39A900]"></span>
          <span class="dot absolute top-0.5 left-0.5 w-5 h-5 rounded-full bg-white shadow-md transition-transform duration-300 ease-out peer-checked:translate-x-5"></span>
        </label>
      `;
    }

    // ===============================
    // FILTROS
    // ===============================
    function filtrarProgramas() {
      if (!allPrograms.length) return [];

      const tipo = programTypeFilter?.value || 'all';
      const busqueda = programSearchInput?.value.toLowerCase() || '';

      return allPrograms.filter(p => {
        // Filtrar por tipo
        if (tipo !== 'all') {
          if (tipo === 'tecnico' && !p.nombre_programa?.toLowerCase().includes('tecnico')) return false;
          if (tipo === 'tecnologo' && !p.nombre_programa?.toLowerCase().includes('tecnologo')) return false;
          if (tipo === 'especializacion' && !p.nombre_programa?.toLowerCase().includes('especializacion')) return false;
        }

        // Filtrar por búsqueda
        if (busqueda) {
          const nombre = (p.nombre_programa || '').toLowerCase();
          const codigo = (p.id_programa || '').toLowerCase();
          return nombre.includes(busqueda) || codigo.includes(busqueda);
        }

        return true;
      });
    }

    // ===============================
    // CARD
    // ===============================
    function createCard(p) {
      const activo = String(p.estado) === '1' || String(p.estado).toLowerCase() === 'true';

      const card = document.createElement('div');
      card.className = 'rounded-2xl ring-1 ring-zinc-200 shadow-sm bg-white overflow-hidden hover:shadow-md transition p-6 flex flex-col md:flex-row md:items-center justify-between gap-4';

      const infoDiv = document.createElement('div');
      infoDiv.className = 'flex-1 space-y-2';
      infoDiv.innerHTML = `
        <div class="flex items-center gap-3 flex-wrap">
            <h3 class="text-lg font-semibold leading-snug" style="word-break: break-word; overflow-wrap: anywhere;">
                ${escapeHtml(p.nombre_programa || '')}
            </h3>
            <span class="text-sm text-zinc-500">Código: <span class="font-medium">${escapeHtml(p.id_programa || '')}</span></span>
        </div>
        <p class="text-sm text-zinc-600">${escapeHtml(p.descripcion || 'Sin descripción')}</p>
        <div class="flex items-center gap-4 text-sm text-zinc-600 flex-wrap">
            <span><span class="font-medium">Duración:</span> ${escapeHtml(formatHours(p.duracion || 0))}</span>
            <span><span class="font-medium">Número de instructores:</span> 0</span>
            <span><span class="font-medium">Instructores asignados:</span> Sin instructores asignados</span>
        </div>
      `;

      const actionsDiv = document.createElement('div');
      actionsDiv.className = 'flex items-center gap-3 flex-shrink-0';

      const addInstructorBtn = document.createElement('button');
      addInstructorBtn.className = 'rounded-xl px-4 py-2 text-sm font-medium bg-[#0a3a57] text-white flex items-center gap-2 whitespace-nowrap';
      addInstructorBtn.innerHTML = `
        <img src="src/assets/img/plus.svg" class="w-4 h-4" alt="">
        Agregar instructor(es)
      `;

      const editBtn = document.createElement('button');
      editBtn.className = 'p-2 rounded-lg hover:bg-zinc-100';
      editBtn.title = 'Editar';
      editBtn.setAttribute('data-edit', escapeHtml(p.id_programa));
      editBtn.innerHTML = '<img src="src/assets/img/pencil-line.svg" alt="Editar" class="w-4 h-4">';
      editBtn.addEventListener('click', () => openModal(false, p));

      const switchContainer = document.createElement('div');
      switchContainer.innerHTML = renderSwitch(activo);

      actionsDiv.appendChild(addInstructorBtn);
      actionsDiv.appendChild(editBtn);
      actionsDiv.appendChild(switchContainer.firstChild);

      card.appendChild(infoDiv);
      card.appendChild(actionsDiv);

      const sw = card.querySelector('input[type="checkbox"]');
      sw?.addEventListener('change', async () => {
        const checked = sw.checked;
        try {
          const res = checked ? await apiActivar(p.id_programa) : await apiInhabilitar(p.id_programa);
          if (res?.error) {
            t.err(res.error);
            sw.checked = !checked;
          } else {
            t.ok(checked ? 'Programa activado' : 'Programa inhabilitado');
            notifyProgramsChanged({
              type: checked ? 'activate' : 'disable',
              program: { id_programa: p.id_programa }
            });
            await loadPrograms();
          }
        } catch {
          t.err('No se pudo cambiar el estado.');
          sw.checked = !checked;
        }
      });

      return card;
    }

    // ===============================
    // RENDER LISTA
    // ===============================
    function renderList(list) {
      grid.innerHTML = '';

      if (!Array.isArray(list) || list.length === 0) {
        emptyBox.classList.remove('hidden');
        emptyBox.innerHTML = `
          <div class="py-12 text-center flex flex-col items-center justify-center">
            <p class="text-zinc-500 mb-4">No hay programas registrados</p>
            <button class="rounded-xl px-4 py-2 text-sm font-medium bg-[#00324d] text-white flex items-center gap-2" data-empty-new>
              <img src="src/assets/img/plus.svg" class="w-4 h-4" alt="símbolo más" />
              Crear nuevo programa
            </button>
          </div>
        `;
        emptyBox.querySelector('[data-empty-new]')?.addEventListener('click', () => openModal(true));
        return;
      }

      emptyBox.classList.add('hidden');
      const frag = document.createDocumentFragment();
      list.forEach(p => frag.appendChild(createCard(p)));
      grid.appendChild(frag);
    }

    // ===============================
    // CARGA INICIAL
    // ===============================
    async function loadPrograms() {
      try {
        const data = await apiListar();
        allPrograms = Array.isArray(data) ? data : [];
        const filtrados = filtrarProgramas();
        renderList(filtrados);
      } catch (error) {
        console.error('Error cargando programas:', error);
        emptyBox.innerHTML = `<div class="py-12 text-center text-red-600">No se pudo cargar la lista de programas.</div>`;
      }
    }

    // ===============================
    // EVENTOS
    // ===============================
    btnNew?.addEventListener('click', () => openModal(true));
    btnClose?.addEventListener('click', closeModal);
    btnCancel?.addEventListener('click', e => { e.preventDefault(); closeModal(); });

    // Eventos de filtros
    programTypeFilter?.addEventListener('change', () => {
      const filtrados = filtrarProgramas();
      renderList(filtrados);
    });

    programSearchInput?.addEventListener('input', () => {
      const filtrados = filtrarProgramas();
      renderList(filtrados);
    });

    form?.addEventListener('submit', async e => {
      e.preventDefault();

      const id_programa = (inpCode?.value || '').trim();
      const nombre_programa = (inpName?.value || '').trim();
      const descripcion = (inpDesc?.value || '').trim();
      const duracion = (inpHours?.value || '').trim();

      if (!editingId) {
        if (!id_programa && !nombre_programa && !descripcion && !duracion)
          return t.warn('Todos los campos son obligatorios');

        if (!id_programa) return t.warn('El código es obligatorio');
        if (!nombre_programa) return t.warn('El nombre del programa es obligatorio');
        if (duracion !== '' && Number.isNaN(Number(duracion)))
          return t.warn('La duración debe ser numérica');
      } else {
        const original = {
          id_programa: form.dataset.originalId || '',
          nombre_programa: form.dataset.originalName || '',
          descripcion: form.dataset.originalDesc || '',
          duracion: form.dataset.originalHours || ''
        };

        const sinCambios =
          original.id_programa === id_programa &&
          original.nombre_programa === nombre_programa &&
          original.descripcion === descripcion &&
          String(original.duracion) === String(duracion);

        if (sinCambios) return t.warn('No has editado nada aún');
      }

      let payload;
      if (editingId) {
        const originalId = form.dataset.originalId || '';
        payload = {
          id_programa: originalId,
          nuevo_id_programa: id_programa,
          nombre_programa,
          descripcion,
          duracion
        };
      } else {
        payload = { id_programa, nombre_programa, descripcion, duracion };
      }

      try {
        const res = editingId ? await apiActualizar(payload) : await apiAgregar(payload);
        if (res?.error) return t.err(res.error);

        closeModal();
        t.ok(editingId ? 'Programa actualizado correctamente' : 'Programa creado correctamente');

        notifyProgramsChanged({
          type: editingId ? 'update' : 'create',
          program: { id_programa: payload.nuevo_id_programa || id_programa, nombre_programa, descripcion, duracion }
        });

        await loadPrograms();
      } catch (error) {
        console.error('Error guardando programa:', error);
        t.err('No se pudo guardar el programa.');
      }
    });

    // ===============================
    // PAGINACIÓN (Básica)
    // ===============================
    const pgPrev = document.getElementById('pgPrev');
    const pgNext = document.getElementById('pgNext');
    const pgInfo = document.querySelector('#programsPagination span');

    let currentPage = 1;
    const itemsPerPage = 5;

    function updatePagination() {
      if (!pgInfo) return;
      const totalPages = Math.ceil(allPrograms.length / itemsPerPage) || 1;
      pgInfo.textContent = `Página ${currentPage} de ${totalPages}`;
      
      if (pgPrev) pgPrev.disabled = currentPage <= 1;
      if (pgNext) pgNext.disabled = currentPage >= totalPages;
    }

    pgPrev?.addEventListener('click', () => {
      if (currentPage > 1) {
        currentPage--;
        loadPrograms();
      }
    });

    pgNext?.addEventListener('click', () => {
      const totalPages = Math.ceil(allPrograms.length / itemsPerPage) || 1;
      if (currentPage < totalPages) {
        currentPage++;
        loadPrograms();
      }
    });

    // ===============================
    // INIT
    // ===============================
    loadPrograms();

    window.addEventListener('excel-subido-ok', () => {
      loadPrograms();
    });
  })();
});