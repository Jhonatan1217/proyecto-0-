// src/assets/js/gestionCompetencias.js
(function () {
  // ===============================
  // CONFIG (usa LOS ENDPOINTS que expones en tu PHP)
  // ===============================
  const BASE = (window.BASE_URL || '').replace(/\/+$/, '');
  const API_COMP = (window.API_COMPETENCIAS || (BASE + 'src/controllers/CompetenciaController.php')).replace(/\/+$/, '');
  const API_PROG = (window.API_PROGRAMAS     || (BASE + 'src/controllers/ProgramasController.php')).replace(/\/+$/, '');
  const API_RAE  = (window.API_RAES          || (BASE + 'src/controllers/RaeController.php')).replace(/\/+$/, '');

  // Rutas de íconos (usaremos <img>, no lucide)
  const ICON_DOWN   = `${BASE}src/assets/img/chevron-down.svg`;
  const ICON_RIGHT  = `${BASE}src/assets/img/chevron-right.svg`;
  const ICON_PENCIL = `${BASE}src/assets/img/pencil-line.svg`;
  const ICON_PLUS   = `${BASE}src/assets/img/plus.svg`;
  const ICON_LIST   = `${BASE}src/assets/img/list-checks.svg`;

  // RAEs cerrados por defecto
  const INITIAL_RAES_OPEN = false;

  // Carga solo en la pestaña de Competencias
  const tab = document.querySelector('[data-tab="competencies"]');
  if (!tab) return;

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
  const inpCode   = document.getElementById('cp_code'); // lo usas como id_competencia
  const inpName   = document.getElementById('cp_name');
  const inpDesc   = document.getElementById('cp_desc');

  // Para animación / título
  const modalCard = modal?.querySelector('.modal-card');
  const titleComp = document.getElementById('titleCompetency');

  // ===============================
  // TOASTS (SweetAlert2)
  // ===============================
  const Toast = (window.Swal ? Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 2200,
    timerProgressBar: true
  }) : null);

  const t = {
    ok:   (m) => Toast && Toast.fire({ icon: 'success', title: m || 'Operación exitosa' }),
    info: (m) => Toast && Toast.fire({ icon: 'info',    title: m || 'Información' }),
    warn: (m) => Toast && Toast.fire({ icon: 'warning', title: m || 'Revisa los datos' }),
    err:  (m) => Toast && Toast.fire({ icon: 'error',   title: m || 'Ocurrió un error' })
  };

  // ===============================
  // ESTADO
  // ===============================
  let PROGRAMS = [];     // [{id_programa, nombre_programa, ...}] o variantes
  let ITEMS = [];        // competencias normalizadas
  let RAE_MAP = {};      // { id_competencia: [ {codigo, nombre} ] }
  let editingId = null;
  let editingSnap = null; // snapshot original para comparar cambios

  // ===============================
  // UTILS
  // ===============================
  const e = (s) => String(s ?? '')
    .replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;')
    .replaceAll('"','&quot;').replaceAll("'","&#039;");

  const show = (el) => el?.classList.remove('hidden');
  const hide = (el) => el?.classList.add('hidden');

  const apiGet = async (url) => {
    const r = await fetch(url, { headers: { 'Accept': 'application/json' } });
    if (!r.ok) throw new Error(`HTTP ${r.status}`);
    return r.json();
  };
  const apiJson = async (url, data) => {
    const r = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept':'application/json' },
      body: JSON.stringify(data)
    });
    if (!r.ok) throw new Error(`HTTP ${r.status}`);
    return r.json();
  };

  // tolerancia de nombres (no rompemos tu backend)
  const mapCompetencia = (raw) => ({
    id:          raw.id_competencia ?? raw.id ?? raw.codigo ?? null,
    program_id:  raw.id_programa ?? raw.program_id ?? raw.programa_id ?? raw.programa ?? null,
    code:        raw.codigo_competencia ?? raw.codigo ?? raw.code ?? String(raw.id_competencia ?? ''),
    name:        raw.nombre_competencia ?? raw.nombre ?? raw.name ?? '',
    desc:        raw.descripcion     ?? raw.description ?? raw.detalle ?? '',
    estado:      typeof raw.estado !== 'undefined' ? Number(raw.estado) : 1,
  });

  const programNameById = (id) => {
    const sid = String(id);
    const p = PROGRAMS.find(p =>
      String(p.id) === sid ||
      String(p.id_programa) === sid ||
      String(p.programa_id) === sid
    );
    return p?.nombre_programa || p?.name || p?.nombre || '—';
  };

  // ===============================
  // CARGAS
  // ===============================
