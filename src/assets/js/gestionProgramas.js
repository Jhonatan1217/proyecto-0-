document.addEventListener('DOMContentLoaded', function () {
  (function () {
    // ===============================
    // CONFIG / ENDPOINT
    // ===============================
    const API = (window.API_PROGRAMAS || (window.BASE_URL || '') + 'src/controllers/programasController.php').replace(/\/+$/, '');

    // ===============================
    // SELECTORES
    // ===============================
    const tabPrograms = document.querySelector('[data-tab="programs"]');
    if (!tabPrograms) return;

    const grid = document.getElementById('programsGrid');
    const emptyBox = document.getElementById('programsEmpty');
    const modal = document.getElementById('modalProgram');
    const modalBackdrop = document.getElementById('modalProgramBackdrop');

    const form     = modal ? modal.querySelector('#formProgramNew') : null;
    const inpCode  = modal ? modal.querySelector('#pg_code')       : null;
    const inpName  = modal ? modal.querySelector('#pg_name')       : null;
    const inpDesc  = modal ? modal.querySelector('#pg_desc')       : null;
    const inpHours = modal ? modal.querySelector('#pg_hours')      : null;
    const btnClose  = modal ? modal.querySelector('#btnCloseProgram')  : null;
    const btnCancel = modal ? modal.querySelector('#btnCancelProgram') : null;

    const btnNew = (() => {
      const candidates = tabPrograms.querySelectorAll('button');
      for (const b of candidates) {
        if ((b.textContent || '').trim().toLowerCase().includes('nuevo programa')) return b;
      }
      return null;
    })();

    // ===============================
    // ESTADO
    // ===============================
    let editingId = null;
    let isSubmitting = false; // evita doble envío

    // ===============================
    // API HELPERS
    // ===============================
    async function apiListar() {
      const r = await fetch(`${API}?accion=listar`, { credentials: 'same-origin' });
      if (!r.ok) throw new Error(`HTTP ${r.status}`);
      return r.json();
    }

    async function apiAgregar(payload) {
      const res = await fetch(`${API}?accion=agregar`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify(payload),
      });
      return res.json();
    }

    async function apiActualizar(payload) {
      const res = await fetch(`${API}?accion=actualizar`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify(payload),
      });
      return res.json();
    }

    async function apiEliminar(id_programa) {
      const fd = new FormData();
      fd.append('id_programa', id_programa);
      const res = await fetch(`${API}?accion=eliminar`, { method: 'POST', body: fd, credentials: 'same-origin' });
      return res.json();
    }

    async function apiActivar(id_programa) {
      const fd = new FormData();
      fd.append('id_programa', id_programa);
      const res = await fetch(`${API}?accion=activar`, { method: 'POST', body: fd, credentials: 'same-origin' });
      return res.json();
    }

    async function apiInhabilitar(id_programa) {
      const fd = new FormData();
      fd.append('id_programa', id_programa);
      const res = await fetch(`${API}?accion=inhabilitar`, { method: 'POST', body: fd, credentials: 'same-origin' });
      return res.json();
    }

    // ===============================
    // UI HELPERS
    // ===============================
    function openModal(createMode = true, data = null) {
      editingId = createMode ? null : (data?.id_programa ?? null);
      if (inpCode)  inpCode.value  = createMode ? '' : (data?.id_programa ?? '');
      if (inpName)  inpName.value  = createMode ? '' : (data?.nombre_programa ?? '');
      if (inpDesc)  inpDesc.value  = createMode ? '' : (data?.descripcion ?? '');
      if (inpHours) inpHours.value = createMode ? '' : (data?.duracion ?? '');
      if (inpCode) inpCode.disabled = !createMode;
      modal?.classList.remove('hidden');
      modalBackdrop?.classList.remove('hidden');
    }

    function closeModal() {
      modal?.classList.add('hidden');
      modalBackdrop?.classList.add('hidden');
      form?.reset();
      editingId = null;
      isSubmitting = false;
      if (inpCode) inpCode.disabled = false;
    }

    function escapeHtml(s) {
      const t = document.createElement('textarea');
      t.textContent = String(s ?? '');
      return t.innerHTML;
    }

    function renderSwitch(active) {
      return `
        <label class="switch flex items-center">
          <input type="checkbox" ${active ? 'checked' : ''} />
          <span class="track absolute inset-0 rounded-full"></span>
          <span class="dot"></span>
        </label>
      `;
    }

    function createCard(p) {
      const activo = String(p.estado) === '1' || String(p.estado).toLowerCase() === 'true';
      const card = document.createElement('div');
      card.className = 'rounded-2xl ring-1 ring-zinc-200 shadow-sm p-5 flex flex-col gap-4 bg-white';

      const header = document.createElement('div');
      header.className = 'flex items-start justify-between gap-3';
      header.innerHTML = `
        <div>
          <h3 class="text-lg font-semibold">${escapeHtml(p.nombre_programa || '')}</h3>
          <p class="text-sm text-zinc-500">Código: <span class="font-medium">${escapeHtml(p.id_programa || '')}</span></p>
        </div>
        <div class="flex items-center gap-3">
          ${renderSwitch(activo)}
        </div>
      `;
      card.appendChild(header);

      const desc = document.createElement('p');
      desc.className = 'text-sm text-zinc-600';
      desc.textContent = p.descripcion || 'Sin descripción';
      card.appendChild(desc);

      const dur = document.createElement('div');
      dur.className = 'text-sm text-zinc-500';
      dur.innerHTML = `<span class="text-zinc-700 font-medium">Duración:</span> ${p.duracion || 0} h`;
      card.appendChild(dur);

      const actions = document.createElement('div');
      actions.className = 'flex items-center gap-2 pt-1';
      const btnEdit = document.createElement('button');
      btnEdit.className = 'px-3 py-2 rounded-xl border border-zinc-300 bg-white text-sm hover:bg-zinc-50';
      btnEdit.textContent = 'Editar';
      const btnDelete = document.createElement('button');
      btnDelete.className = 'px-3 py-2 rounded-xl bg-[#00324d] text-white text-sm hover:bg-[#00263a]';
      btnDelete.textContent = 'Eliminar';
      actions.append(btnEdit, btnDelete);
      card.appendChild(actions);

      const sw = header.querySelector('label.switch input[type="checkbox"]');
      const track = header.querySelector('label.switch .track');
      if (sw && track) {
        track.style.background = activo ? '#39a900' : '#e5e7eb';
        sw.checked = !!activo;
        sw.addEventListener('change', async () => {
          track.style.background = sw.checked ? '#39a900' : '#e5e7eb';
          const res = sw.checked ? await apiActivar(p.id_programa) : await apiInhabilitar(p.id_programa);
          if (res?.error) {
            alert(res.error);
            sw.checked = !sw.checked;
            track.style.background = sw.checked ? '#39a900' : '#e5e7eb';
          }
        });
      }

      btnEdit.addEventListener('click', () => openModal(false, p));
      btnDelete.addEventListener('click', async () => {
        const ok = confirm(`¿Eliminar el programa "${p.nombre_programa}" (${p.id_programa})?`);
        if (!ok) return;
        const res = await apiEliminar(p.id_programa);
        if (res?.error) return alert(res.error);
        await loadPrograms();
      });

      return card;
    }

    function renderList(list) {
      grid.innerHTML = '';
      if (!Array.isArray(list) || list.length === 0) {
        emptyBox.classList.remove('hidden');
        emptyBox.innerHTML = `
          <div class="py-12 text-center">
            <div class="text-zinc-500">No hay programas registrados</div>
            <div class="mt-4">
              <button class="rounded-xl bg-zinc-900 text-white px-4 py-2 text-sm font-medium hover:bg-black" data-empty-new>Crear programa</button>
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
        if (Array.isArray(data)) renderList(data);
        else renderList([]);
      } catch {
        emptyBox.classList.remove('hidden');
        emptyBox.innerHTML = `<div class="py-12 text-center text-red-600">Error al cargar programas.</div>`;
      }
    }

    // ===============================
    // EVENTOS MODAL + FORM
    // ===============================
    btnNew?.addEventListener('click', () => openModal(true));
    btnClose?.addEventListener('click', closeModal);
    btnCancel?.addEventListener('click', e => { e.preventDefault(); closeModal(); });

    form?.addEventListener('submit', async e => {
      e.preventDefault();
      e.stopImmediatePropagation(); // 🔥 evita doble envío de submit por eventos duplicados
    
      if (isSubmitting) return;
      isSubmitting = true;
    
      const id_programa     = (inpCode?.value  || '').trim();
      const nombre_programa = (inpName?.value  || '').trim();
      const descripcion     = (inpDesc?.value  || '').trim();
      const duracion        = (inpHours?.value || '').trim();
    
      if (!id_programa) {
        alert('El código es obligatorio.');
        isSubmitting = false;
        return;
      }
      if (!nombre_programa) {
        alert('El nombre es obligatorio.');
        isSubmitting = false;
        return;
      }
      if (duracion !== '' && Number.isNaN(Number(duracion))) {
        alert('Duración debe ser numérica.');
        isSubmitting = false;
        return;
      }
    
      const payload = { id_programa, nombre_programa, descripcion, duracion };
    
      try {
        const res = editingId ? await apiActualizar(payload) : await apiAgregar(payload);
        if (res?.error) alert(res.error);
        else alert(res.success || 'Programa guardado correctamente.');
        closeModal();
        await loadPrograms();
      } catch {
        alert('Error al guardar el programa.');
      } finally {
        isSubmitting = false;
      }
    });    

    // ===============================
    // INIT
    // ===============================
    loadPrograms();
  })();
});
