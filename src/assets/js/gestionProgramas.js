// src/assets/js/gestionProgramas.js
// Notas: comentarios ligeros y prácticos, sin reescribir ni tocar el código base.
document.addEventListener('DOMContentLoaded', () => {
  (function () {
    // ===============================
    // CONFIG
    // ===============================
    // Endpoints: usa variables globales si existen; si no, cae al controlador por defecto
    const API = (window.API_PROGRAMAS || (window.BASE_URL || '') + 'src/controllers/ProgramasController.php').replace(/\/+$/, '');

    // ===============================
    // SELECTORES
    // ===============================
    // Corta ejecución si esta pestaña no está presente (evita errores en otras vistas)
    const tabPrograms = document.querySelector('[data-tab="programs"]');
    if (!tabPrograms) return;

    // Elementos principales de la vista
    const grid      = document.getElementById('programsGrid');
    const emptyBox  = document.getElementById('programsEmpty');
    const modal     = document.getElementById('modalProgram');
    const backdrop  = document.getElementById('modalProgramBackdrop');

    // Campos del modal (con chequeo defensivo cuando el modal no existe)
    const form      = modal ? modal.querySelector('#formProgramNew') : null;
    const inpCode   = modal ? modal.querySelector('#pg_code')       : null;
    const inpName   = modal ? modal.querySelector('#pg_name')       : null;
    const inpDesc   = modal ? modal.querySelector('#pg_desc')       : null;
    const inpHours  = modal ? modal.querySelector('#pg_hours')      : null;
    const btnClose  = modal ? modal.querySelector('#btnCloseProgram')  : null;
    const btnCancel = modal ? modal.querySelector('#btnCancelProgram') : null;

    // Botón flotante / CTA para abrir modal de creación
    const btnNew = document.getElementById('btnNewProgram');
    // Título del modal: soporta varias variantes de selector
    const modalTitle = modal ? (modal.querySelector('#modalProgramTitle') ||
                                modal.querySelector('[data-modal-title]') ||
                                modal.querySelector('.modal-title')) : null;

    let editingId = null; // id del programa que se está editando (null si es creación)

    // ===============================
    // SWEETALERT TOASTS
    // ===============================
    // Configuración del toast: aparece arriba a la derecha y se pausa al pasar el mouse
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
    // Atajos para disparar mensajes con íconos consistentes
    const t = {
      ok:   m => Toast.fire({ icon: 'success', title: m || 'Operación exitosa' }),
      warn: m => Toast.fire({ icon: 'warning', title: m || 'Revisa los campos' }),
      err:  m => Toast.fire({ icon: 'error',   title: m || 'Error en la operación' }),
      info: m => Toast.fire({ icon: 'info',    title: m || 'Información' })
    };

    // ===============================
    // 🔔 NOTIFICADOR (NUEVO)
    // ===============================
    // Emite un CustomEvent para que otras pestañas (Competencias/RAE) se enteren de cambios
    function notifyProgramsChanged(detail){
      try {
        window.dispatchEvent(new CustomEvent('programs:changed', { detail }));
      } catch (_) {}
    }

    // ===============================
    // API HELPERS
    // ===============================
    // Lectura simple: devuelve JSON o lanza por estado HTTP
    async function apiListar() {
      const r = await fetch(`${API}?accion=listar`, { credentials: 'same-origin' });
      if (!r.ok) throw new Error(`HTTP ${r.status}`);
      return r.json();
    }
    // Alta: envía payload en JSON (POST)
    async function apiAgregar(payload) {
      const r = await fetch(`${API}?accion=agregar`, {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin', body: JSON.stringify(payload),
      });
      return r.json();
    }
    // Actualización: también por JSON
    async function apiActualizar(payload) {
      const r = await fetch(`${API}?accion=actualizar`, {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin', body: JSON.stringify(payload),
      });
      return r.json();
    }
    // Activar/inhabilitar: aquí se usa FormData por compatibilidad con el backend
    async function apiActivar(id_programa) {
      const fd = new FormData(); fd.append('id_programa', id_programa);
      const r = await fetch(`${API}?accion=activar`, { method: 'POST', body: fd });
      return r.json();
    }
    async function apiInhabilitar(id_programa) {
      const fd = new FormData(); fd.append('id_programa', id_programa);
      const r = await fetch(`${API}?accion=inhabilitar`, { method: 'POST', body: fd });
      return r.json();
    }

    // ===============================
    // UI HELPERS
    // ===============================
    // Abre modal en modo crear/editar. También pre-carga datos y guarda "originales" para comparar cambios
    function openModal(isCreate = true, data = null) {
      // Guarda si estamos creando o editando
      editingId = isCreate ? null : (data?.id_programa ?? null);

      // Carga los datos en los campos
      if (inpCode)  inpCode.value  = isCreate ? '' : (data?.id_programa ?? '');
      if (inpName)  inpName.value  = isCreate ? '' : (data?.nombre_programa ?? '');
      if (inpDesc)  inpDesc.value  = isCreate ? '' : (data?.descripcion ?? '');
      if (inpHours) inpHours.value = isCreate ? '' : (data?.duracion ?? '');

      // el código también puede editarse
      if (inpCode) inpCode.disabled = false;

      // Cambia el título del modal según acción
      if (modalTitle) modalTitle.textContent = isCreate ? 'Nuevo Programa' : 'Editar Programa';

      // Si estamos editando, guardamos los valores originales en atributos data-* del formulario
      if (!isCreate && form) {
        form.dataset.originalId    = data?.id_programa ?? '';
        form.dataset.originalName  = data?.nombre_programa ?? '';
        form.dataset.originalDesc  = data?.descripcion ?? '';
        form.dataset.originalHours = data?.duracion ?? '';
      } else if (form) {
        // Si es un modal nuevo, limpiamos los data-* anteriores
        delete form.dataset.originalId;
        delete form.dataset.originalName;
        delete form.dataset.originalDesc;
        delete form.dataset.originalHours;
      }

      // Muestra el modal
      modal?.classList.remove('hidden');
      backdrop?.classList.remove('hidden');

      // Animación modal (fade + scale)
      modal.classList.add('animate-modal');
      backdrop.classList.add('animate-backdrop');
      setTimeout(() => {
        modal.classList.remove('animate-modal');
        backdrop.classList.remove('animate-backdrop');
      }, 300);
    }

    // Cierra y limpia el modal (incluye reset de estados visuales)
    function closeModal() {
      modal?.classList.add('hidden');
      backdrop?.classList.add('hidden');
      form?.reset();
      editingId = null;
      if (inpCode) inpCode.disabled = false;
      if (modalTitle) modalTitle.textContent = 'Nuevo Programa';
    }

    // Escapa HTML para evitar que la descripción/nombre rompan el DOM si traen caracteres raros
    function escapeHtml(s) {
      const t = document.createElement('textarea');
      t.textContent = String(s ?? '');
      return t.innerHTML;
    }

    // Presenta la duración con sufijo "horas" si es numérica
    function formatHours(h) {
      const n = Number(h);
      return Number.isFinite(n) ? `${n} horas` : `${h}`;
    }

    // ===============================
    // SWITCH ESTILO VERDE #39A900
    // ===============================
    // Render del toggle accesible; se actualiza con eventos más abajo
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
    // CARD
    // ===============================
    // Crea la tarjeta de un programa con acciones de editar y activar/inhabilitar
    function createCard(p) {
      const activo = String(p.estado) === '1' || String(p.estado).toLowerCase() === 'true';

      const card = document.createElement('div');
      card.className = 'rounded-2xl ring-1 ring-zinc-200 shadow-sm bg-white overflow-hidden hover:shadow-md transition p-6 space-y-4';

      card.innerHTML = `
        <div class="flex items-start justify-between gap-4">
          <div class="flex-1 space-y-1.5">
            <h3 class="text-lg font-semibold leading-snug">${escapeHtml(p.nombre_programa || '')}</h3>
            <p class="text-sm text-zinc-500">Código: <span class="font-medium">${escapeHtml(p.id_programa || '')}</span></p>
          </div>
          <div class="flex items-center gap-2">
            <button class="p-2 rounded-lg hover:bg-zinc-100" title="Editar" data-edit="${escapeHtml(p.id_programa)}">
              <img src="src/assets/img/pencil-line.svg" alt="Editar" class="w-4 h-4">
            </button>
            ${renderSwitch(activo)}
          </div>
        </div>

        <div class="space-y-2 mt-3 text-sm text-zinc-600">
          <p>${escapeHtml(p.descripcion || 'Sin descripción')}</p>
          <p><span class="font-medium">Duración:</span> ${escapeHtml(formatHours(p.duracion || 0))}</p>
          <div>
            ${activo
              ? '<span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium" style="background:#eaf7e6;border:1px solid rgba(57,169,0,.22);color:#39a900">Activo</span>'
              : '<span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium" style="background:#f3f4f6;border:1px solid #e5e7eb;color:#6b7280">Inactivo</span>'
            }

          </div>
        </div>
      `;

      // Toggle de estado con manejo de rollback si la API falla
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

            // 🔔 Notificar cambio de estado
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

      // Click en editar: reusa openModal en modo edición
      card.querySelector('[data-edit]')?.addEventListener('click', () => openModal(false, p));

      return card;
    }

    // ===============================
    // RENDER LISTA
    // ===============================
    // Pinta la grilla o muestra el estado vacío con CTA para crear
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
    // Trae los programas y maneja errores de red/servidor
    async function loadPrograms() {
      try {
        const data = await apiListar();
        if (Array.isArray(data)) renderList(data);
        else if (data?.error)
          emptyBox.innerHTML = `<div class="py-12 text-center text-red-600">${escapeHtml(data.error)}</div>`;
        else renderList([]);
      } catch {
        emptyBox.innerHTML = `<div class="py-12 text-center text-red-600">No se pudo cargar la lista de programas.</div>`;
      }
    }

    // ===============================
    // EVENTOS MODAL + FORM
    // ===============================
    // Apertura/cierre del modal y cancelación sin recargar
    btnNew?.addEventListener('click', () => openModal(true));
    btnClose?.addEventListener('click', closeModal);
    btnCancel?.addEventListener('click', e => { e.preventDefault(); closeModal(); });

    // Submit del formulario con validaciones separadas por modo (crear/editar)
    form?.addEventListener('submit', async e => {
      e.preventDefault();

      const id_programa     = (inpCode?.value || '').trim();
      const nombre_programa = (inpName?.value || '').trim();
      const descripcion     = (inpDesc?.value || '').trim();
      const duracion        = (inpHours?.value || '').trim();

      // Validaciones distintas según modo
      if (!editingId) {
        // ===== CREAR =====
        if (!id_programa && !nombre_programa && !descripcion && !duracion)
          return t.warn('Todos los campos son obligatorios');

        if (!id_programa)     return t.warn('El código es obligatorio');
        if (!nombre_programa) return t.warn('El nombre del programa es obligatorio');
        if (duracion !== '' && Number.isNaN(Number(duracion))) 
          return t.warn('La duración debe ser numérica');
      } else {
        // ===== EDITAR =====
        // Guardamos los valores originales en atributos de data-* al abrir el modal
        const original = {
          id_programa:     form.dataset.originalId || '',
          nombre_programa: form.dataset.originalName || '',
          descripcion:     form.dataset.originalDesc || '',
          duracion:        form.dataset.originalHours || ''
        };

        //  ahora también se compara el código (id_programa)
        const sinCambios = 
          original.id_programa === id_programa &&
          original.nombre_programa === nombre_programa &&
          original.descripcion === descripcion &&
          String(original.duracion) === String(duracion);

        if (sinCambios) return t.warn('No has editado nada aún');
      }

      // Construcción de payload
      let payload;
      if (editingId) {
        // En edición: enviar id original + posible nuevo id (PK)
        const originalId = form.dataset.originalId || '';
        payload = {
          id_programa: originalId,          // id actual en BD
          nuevo_id_programa: id_programa,   // posible código nuevo desde el input
          nombre_programa,
          descripcion,
          duracion
        };
      } else {
        // En creación: id del input es el id a crear
        payload = { id_programa, nombre_programa, descripcion, duracion };
      }

      try {
        const res = editingId ? await apiActualizar(payload) : await apiAgregar(payload);
        if (res?.error) return t.err(res.error);

        closeModal();
        t.ok(editingId ? 'Programa actualizado correctamente' : 'Programa creado correctamente');

        //  Notificar creación/actualización (para Competencias)
        notifyProgramsChanged({
          type: editingId ? 'update' : 'create',
          program: { id_programa: payload.nuevo_id_programa || id_programa, nombre_programa, descripcion, duracion }
        });

        await loadPrograms();
      } catch {
        t.err('No se pudo guardar el programa.');
      }
    });

    // ===============================
    // INIT
    // ===============================
    // Carga inicial de datos
    loadPrograms();
  })();
});
