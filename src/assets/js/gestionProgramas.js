// src/assets/js/gestionProgramas.js
document.addEventListener('DOMContentLoaded', function () {
  (function () {
    // ===============================
    // CONFIG / ENDPOINT
    // ===============================
    const API = (window.API_PROGRAMAS || (window.BASE_URL || '') + 'src/controllers/programasController.php').replace(/\/+$/, '');

    // ===============================
    // SELECTORES (solo en TAB Programas)
    // ===============================
    const tabPrograms = document.querySelector('[data-tab="programs"]');
    if (!tabPrograms) return; // Si la pestaña no existe, salimos silenciosamente

    const grid = document.getElementById('programsGrid');
    const emptyBox = document.getElementById('programsEmpty');

    const modal = document.getElementById('modalProgram');
    const modalBackdrop = document.getElementById('modalProgramBackdrop');

    // 🔒 Selectores *dentro* del modal para evitar capturar copias duplicadas
    const form     = modal ? modal.querySelector('#formProgramNew') : null;
    const inpCode  = modal ? modal.querySelector('#pg_code')       : null; // id_programa (código)
    const inpName  = modal ? modal.querySelector('#pg_name')       : null; // nombre_programa
    const inpDesc  = modal ? modal.querySelector('#pg_desc')       : null; // descripcion
    const inpHours = modal ? modal.querySelector('#pg_hours')      : null; // duracion
    const btnClose  = modal ? modal.querySelector('#btnCloseProgram')  : null;
    const btnCancel = modal ? modal.querySelector('#btnCancelProgram') : null;

    // Botón "Nuevo Programa" (lo buscamos por texto)
    const btnNew = (() => {
      const candidates = tabPrograms.querySelectorAll('button');
      for (const b of candidates) {
        if ((b.textContent || '').trim().toLowerCase().includes('nuevo programa')) return b;
      }
      return null;
    })();

    // Estado local
    let editingId = null; // null => creando; string/number => editando

    // ===============================
    // HELPERS API
    // ===============================
    async function apiListar() {
  const r = await fetch(`${API}?accion=listar`, { credentials: 'same-origin' });
  if (!r.ok) throw new Error(`HTTP ${r.status} en listar`);
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
      const url = `${API}?accion=eliminar`;
      const fd = new FormData();
      fd.append('id_programa', id_programa);
      const res = await fetch(url, { method: 'POST', body: fd, credentials: 'same-origin' });
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
    // UI HELPERS (modal y estados)
    // ===============================
    function openModal(createMode = true, data = null) {
      editingId = createMode ? null : (data?.id_programa ?? null);

      // Llenar / limpiar
      if (inpCode)  { inpCode.value  = createMode ? '' : (data?.id_programa     ?? ''); }
      if (inpName)  { inpName.value  = createMode ? '' : (data?.nombre_programa ?? ''); }
      if (inpDesc)  { inpDesc.value  = createMode ? '' : (data?.descripcion     ?? ''); }
      if (inpHours) { inpHours.value = createMode ? '' : (data?.duracion        ?? ''); }

      // Si estamos editando, deshabilitar el id (pk)
      if (inpCode) inpCode.disabled = !createMode;

      modal?.classList.remove('hidden');
      modalBackdrop?.classList.remove('hidden');
    }

    function closeModal() {
      modal?.classList.add('hidden');
      modalBackdrop?.classList.add('hidden');
      form?.reset();
      editingId = null;
      if (inpCode) inpCode.disabled = false;
    }

    // ===============================
    // RENDER
    // ===============================
    function formatHours(h) {
      const n = Number(h);
      return Number.isFinite(n) ? `${n} h` : `${h}`;
    }

    function createCard(p) {
      const activo = String(p.estado) === '1' || String(p.estado).toLowerCase() === 'true';

      // Card wrapper
      const card = document.createElement('div');
      card.className = 'rounded-2xl ring-1 ring-zinc-200 shadow-sm p-5 flex flex-col gap-4 bg-white';

      // Header: Nombre + código
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

      // Descripción
      const desc = document.createElement('p');
      desc.className = 'text-sm text-zinc-600';
      desc.textContent = p.descripcion || 'Sin descripción';
      card.appendChild(desc);

      // Duración
      const dur = document.createElement('div');
      dur.className = 'text-sm text-zinc-500';
      dur.innerHTML = `<span class="text-zinc-700 font-medium">Duración:</span> ${formatHours(p.duracion || 0)}`;
      card.appendChild(dur);

      // Acciones
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

      // === Handlers ===
      // Switch estado
      const sw = header.querySelector('label.switch input[type="checkbox"]');
      const track = header.querySelector('label.switch .track');

      if (sw && track) {
        // set initial track color
        track.style.background = activo ? '#39a900' : '#e5e7eb';
        sw.checked = !!activo;

        sw.addEventListener('change', async () => {
          // animación suave (tu CSS ya tiene transition)
          track.style.background = sw.checked ? '#39a900' : '#e5e7eb';
          try {
            const res = sw.checked ? await apiActivar(p.id_programa) : await apiInhabilitar(p.id_programa);
            if (res?.error) {
              alert(res.error);
              // revertir si hay error
              sw.checked = !sw.checked;
              track.style.background = sw.checked ? '#39a900' : '#e5e7eb';
            }
          } catch (e) {
            alert('Error al cambiar estado.');
            sw.checked = !sw.checked;
            track.style.background = sw.checked ? '#39a900' : '#e5e7eb';
          }
        });
      }

      // Edit
      btnEdit.addEventListener('click', () => openModal(false, p));

      // Delete
      btnDelete.addEventListener('click', async () => {
        const ok = confirm(`¿Eliminar el programa "${p.nombre_programa}" (${p.id_programa})?`);
        if (!ok) return;
        try {
          const res = await apiEliminar(p.id_programa);
          if (res?.error) return alert(res.error);
          await loadPrograms();
        } catch (e) {
          alert('No se pudo eliminar.');
        }
      });

      return card;
    }

    function renderSwitch(active) {
      // Usa tu CSS existente: .switch, .dot, .track, transitions
      return `
        <label class="switch flex items-center">
          <input type="checkbox" ${active ? 'checked' : ''} />
          <span class="track absolute inset-0 rounded-full"></span>
          <span class="dot"></span>
        </label>
      `;
    }

    function escapeHtml(s) {
      const t = document.createElement('textarea');
      t.textContent = String(s ?? '');
      return t.innerHTML;
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
        const b = emptyBox.querySelector('[data-empty-new]');
        b?.addEventListener('click', () => openModal(true, null));
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
      } catch (e) {
        emptyBox.classList.remove('hidden');
        emptyBox.innerHTML = `<div class="py-12 text-center text-red-600">No se pudo cargar la lista de programas.</div>`;
      }
    }

    // ===============================
    // EVENTOS MODAL + FORM
    // ===============================
    btnNew?.addEventListener('click', () => openModal(true, null));
    btnClose?.addEventListener('click', closeModal);
    btnCancel?.addEventListener('click', (e) => { e.preventDefault(); closeModal(); });

    form?.addEventListener('submit', async (e) => {
      e.preventDefault();

      const id_programa     = (inpCode?.value  || '').trim();
      const nombre_programa = (inpName?.value  || '').trim();
      const descripcion     = (inpDesc?.value  || '').trim();
      const duracion        = (inpHours?.value || '').trim();

      // Validaciones simples
      if (!id_programa) return alert('El código (id_programa) es obligatorio.');
      if (!nombre_programa) return alert('El nombre del programa es obligatorio.');
      if (duracion !== '' && Number.isNaN(Number(duracion))) return alert('Duración debe ser numérica.');

      const payload = { id_programa, nombre_programa, descripcion, duracion };

      try {
        const res = editingId ? await apiActualizar(payload) : await apiAgregar(payload);
        if (res?.error) return alert(res.error);
        closeModal();
        await loadPrograms();
      } catch (err) {
        alert('No se pudo guardar el programa.');
      }
    });

    // ===============================
    // INIT
    // ===============================
    loadPrograms();
  })();
});
