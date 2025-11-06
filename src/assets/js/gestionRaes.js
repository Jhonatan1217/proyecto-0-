// src/assets/js/gestionRaes.js
(function () {
  const section = document.querySelector('section[data-tab="raes"]');
  if (!section) return;

  // ==== Endpoints ====
  const API_RAES = (window.API_RAES || (window.BASE_URL || '') + 'src/controllers/RaeController.php').replace(/\/+$/, '');

  // ==== Selectores filtros/listado ====
  const selProgFilter  = section.querySelector('#raeProgramFilter');
  const selCompFilter  = section.querySelector('#raeCompetencyFilter');
  const list           = section.querySelector('#raesList');
  const emptyBox       = section.querySelector('#raesEmpty');

  // ==== Modal ====
  const modal     = document.getElementById('modalRae');
  const backdrop  = document.getElementById('modalRaeBackdrop');
  const btnNew    = Array.from(section.querySelectorAll('button'))
                     .find(b => (b.textContent || '').toLowerCase().includes('nuevo rae'));
  const btnClose  = document.getElementById('btnCloseRae');
  const btnCancel = document.getElementById('btnCancelRae');
  const form      = document.getElementById('formRaeNew');
  const inCode    = document.getElementById('rae_code');     // aquí va id_rae
  const inDesc    = document.getElementById('rae_desc');
  const selComp   = document.getElementById('rae_competency');

  // ==== Inyectar select de Programas en el modal (sin tocar HTML base) ====
  let selProgInForm = null;
  (function injectProgramSelectInModal(){
    if (!form) return;
    const firstGroup = form.querySelector('div'); // insertar antes del campo "Competencia"
    const wrapper = document.createElement('div');
    wrapper.innerHTML = `
      <label class="block text-sm font-medium mb-1">Programa *</label>
      <select id="rae_program" class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm bg-white">
        <option value="">Seleccione un programa</option>
      </select>
    `;
    form.insertBefore(wrapper, firstGroup);
    selProgInForm = wrapper.querySelector('#rae_program');
  })();

  // ==== Fetch helpers ====
  const q = p => new URLSearchParams(p).toString();

  async function fetchJSON(url) {
    const res = await fetch(url);
    if (!res.ok) throw new Error('HTTP ' + res.status);
    return res.json();
  }

  // ==== Cargar Programas para filtros y modal ====
  async function loadPrograms() {
    const data = await fetchJSON(`${API_RAES}?accion=programas`);
    const progs = Array.isArray(data) ? data : (data.data || []);

    // Filtro de programas: trae TODOS; mostramos ID – Nombre para mayor claridad
    if (selProgFilter) {
      selProgFilter.innerHTML = `<option value="all">Todos los programas</option>`;
      for (const p of progs) {
        const opt = document.createElement('option');
        opt.value = String(p.id_programa);
        opt.textContent = `${p.id_programa} – ${p.nombre_programa}`;
        selProgFilter.appendChild(opt);
      }
    }

    // Modal: Programas (ID – Nombre)
    if (selProgInForm) {
      selProgInForm.innerHTML = `<option value="">Seleccione un programa</option>`;
      for (const p of progs) {
        const opt = document.createElement('option');
        opt.value = String(p.id_programa);
        opt.textContent = `${p.id_programa} – ${p.nombre_programa}`;
        selProgInForm.appendChild(opt);
      }
    }
  }

  // ==== Cargar Competencias por programa (para filtro y modal) ====
  async function loadCompetenciasFor(programId, targetSelect, withNames = false) {
    // Filtro: si no hay programa, mostrar "Todas…"
    if (targetSelect === selCompFilter && (!programId || programId === 'all')) {
      targetSelect.innerHTML = `<option value="all">Todas las competencias</option>`;
      return;
    }

    if (!programId || programId === 'all') {
      // Modal: si no hay programa, deja solo placeholder
      targetSelect.innerHTML = `<option value="">Seleccione una competencia</option>`;
      return;
    }

    const data = await fetchJSON(`${API_RAES}?accion=competenciasPorPrograma&${q({id_programa: programId})}`);
    const comps = Array.isArray(data) ? data : (data.data || []);

    targetSelect.innerHTML = targetSelect === selCompFilter
      ? `<option value="all">Todas las competencias</option>`
      : `<option value="">Seleccione una competencia</option>`;

    for (const c of comps) {
      const opt = document.createElement('option');
      opt.value = String(c.id_competencia); // value = ID de competencia (código)
      // Texto:
      // - En filtro: SOLO el código (ID)
      // - En modal: ID – Nombre (más amigable), pero el value sigue siendo el ID
      opt.textContent = targetSelect === selCompFilter || !withNames
        ? String(c.id_competencia)
        : `${c.id_competencia} – ${c.nombre_competencia}`;
      targetSelect.appendChild(opt);
    }
  }

  // ==== Listar RAEs con filtros ====
  async function loadRaes() {
    const id_programa    = selProgFilter?.value || 'all';
    const id_competencia = selCompFilter?.value || 'all';

    const url = `${API_RAES}?accion=listar&${q({ id_programa, id_competencia })}`;
    const rows = await fetchJSON(url);

    // Render
    list.innerHTML = '';
    const data = Array.isArray(rows) ? rows : (rows.data || []);
    if (!data.length) {
      emptyBox.classList.remove('hidden');
      return;
    }
    emptyBox.classList.add('hidden');

    for (const r of data) {
      const card = document.createElement('div');
      card.className = 'rounded-2xl ring-1 ring-zinc-200 shadow-sm p-4';
      card.innerHTML = `
        <div class="flex items-center justify-between">
          <div>
            <div class="text-sm text-zinc-500">ID RAE</div>
            <div class="font-semibold">${escapeHtml(r.id_rae)}</div>
          </div>
          <span class="text-xs px-2 py-1 rounded-full ${Number(r.estado) ? 'bg-green-100 text-green-700' : 'bg-zinc-100 text-zinc-600'}">
            ${Number(r.estado) ? 'Activo' : 'Inactivo'}
          </span>
        </div>
        <div class="mt-3 text-sm"><span class="text-zinc-500">Competencia (ID):</span> <b>${escapeHtml(r.id_competencia)}</b></div>
        <div class="text-sm"><span class="text-zinc-500">Programa (ID):</span> <b>${escapeHtml(r.id_programa ?? '')}</b></div>
        <p class="mt-3 text-sm">${escapeHtml(r.descripcion)}</p>
      `;
      list.appendChild(card);
    }
  }

  // ==== Helpers ====
  function escapeHtml(s) {
    return String(s ?? '')
      .replaceAll('&','&amp;').replaceAll('<','&lt;')
      .replaceAll('>','&gt;').replaceAll('"','&quot;')
      .replaceAll("'","&#039;");
  }

  // ==== Abrir / Cerrar modal ====
  function openModal() {
    if (form) form.reset();
    if (inCode) inCode.value = ''; // el código lo autollenaremos al elegir la competencia
    // limpiar competencias del modal
    if (selComp) selComp.innerHTML = `<option value="">Seleccione una competencia</option>`;
    backdrop.classList.remove('hidden');
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    window.lucide?.createIcons();
  }
  function closeModal() {
    backdrop.classList.add('hidden');
    modal.classList.add('hidden');
    document.body.style.overflow = '';
  }

  // ==== Eventos UI ====
  btnNew && btnNew.addEventListener('click', openModal);
  btnClose && btnClose.addEventListener('click', closeModal);
  btnCancel && btnCancel.addEventListener('click', closeModal);
  backdrop.addEventListener('click', closeModal);
  modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal(); });

  // Filtro: al cambiar programa => cargar competencias (SOLO códigos) y recargar lista
  selProgFilter?.addEventListener('change', async () => {
    await loadCompetenciasFor(selProgFilter.value, selCompFilter, false); // filtro muestra solo el ID
    await loadRaes();
  });

  // Filtro: al cambiar competencia => recargar lista
  selCompFilter?.addEventListener('change', loadRaes);

  // Modal: al cambiar programa => cargar competencias del programa (en modal ID – Nombre) y limpiar código
  selProgInForm?.addEventListener('change', async () => {
    const pid = selProgInForm.value;
    if (inCode) inCode.value = ''; // ya no autocompletamos con programa; se autocompleta con la COMPETENCIA
    if (pid) {
      await loadCompetenciasFor(pid, selComp, true); // en modal: ID – Nombre
    } else {
      selComp.innerHTML = `<option value="">Seleccione una competencia</option>`;
    }
  });

  // Modal: al cambiar COMPETENCIA => autocompletar código con "<id_competencia>-"
  selComp?.addEventListener('change', () => {
    const cid = selComp.value || '';
    if (inCode) inCode.value = cid ? `${cid}-` : '';
  });

  // Submit: crear RAE (usa id_rae = #rae_code)
  form?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const id_rae         = (inCode?.value || '').trim();
    const descripcion    = (inDesc?.value || '').trim();
    const id_competencia = (selComp?.value || '').trim();

    if (!id_rae || !descripcion || !id_competencia) {
      alert('Completa Programa, Competencia, Código y Descripción.');
      return;
    }

    const url = `${API_RAES}?accion=crear&${q({ id_rae, descripcion, id_competencia })}`;
    const res = await fetchJSON(url);
    if (res.error) {
      alert(res.error);
      return;
    }
    closeModal();
    await loadRaes();
  });

  // ==== Init ====
  (async function init(){
    await loadPrograms();                                   // llena filtros y modal (programas)
    await loadCompetenciasFor('all', selCompFilter, false); // filtro competencias: "Todas…"
    await loadRaes();                                       // render inicial
  })();
})();