// ===============================
// CARGAS
// ===============================
async function loadPrograms({ preserveSelection = true } = {}) {
  try {
    // Guarda selección actual (si aplica)
    const prevFilterValue = preserveSelection && filterProgram ? filterProgram.value : null;
    const prevSelProg     = preserveSelection && selProg       ? selProg.value       : null;

    const res = await apiGet(`${API_PROG}?accion=listar`);
    PROGRAMS = Array.isArray(res) ? res : (res?.data || []);

    // Limpia opciones manteniendo los placeholders
    if (filterProgram) filterProgram.querySelectorAll('option:not([value="all"])').forEach(o => o.remove());
    if (selProg)       selProg.querySelectorAll('option:not([value=""])').forEach(o => o.remove());

    // Repuebla
    PROGRAMS.forEach(p => {
      const id   = String(p.id ?? p.id_programa ?? p.programa_id ?? '');
      const name = p.nombre_programa ?? p.name ?? p.nombre ?? `Programa ${id}`;
      if (filterProgram) {
        const opt = document.createElement('option');
        opt.value = id; opt.textContent = name;
        filterProgram.appendChild(opt);
      }
      if (selProg) {
        const opt = document.createElement('option');
        opt.value = id; opt.textContent = name;
        selProg.appendChild(opt);
      }
    });

    // Restaura selección si sigue existiendo (NO auto-rellena con el recién creado)
    if (preserveSelection && filterProgram) {
      const exists = Array.from(filterProgram.options).some(o => o.value === prevFilterValue);
      filterProgram.value = exists ? prevFilterValue : 'all';
    }
    if (preserveSelection && selProg) {
      const exists = Array.from(selProg.options).some(o => o.value === prevSelProg);
      selProg.value = exists ? prevSelProg : '';
    }
  } catch (err) {
    console.error('[Competencias] loadPrograms:', err);
    t.err('No se pudieron cargar los programas');
  }
}


  async function loadCompetencias() {
    try {
      const res = await apiGet(`${API_COMP}?accion=listar`);
      const arr = Array.isArray(res) ? res : (res?.data || []);
      ITEMS = arr.map(mapCompetencia);
      renderList();
    } catch (err) {
      console.error('[Competencias] loadCompetencias]:', err);
      ITEMS = [];
      renderList();
      t.err('No se pudieron cargar las competencias');
    }
  }

  async function tryLoadRaeMap() {
    try {
      const res = await apiGet(`${API_RAE}?accion=listar`);
      const arr = Array.isArray(res) ? res : (res?.data || []);
      RAE_MAP = {};

      arr.forEach(r => {
        const idc = String(r.id_competencia ?? r.competencia_id ?? r.idCompetencia ?? '');
        if (!idc) return;

        const codigo = [
          r.codigo_rae, r.codigoRAE, r.codigo, r.id_rae, r.idRAE, r.clave, r.ref
        ].find(v => (v ?? '') !== '') ?? '';

        const nombre = [
          r.nombre_rae, r.nombreRAE, r.nombre, r.titulo, r.texto,
          r.descripcion_rae, r.descripcionRAE, r.descripcion, r.detalle
        ].find(v => (v ?? '') !== '') ?? '';

        const cod = String(codigo ?? '').trim();
        const nom = String(nombre ?? '').trim();

        (RAE_MAP[idc] ||= []).push({
          codigo: cod || '—',
          nombre: nom || '—'
        });
      });

      renderList();
    } catch (_e) {
      RAE_MAP = {};
      // sin toast aquí para no molestar si no hay RAEs
    }
  }

  // ===============================
  // RENDER
  // ===============================
  // === Badge de estado con colores exactos (#39a900 activo, gris claro inhabilitado)
