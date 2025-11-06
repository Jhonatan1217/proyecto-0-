// src/assets/js/gestionCompetencias.js
(function () {
  // ===============================
  // CONFIG & GUARD CLAUSE
  // ===============================
  const BASE = (window.BASE_URL || '').replace(/\/+$/, '');
  const API_COMP = (window.API_COMPETENCIAS || BASE + 'src/controllers/CompetenciasController.php').replace(/\/+$/, '');
  const API_PROG = (window.API_PROGRAMAS     || BASE + 'src/controllers/ProgramasController.php').replace(/\/+$/, '');
  // Opcional: si tienes endpoint de RAEs, déjalo configurado. Si no, no pasa nada.
  const API_RAE  = (window.API_RAES || BASE + 'src/controllers/RaesController.php').replace(/\/+$/, '');

  const tabCompetencies = document.querySelector('[data-tab="competencies"]');
  if (!tabCompetencies) return;

  // ===============================
  // SELECTORES
  // ===============================
  const list          = document.getElementById('competenciesList');
  const emptyBox      = document.getElementById('competenciesEmpty');
  const btnNew        = document.getElementById('btnNewCompetency');
  const filterProgram = document.getElementById('competencyProgramFilter');

  const modal     = document.getElementById('modalCompetency');
  const backdrop  = document.getElementById('modalCompetencyBackdrop');
  const form      = document.getElementById('formCompetencyNew');

  const selProg   = document.getElementById('cp_program');
  const inpCode   = document.getElementById('cp_code'); // Código (lo usamos como id_competencia)
  const inpName   = document.getElementById('cp_name');
  const inpDesc   = document.getElementById('cp_desc');

  // Estado interno
  let PROGRAMS = [];          // [{id, nombre_programa, ...}]
  let COMPETENCIAS = [];      // competencias normalizadas
  let RAE_MAP = {};           // { id_competencia: [ {codigo_rae, nombre_rae} ] }
  let editingId = null;

  // ===============================
  // UTILS
  // ===============================
  const e = (s) => String(s ?? '')
    .replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;')
    .replaceAll('"','&quot;').replaceAll("'","&#039;");

  const show = (el) => el?.classList.remove('hidden');
  const hide = (el) => el?.classList.add('hidden');

  const programNameById = (id) => {
    const p = PROGRAMS.find(p => String(p.id) === String(id) || String(p.id_programa) === String(id));
    return p?.name || p?.nombre_programa || '—';
  };

  const mapCompetencia = (raw) => ({
    id:        raw.id_competencia ?? raw.id ?? null,
    program_id:raw.program_id ?? raw.id_programa ?? raw.programa_id ?? null,
    code:      raw.codigo_competencia ?? raw.code ?? raw.codigo ?? '',
    name:      raw.nombre_competencia ?? raw.name ?? raw.nombre ?? '',
    desc:      raw.descripcion ?? raw.description ?? '',
    estado:    typeof raw.estado !== 'undefined' ? Number(raw.estado) : 1,
  });

  // ===============================
  // FETCH HELPERS
  // ===============================
  async function apiGet(url) {
    const r = await fetch(url, { headers: { 'Accept': 'application/json' } });
    if (!r.ok) throw new Error(`HTTP ${r.status}`);
    return r.json();
  }
  async function apiJson(url, data) {
    const r = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept':'application/json' },
      body: JSON.stringify(data)
    });
    if (!r.ok) throw new Error(`HTTP ${r.status}`);
    return r.json();
  }

  // ===============================
  // CARGAS
  // ===============================
  async function loadPrograms() {
    try {
      const res = await apiGet(`${API_PROG}?accion=listar`);
      PROGRAMS = Array.isArray(res) ? res : (res?.data || []);

      // Filtro
      if (filterProgram) {
        filterProgram.querySelectorAll('option:not([value="all"])').forEach(o => o.remove());
        PROGRAMS.forEach(p => {
          const opt = document.createElement('option');
          opt.value = String(p.id ?? p.id_programa ?? '');
          opt.textContent = p.name ?? p.nombre_programa ?? `Programa ${opt.value}`;
          filterProgram.appendChild(opt);
        });
      }
      // Select modal
      if (selProg) {
        selProg.querySelectorAll('option:not([value=""])').forEach(o => o.remove());
        PROGRAMS.forEach(p => {
          const opt = document.createElement('option');
          opt.value = String(p.id ?? p.id_programa ?? '');
          opt.textContent = p.name ?? p.nombre_programa ?? `Programa ${opt.value}`;
          selProg.appendChild(opt);
        });
      }
    } catch (err) {
      console.error('[Competencias] No se pudieron cargar programas:', err);
    }
  }

  async function loadCompetencias() {
    try {
      const res = await apiGet(`${API_COMP}?accion=listar`);
      const data = Array.isArray(res) ? res : (res?.data || []);
      COMPETENCIAS = data.map(mapCompetencia);
      renderList();
    } catch (err) {
      console.error('[Competencias] Error al listar:', err);
      COMPETENCIAS = [];
      renderList();
    }
  }

  // Opcional: precarga de RAEs agrupados por competencia, si existe el endpoint
  async function tryLoadRaeMap() {
    try {
      const res = await apiGet(`${API_RAE}?accion=listar`);
      const arr = Array.isArray(res) ? res : (res?.data || []);
      // normalizar por id_competencia
      RAE_MAP = {};
      arr.forEach(r => {
        const idc = String(r.id_competencia ?? r.competencia_id ?? '');
        if (!idc) return;
        (RAE_MAP[idc] ||= []).push({
          codigo: r.codigo_rae ?? r.codigo ?? '',
          nombre: r.nombre_rae ?? r.nombre ?? r.titulo ?? '',
        });
      });
      // refrescar conteos
      renderList();
    } catch (_e) {
      // Silencioso: si no hay API de RAEs no rompemos la UI
      RAE_MAP = {};
    }
  }

  // ===============================
  // RENDER
  // ===============================
  function statusChip(estado) {
    const cls = estado ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200' : 'bg-rose-50 text-rose-700 ring-1 ring-rose-200';
    const txt = estado ? 'Activo' : 'Inhabilitado';
    return `<span class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium rounded-full ${cls}">${txt}</span>`;
  }

  function raePill(count) {
    return `
      <span class="inline-flex items-center gap-2 text-xs px-3 py-1 rounded-full bg-zinc-100 text-zinc-700 ring-1 ring-zinc-200">
        <i data-lucide="list-checks" class="w-3.5 h-3.5"></i>
        ${count} RAEs
      </span>`;
  }

  function renderRaeItemsHtml(items) {
    if (!items || !items.length) return '';
    return items.map(it => `
      <div class="rounded-xl ring-1 ring-zinc-200 bg-white px-4 py-3">
        <div class="text-xs text-zinc-500 font-medium mb-1">${e(it.codigo)}</div>
        <div class="text-sm text-zinc-700">${e(it.nombre)}</div>
      </div>
    `).join('');
  }

  function renderList() {
    if (!list) return;

    const progFilter = filterProgram?.value || 'all';
    const filtered = COMPETENCIAS.filter(c => {
      if (!progFilter || progFilter === 'all') return true;
      if (!c.program_id) return true;
      return String(c.program_id) === String(progFilter);
    });

    list.innerHTML = '';
    if (filtered.length === 0) { show(emptyBox); return; }
    hide(emptyBox);

    filtered.forEach(c => {
      const raes = RAE_MAP[String(c.id)] || [];
      const li = document.createElement('div');
      li.className = 'rounded-2xl ring-1 ring-zinc-200 shadow-sm bg-white overflow-hidden';
      li.innerHTML = `
        <div class="p-5">
          <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
              <div class="text-sm text-zinc-500 flex items-center gap-2 mb-1">
                ${c.program_id ? `<span class="inline-flex items-center gap-2">
                  <i data-lucide="graduation-cap" class="w-4 h-4"></i>
                  ${e(programNameById(c.program_id))}
                </span>` : '<span>—</span>'}
                <span class="text-zinc-300">•</span>
                ${statusChip(c.estado)}
              </div>
              <h3 class="text-xl font-semibold text-zinc-900">${e(c.name || '(Sin nombre)')}</h3>
              <p class="text-sm text-zinc-500 mt-1">Código: <span class="font-medium">${e(c.code || '—')}</span></p>
              ${c.desc ? `<p class="text-sm text-zinc-600 mt-3">${e(c.desc)}</p>` : ''}
              <div class="mt-3">${raePill(raes.length)}</div>
            </div>

            <div class="shrink-0 flex items-center gap-2">
              <button class="btn-edit inline-flex items-center gap-2 rounded-lg border border-zinc-200 px-3 py-2 text-sm hover:bg-zinc-50" data-id="${e(c.id)}" title="Editar">
                <i data-lucide="pencil" class="w-4 h-4"></i> Editar
              </button>
              <button
                class="btn-toggle inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-white ${c.estado ? 'bg-rose-600 hover:bg-rose-700' : 'bg-emerald-600 hover:bg-emerald-700'}"
                data-id="${e(c.id)}"
                data-next="${c.estado ? 0 : 1}">
                <i data-lucide="${c.estado ? 'ban' : 'check'}" class="w-4 h-4"></i>
                ${c.estado ? 'Inhabilitar' : 'Activar'}
              </button>
              <button class="btn-collapse inline-flex items-center justify-center rounded-lg border border-zinc-200 w-10 h-10 hover:bg-zinc-50" data-target="rae-${e(c.id)}" aria-expanded="false" title="Mostrar RAEs">
                <i data-lucide="chevron-down" class="w-5 h-5"></i>
              </button>
            </div>
          </div>
        </div>

        <div class="border-t border-zinc-200 px-5 py-4 hidden" id="rae-${e(c.id)}" role="region" aria-label="RAEs">
          <div class="text-sm font-semibold text-zinc-900 mb-3">Resultados de Aprendizaje Esperados (RAE)</div>
          <div class="grid gap-3">
            ${renderRaeItemsHtml(raes)}
          </div>
        </div>
      `;
      list.appendChild(li);
    });

    // Bind actions
    window.lucide?.createIcons();
    list.querySelectorAll('.btn-edit').forEach(b => b.addEventListener('click', onEditClick));
    list.querySelectorAll('.btn-toggle').forEach(b => b.addEventListener('click', onToggleClick));
    list.querySelectorAll('.btn-collapse').forEach(b => b.addEventListener('click', onCollapseClick));
  }

  // ===============================
  // EVENTS
  // ===============================
  btnNew?.addEventListener('click', () => {
    editingId = null;
    form?.reset();
    openModal();
  });

  document.getElementById('btnCloseCompetency')?.addEventListener('click', closeModal);
  document.getElementById('btnCancelCompetency')?.addEventListener('click', closeModal);
  backdrop?.addEventListener('click', (e) => { if (e.target === backdrop) closeModal(); });

  filterProgram?.addEventListener('change', renderList);

  function openModal() {
    show(backdrop); show(modal);
    form?.reset();
    editingId = null;
    window.lucide?.createIcons();
  }
  function closeModal() {
    hide(backdrop); hide(modal);
    form?.reset();
    editingId = null;
  }

  function onEditClick(e) {
    const id = e.currentTarget.getAttribute('data-id');
    const item = COMPETENCIAS.find(x => String(x.id) === String(id));
    if (!item) return;

    editingId = item.id;
    if (selProg && item.program_id) selProg.value = String(item.program_id);
    if (inpCode) inpCode.value = item.code || '';
    if (inpName) inpName.value = item.name || '';
    if (inpDesc) inpDesc.value = item.desc || '';

    openModal();
  }

  async function onToggleClick(e) {
    const id    = e.currentTarget.getAttribute('data-id');
    const next  = Number(e.currentTarget.getAttribute('data-next') || 0);
    try {
      const res = await apiJson(`${API_COMP}?accion=inhabilitar`, { id_competencia: id, estado: next });
      if (res?.error) throw new Error(res.error);
      await loadCompetencias();
    } catch (err) {
      console.error('[Competencias] inhabilitar/activar:', err);
      alert('No fue posible cambiar el estado. Revisa la consola.');
    }
  }

  function onCollapseClick(e) {
    const btn = e.currentTarget;
    const targetId = btn.getAttribute('data-target');
    const panel = document.getElementById(targetId);
    if (!panel) return;
    const expanded = btn.getAttribute('aria-expanded') === 'true';
    btn.setAttribute('aria-expanded', String(!expanded));
    panel.classList.toggle('hidden', expanded);
    const icon = btn.querySelector('[data-lucide]');
    if (icon) icon.setAttribute('data-lucide', expanded ? 'chevron-down' : 'chevron-up');
    window.lucide?.createIcons();
  }

  // ===============================
  // SUBMIT (crear / actualizar)
  // ===============================
  form?.addEventListener('submit', async (ev) => {
    ev.preventDefault();

    const idFromCode = (inpCode?.value || '').trim();
    const payload = {
      id_competencia: idFromCode,                         // importante si tu tabla exige PK explícita
      nombre_competencia: (inpName?.value || '').trim(),
      descripcion: (inpDesc?.value || '').trim(),
      id_programa: (selProg?.value || '').trim() || null,
      codigo_competencia: idFromCode
    };

    if (!payload.nombre_competencia || !payload.descripcion) {
      alert('Debe diligenciar Nombre y Descripción.');
      return;
    }
    if (!editingId && !payload.id_competencia) {
      alert('El Código es obligatorio (se usa como id_competencia).');
      return;
    }

    try {
      if (editingId) {
        const res = await apiJson(`${API_COMP}?accion=actualizar`, {
          id_competencia: editingId,
          nombre_competencia: payload.nombre_competencia,
          descripcion: payload.descripcion
        });
        if (res?.error) throw new Error(res.error);
      } else {
        const res = await apiJson(`${API_COMP}?accion=crear`, payload);
        if (res?.error) throw new Error(res.error);
      }
      closeModal();
      await loadCompetencias();
      await tryLoadRaeMap();
    } catch (err) {
      console.error('[Competencias] crear/actualizar:', err);
      alert('No fue posible guardar la competencia. Revisa la consola.');
    }
  });

  // ===============================
  // INIT
  // ===============================
  (async function init(){
    await loadPrograms();
    await loadCompetencias();
    await tryLoadRaeMap(); // si no existe el endpoint, no rompe la UI
  })();
})();
