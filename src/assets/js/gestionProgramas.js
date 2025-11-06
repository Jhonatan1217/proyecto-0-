// src/assets/js/gestionProgramas.js
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

    const grid      = document.getElementById('programsGrid');
    const emptyBox  = document.getElementById('programsEmpty');
    const modal     = document.getElementById('modalProgram');
    const backdrop  = document.getElementById('modalProgramBackdrop');

    const form      = modal ? modal.querySelector('#formProgramNew') : null;
    const inpCode   = modal ? modal.querySelector('#pg_code')       : null; // id_programa
    const inpName   = modal ? modal.querySelector('#pg_name')       : null; // nombre_programa
    const inpDesc   = modal ? modal.querySelector('#pg_desc')       : null; // descripcion
    const inpHours  = modal ? modal.querySelector('#pg_hours')      : null; // duracion
    const btnClose  = modal ? modal.querySelector('#btnCloseProgram')  : null;
    const btnCancel = modal ? modal.querySelector('#btnCancelProgram') : null;

    const btnNew = document.getElementById('btnNewProgram');

    let editingId = null;

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
    async function apiEliminar(id_programa) {
      const fd = new FormData(); fd.append('id_programa', id_programa);
      const r = await fetch(`${API}?accion=eliminar`, { method: 'POST', body: fd, credentials: 'same-origin' });
      return r.json();
    }
    async function apiActivar(id_programa) {
      const fd = new FormData(); fd.append('id_programa', id_programa);
      const r = await fetch(`${API}?accion=activar`, { method: 'POST', body: fd, credentials: 'same-origin' });
      return r.json();
    }
    async function apiInhabilitar(id_programa) {
      const fd = new FormData(); fd.append('id_programa', id_programa);
      const r = await fetch(`${API}?accion=inhabilitar`, { method: 'POST', body: fd, credentials: 'same-origin' });
      return r.json();
    }

    // ===============================
    // UI HELPERS
    // ===============================
    function openModal(isCreate = true, data = null) {
      editingId = isCreate ? null : (data?.id_programa ?? null);

      if (inpCode)  { inpCode.value  = isCreate ? '' : (data?.id_programa     ?? ''); }
      if (inpName)  { inpName.value  = isCreate ? '' : (data?.nombre_programa ?? ''); }
      if (inpDesc)  { inpDesc.value  = isCreate ? '' : (data?.descripcion     ?? ''); }
      if (inpHours) { inpHours.value = isCreate ? '' : (data?.duracion        ?? ''); }

      if (inpCode) inpCode.disabled = !isCreate;

      modal?.classList.remove('hidden');
      backdrop?.classList.remove('hidden');
      window.lucide?.createIcons();
    }

    function closeModal() {
      modal?.classList.add('hidden');
      backdrop?.classList.add('hidden');
      form?.reset();
      editingId = null;
      if (inpCode) inpCode.disabled = false;
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
      // Track negro cuando activo (como en la imagen), gris cuando no
      return `
        <label class="switch relative inline-flex items-center" title="Cambiar estado">
          <input type="checkbox" ${active ? 'checked' : ''}/>
          <span class="track absolute inset-0 rounded-full"></span>
          <span class="dot"></span>
        </label>
      `;
    }

    // ===============================
    // CARD (MATCH UI DE LA CAPTURA)
    // ===============================
    function createCard(p) {
      const activo = String(p.estado) === '1' || String(p.estado).toLowerCase() === 'true';

      const card = document.createElement('div');
      card.className = [
        'rounded-2xl', 'ring-1', 'ring-zinc-200',
        'shadow-sm', 'bg-white', 'overflow-hidden',
        'hover:shadow-md', 'transition'
      ].join(' ');

      // Header
      const header = document.createElement('div');
      header.className = 'px-6 pt-6 pb-2';
      header.innerHTML = `
        <div class="flex items-start justify-between gap-4">
          <div class="flex-1">
            <h3 class="text-lg font-semibold leading-snug">${escapeHtml(p.nombre_programa || '')}</h3>
            <p class="mt-1 text-sm text-zinc-500">Código: <span class="font-medium">${escapeHtml(p.id_programa || '')}</span></p>
          </div>
          <div class="flex items-center gap-2">
            <button class="p-2 rounded-lg hover:bg-zinc-100" title="Editar" data-edit="${escapeHtml(p.id_programa)}">
              <img src="src/assets/img/pencil-line.svg" alt="Editar" class="w-4 h-4">
            </button>
            ${renderSwitch(activo)}
          </div>
        </div>
      `;
      card.appendChild(header);

      // Body
      const body = document.createElement('div');
      body.className = 'px-6 pb-6';
      body.innerHTML = `
        <p class="text-sm text-zinc-600">${escapeHtml(p.descripcion || 'Sin descripción')}</p>
        <p class="mt-2 text-sm"><span class="font-medium">Duración:</span> ${escapeHtml(formatHours(p.duracion || 0))}</p>
        <div class="mt-2">
          ${activo
            ? '<span class="inline-flex items-center rounded-full bg-green-100 text-green-700 px-2 py-0.5 text-xs font-medium">Activo</span>'
            : '<span class="inline-flex items-center rounded-full bg-zinc-100 text-zinc-500 px-2 py-0.5 text-xs font-medium">Inactivo</span>'
          }
        </div>
      `;
      card.appendChild(body);

      // Handlers: switch
      const sw     = header.querySelector('label.switch input[type="checkbox"]');
      const track  = header.querySelector('label.switch .track');
      if (track) track.style.background = activo ? '#0a0a0a' : '#e5e7eb'; // negro como en mock

      sw?.addEventListener('change', async () => {
        // feedback visual inmediato
        track.style.background = sw.checked ? '#0a0a0a' : '#e5e7eb';
        try {
          const res = sw.checked ? await apiActivar(p.id_programa) : await apiInhabilitar(p.id_programa);
          if (res?.error) {
            alert(res.error);
            // revertir
            sw.checked = !sw.checked;
            track.style.background = sw.checked ? '#0a0a0a' : '#e5e7eb';
          } else {
            // refrescar lista para que badge cambie
            await loadPrograms();
          }
        } catch {
          alert('No se pudo cambiar el estado.');
          sw.checked = !sw.checked;
          track.style.background = sw.checked ? '#0a0a0a' : '#e5e7eb';
        }
      });

      // Handler: editar
      const btnEdit = header.querySelector('[data-edit]');
      btnEdit?.addEventListener('click', () => openModal(false, p));

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
          <div class="py-12 text-center">
            <div class="text-zinc-500">No hay programas registrados</div>
            <div class="mt-4">
              <button class="rounded-xl px-4 py-2 text-sm font-medium" style="background:#0a0a0a;color:#fff" data-empty-new>
                Crear programa
              </button>
            </div>
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
        if (Array.isArray(data)) {
          renderList(data);
        } else if (data?.error) {
          emptyBox.classList.remove('hidden');
          emptyBox.innerHTML = `<div class="py-12 text-center text-red-600">${escapeHtml(data.error)}</div>`;
        } else {
          renderList([]);
        }
        window.lucide?.createIcons();
      } catch {
        emptyBox.classList.remove('hidden');
        emptyBox.innerHTML = `<div class="py-12 text-center text-red-600">No se pudo cargar la lista de programas.</div>`;
      }
    }

    // ===============================
    // EVENTOS MODAL + FORM
    // ===============================
    btnNew?.addEventListener('click', () => openModal(true));
    btnClose?.addEventListener('click', closeModal);
    btnCancel?.addEventListener('click', (e) => { e.preventDefault(); closeModal(); });

    form?.addEventListener('submit', async (e) => {
      e.preventDefault();

      const id_programa     = (inpCode?.value  || '').trim();
      const nombre_programa = (inpName?.value  || '').trim();
      const descripcion     = (inpDesc?.value  || '').trim();
      const duracion        = (inpHours?.value || '').trim();

      if (!id_programa)     return alert('El código (id_programa) es obligatorio.');
      if (!nombre_programa) return alert('El nombre del programa es obligatorio.');
      if (duracion !== '' && Number.isNaN(Number(duracion))) return alert('Duración debe ser numérica.');

      const payload = { id_programa, nombre_programa, descripcion, duracion };

      try {
        const res = editingId ? await apiActualizar(payload) : await apiAgregar(payload);
        if (res?.error) return alert(res.error);
        closeModal();
        await loadPrograms();
      } catch {
        alert('No se pudo guardar el programa.');
      }
    });

    // ===============================
    // INIT
    // ===============================
    loadPrograms();
  })();
});