function statusChip(estado) {
  if (Number(estado) === 1) {
    // Activo -> verde exacto
    return `
      <span
        class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium rounded-full"
        style="background:#eaf7e6;border:1px solid rgba(57,169,0,.22);color:#39a900"
      >
        Activo
      </span>
    `;
  }
  // Inhabilitado -> gris claro
  return `
    <span
      class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium rounded-full"
      style="background:#f3f4f6;border:1px solid #e5e7eb;color:#6b7280"
    >
      Inhabilitado
    </span>
  `;
}


  function raeCountPill(count) {
    return `
      <span class="inline-flex items-center gap-2 text-xs px-3 py-1 rounded-full bg-zinc-100 text-zinc-700 ring-1 ring-zinc-200">
        <img src="${ICON_LIST}" class="w-3.5 h-3.5" alt="" />
        ${count} RAEs
      </span>`;
  }

  function renderRaeItemsHtml(items) {
    if (!items || !items.length) return '';
    return items.map(it => `
      <div class="rounded-xl bg-zinc-50 ring-1 ring-zinc-200 px-4 py-4">
        <div class="flex items-center gap-4">
          <span class="inline-flex items-center whitespace-nowrap rounded-full bg-zinc-100 ring-1 ring-zinc-300 px-3 py-1 text-xs font-semibold text-zinc-700">
            ${e(it.codigo || '—')}
          </span>
          <p class="text-sm text-zinc-700 leading-relaxed">${e(it.nombre || '—')}</p>
        </div>
      </div>
    `).join('');
  }

  function renderSwitchHtml(id, estado) {
    return `
      <label class="switch cursor-pointer" data-switch="${e(id)}" title="${estado ? 'Activo' : 'Inhabilitado'}" aria-label="Cambiar estado">
        <input type="checkbox" ${estado ? 'checked' : ''} />
        <span class="dot"></span>
      </label>
    `;
  }

  function paintSwitch(el) {
    try {
      const input = el.querySelector('input');
      const setBg = () => { el.style.background = input.checked ? '#39a900' : '#e5e7eb'; };
      setBg();
      input.addEventListener('change', setBg);
    } catch {}
  }

  function renderList() {
    if (!list) return;

    const pf = filterProgram?.value || 'all';
    const isFiltering = !!filterProgram && pf !== 'all';
    const data = ITEMS.filter(c => {
      if (!isFiltering) return true;
      if (!c.program_id) return false;
      return String(c.program_id) === String(pf);
    });

    // Limpia el grid de tarjetas SIEMPRE
    list.innerHTML = '';

    // --- Caso A: NO hay NINGUNA competencia en DB ---
    if (!ITEMS.length) {
      // emptyBox actúa como el panel con borde
      if (emptyBox) {
        show(emptyBox);
        emptyBox.innerHTML = `
          <div class="w-full rounded-2xl border border-zinc-200 bg-white">
            <div class="flex flex-col items-center justify-center text-center py-16">
              <p class="text-zinc-500 mb-5 text-sm sm:text-base">
                No hay competencias registrados
              </p>
              <button id="btnFirstCompetency"
                      class="flex items-center gap-2  bg-[#00324d] text-white px-4 py-2 rounded-xl font-medium text-sm">
                <img src="${ICON_PLUS}" class="w-4 h-4" alt="simbolo de mas" />
                Crear Primera Competencia
              </button>
            </div>
          </div>
        `;
        document.getElementById('btnFirstCompetency')
          ?.addEventListener('click', () => btnNew?.click());
      }
      return;
    }

    // --- Caso B: SÍ hay competencias, pero el FILTRO no devuelve ninguna ---
    if (!data.length) {
      if (emptyBox) {
        show(emptyBox);
        emptyBox.innerHTML = `
          <div class="w-full rounded-2xl border border-zinc-200 bg-white">
            <div class="flex items-center justify-center text-center py-16">
              <p class="text-zinc-500 text-sm sm:text-base">
                No hay competencias que coincidan con el filtro seleccionado.
              </p>
            </div>
          </div>
        `;
      }
      return;
    }

    // --- Caso C: hay resultados para renderizar ---
    if (emptyBox) {
      // oculta el panel vacío y limpia su contenido por si venía de A/B
      emptyBox.innerHTML = '';
      hide(emptyBox);
    }

    data.forEach(c => {
      const raes = RAE_MAP[String(c.id)] || [];
      const isOpen = INITIAL_RAES_OPEN;
      const card = document.createElement('div');
      card.className = 'rounded-2xl ring-1 ring-zinc-200 shadow-sm bg-white overflow-hidden';

      const raeTarget = `rae-${e(c.id)}`;
      const iconSrc  = isOpen ? ICON_DOWN : ICON_RIGHT;

      card.innerHTML = `
        <div class="p-5">
          <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
              <div class="flex items-center gap-2">
                <button class="btn-collapse inline-flex items-center justify-center p-1 text-zinc-700 hover:text-zinc-900"
                        data-target="${raeTarget}" aria-expanded="${isOpen}" title="${isOpen ? 'Ocultar RAEs' : 'Mostrar RAEs'}">
                  <img src="${iconSrc}" alt="toggle" class="w-4 h-4 transition-transform duration-200" />
                </button>
                <h3 class="text-2xl font-bold tracking-tight text-zinc-900">${e(c.name || '(Sin nombre)')}</h3>
              </div>

              <p class="text-sm text-zinc-500 mt-2 flex items-center gap-2 flex-wrap">
                <span><span class="opacity-80">Código:</span> <span class="font-semibold">${e(c.code || '—')}</span></span>
                <span class="text-zinc-300">•</span>
                <span class="inline-flex items-center rounded-full bg-zinc-100 text-zinc-600 px-3 py-1 text-xs font-medium">
                  ${e(programNameById(c.program_id) || '—')}
                </span>
                <span class="text-zinc-300">•</span>
                ${statusChip(c.estado)}
              </p>

              ${c.desc ? `<p class="text-sm text-zinc-700 mt-3">${e(c.desc)}</p>` : ''}

              <div class="mt-4">${raeCountPill(raes.length)}</div>
            </div>

            <div class="shrink-0 flex items-center gap-3">
              <button class="btn-edit inline-flex items-center justify-center p-2 text-zinc-600 hover:text-zinc-900" data-id="${e(c.id)}" title="Editar">
                <img src="${ICON_PENCIL}" class="w-5 h-5" alt="Editar" />
              </button>
              ${renderSwitchHtml(c.id, !!c.estado)}
            </div>
          </div>
        </div>

        <div class="border-t border-zinc-200 px-5 py-4 ${isOpen ? '' : 'hidden'}" id="${raeTarget}" role="region" aria-label="RAEs">
          <div class="text-sm font-semibold text-zinc-900 mb-3">Resultados de Aprendizaje Esperados (RAE)</div>
          <div class="grid gap-3">
            ${renderRaeItemsHtml( (RAE_MAP[String(c.id)] || []) )}
          </div>
        </div>
      `;

      list.appendChild(card);
    });

    // Listeners de acciones luego del render
    list.querySelectorAll('.btn-edit').forEach(b => b.addEventListener('click', onEditClick));
    list.querySelectorAll('.switch').forEach(sw => {
      paintSwitch(sw);
      const input = sw.querySelector('input');
      const id = sw.getAttribute('data-switch');
      input.addEventListener('change', async () => {
        const next = input.checked ? 1 : 0;
        try {
          const res = await apiJson(`${API_COMP}?accion=inhabilitar`, { id_competencia: id, estado: next });
          if (res?.error) throw new Error(res.error);
          t.ok('Estado actualizado');
          await loadCompetencias();
          await tryLoadRaeMap();
        } catch (err) {
          console.error('[Competencias] cambiar estado:', err);
          input.checked = !input.checked;
          paintSwitch(sw);
          t.err('No fue posible cambiar el estado');
        }
      });
    });
    list.querySelectorAll('.btn-collapse').forEach(b => b.addEventListener('click', onCollapseClick));
  }

  // ===============================
  // EVENTS
  // ===============================
  btnNew?.addEventListener('click', () => {
    editingId = null;
    editingSnap = null;
    form?.reset();

    if (titleComp) titleComp.textContent = 'Nueva Competencia';

    if (inpCode) {
      inpCode.removeAttribute('readonly');
      inpCode.classList.remove('bg-zinc-50');
      inpCode.value = '';
    }
    if (selProg) selProg.value = '';
    if (inpName) inpName.value = '';
    if (inpDesc) inpDesc.value = '';

    openModal(true);
  });

  document.getElementById('btnCloseCompetency')?.addEventListener('click', closeModal);
  document.getElementById('btnCancelCompetency')?.addEventListener('click', closeModal);
  backdrop?.addEventListener('click', (e) => { if (e.target === backdrop) closeModal(); });

  filterProgram?.addEventListener('change', renderList);

  function onCollapseClick(e) {
    const btn = e.currentTarget;
    const targetId = btn.getAttribute('data-target');
    const panel = document.getElementById(targetId);
    if (!panel) return;

    const expanded = btn.getAttribute('aria-expanded') === 'true';
    btn.setAttribute('aria-expanded', String(!expanded));
    panel.classList.toggle('hidden', expanded);

    // Cambiar imagen (no lucide)
    const img = btn.querySelector('img');
    if (img) {
      img.src = expanded ? ICON_RIGHT : ICON_DOWN;
    }

    btn.title = expanded ? 'Mostrar RAEs' : 'Ocultar RAEs';
  }

  function openModal(withAnim = false) {
    show(backdrop); show(modal);
    if (!editingId) form?.reset();

    if (withAnim) {
      backdrop.classList.add('animate-backdrop');
      modalCard?.classList.add('animate-modal');
      const clean = () => {
        backdrop.classList.remove('animate-backdrop');
        modalCard?.classList.remove('animate-modal');
        modalCard?.removeEventListener('animationend', clean);
      };
      modalCard?.addEventListener('animationend', clean);
    }
  }

  function closeModal() {
    hide(backdrop); hide(modal);
    form?.reset();
    editingId = null;
    editingSnap = null;
    if (inpCode) {
      inpCode.removeAttribute('readonly');
      inpCode.classList.remove('bg-zinc-50');
    }
  }

  async function onEditClick(e) {
    const id = e.currentTarget.getAttribute('data-id');

    // Busca en memoria
    let item = ITEMS.find(x => String(x.id) === String(id));

    // Si faltan datos, pide al backend (acepta varias formas)
    if (!item || !item.name || !item.program_id) {
      try {
        const raw =
          await apiGet(`${API_COMP}?accion=obtener&id=${encodeURIComponent(id)}`)
            .catch(() => apiGet(`${API_COMP}?accion=obtener&id_competencia=${encodeURIComponent(id)}`));
        if (raw && typeof raw === 'object') {
          item = mapCompetencia(Array.isArray(raw) ? raw[0] : raw);
        }
      } catch (_) {}
    }

    if (!item) {
      t.err('No fue posible cargar la competencia');
      return;
    }

    editingId = item.id;
    editingSnap = {
      program_id: String(item.program_id ?? ''),
      code: String(item.code ?? ''),
      name: String(item.name ?? ''),
      desc: String(item.desc ?? '')
    };

    // Programa seleccionado (valida que exista en options)
    if (selProg) {
      const target = String(item.program_id ?? '');
      const exists = Array.from(selProg.options).some(o => String(o.value) === target);
      selProg.value = exists ? target : '';
    }

    if (inpCode) {
      inpCode.value = item.code || '';
      inpCode.removeAttribute('readonly'); // editable
      inpCode.classList.remove('bg-zinc-50');
    }
    if (inpName) inpName.value = item.name || '';
    if (inpDesc) inpDesc.value = item.desc || '';

    if (titleComp) titleComp.textContent = 'Editar Competencia';

    openModal(true);
  }

  // ===============================
  // GUARDAR (Crear / Actualizar)
  // ===============================
  form?.addEventListener('submit', async (ev) => {
    ev.preventDefault();

    const isEditing  = !!editingId;
    const newCode    = (inpCode?.value || '').trim();
    const payload = {
      id_competencia: isEditing ? String(editingId) : newCode,   // id actual (clave)
      codigo_competencia: newCode,                                // posible nuevo código
      nombre_competencia: (inpName?.value || '').trim(),
      descripcion: (inpDesc?.value || '').trim(),
      id_programa: (selProg?.value || '').trim() || null
    };

    // === Validación: todos los campos vacíos ===
    if (!payload.codigo_competencia && !payload.nombre_competencia && !payload.descripcion && !payload.id_programa) {
      t.warn('Todos los campos son obligatorios');
      return;
    }

    // === Validaciones ===
    if (!payload.nombre_competencia) { t.warn('El nombre es obligatorio'); return; }
    if (!isEditing && !payload.codigo_competencia) { t.warn('El código es obligatorio'); return; }
    if (!isEditing && !payload.id_programa) { t.warn('Seleccione un programa'); return; }
    if (!isEditing && !payload.descripcion) { t.warn('La descripción es obligatoria'); return; }

    // Validación "no cambiaste nada" al EDITAR
    if (isEditing && editingSnap) {
      const changed =
        String(payload.codigo_competencia) !== editingSnap.code ||
        String(payload.id_programa ?? '')  !== editingSnap.program_id ||
        String(payload.nombre_competencia) !== editingSnap.name ||
        String(payload.descripcion)        !== editingSnap.desc;

      if (!changed) { t.info('No has realizado cambios aun'); return; }
    }

    try {
      if (isEditing) {
        // por si el controller usa nuevo_id_competencia
        payload.nuevo_id_competencia = newCode;
        const res = await apiJson(`${API_COMP}?accion=actualizar`, payload);
        if (res?.error) throw new Error(res.error);
        t.ok('Competencia actualizada');
      } else {
        const res = await apiJson(`${API_COMP}?accion=crear`, payload);
        if (res?.error) throw new Error(res.error);
        t.ok('Competencia creada');
      }
      closeModal();
      await loadCompetencias();
      await tryLoadRaeMap();
    } catch (err) {
      console.error('[Competencias] guardar:', err);
      t.err('No fue posible guardar');
    }
  });


// ===============================
// 🔔 ESCUCHAR CAMBIOS DE RAEs (CREADOS/EDITADOS)
// ===============================
window.addEventListener('raes:changed', async (_ev) => {
  // Recarga el mapa de RAEs y repinta la lista respetando los filtros actuales
  await tryLoadRaeMap();
  renderList();
});


// ===============================
// ✅ NUEVO: ESCUCHAR CAMBIOS DE PROGRAMAS (sin recargar la página)
// ===============================
function isModalOpen() {
  return modal && !modal.classList.contains('hidden') && !backdrop?.classList.contains('hidden');
}

window.addEventListener('programs:changed', async (ev) => {
  const type = ev?.detail?.type || '';
  const prog = ev?.detail?.program || {};
  const pid  = String(prog.id_programa ?? prog.id ?? '');

  // 1) Recarga ambos selects (filtro y modal) preservando selección actual
  await loadPrograms({ preserveSelection: true });

  // 2) Si el modal está abierto y estamos creando, preselecciona el nuevo programa
  if (type === 'create' && isModalOpen() && !editingId && pid && selProg) {
    const has = Array.from(selProg.options).some(o => String(o.value) === pid);
    if (has) selProg.value = pid;
  }

  // 3) Re-pinta lista por si cambió el nombre del programa (afecta chips)
  renderList();
});


  // ===============================
  // INIT
  // ===============================
  (async function init(){
    await loadPrograms({ preserveSelection: true });
    await loadCompetencias();
    await tryLoadRaeMap();
  })();

})();
