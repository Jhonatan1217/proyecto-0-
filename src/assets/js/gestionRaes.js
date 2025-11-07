
(function () {
  const section = document.querySelector('section[data-tab="raes"]');
  if (!section) return; // Si no existe la pestaña RAEs, no ejecuta nada

  // ==== Endpoints ====
  const API_RAES = (window.API_RAES || (window.BASE_URL || '') + 'src/controllers/RaeController.php').replace(/\/+$/, '');

  const BASE = (window.BASE_URL || '').replace(/\/+$/, '');
  const ICON_PENCIL = `${BASE}src/assets/img/pencil-line.svg`;

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

  // Título del modal (no tocamos tu HTML; intentamos encontrarlo)
  const titleRae = document.getElementById('titleRae')
                 || modal?.querySelector('[data-title]')
                 || modal?.querySelector('h2, h3, [role="heading"]');

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

  // ==== Helpers / Estado ====
  const q = p => new URLSearchParams(p).toString();
  const isModalOpen = () => modal && !modal.classList.contains('hidden');

  // estado de edición
  let editingRaeId = null;           // si no es null => estamos editando ese id_rae
  let editingSnap  = null;           // {id, prog, comp, desc}

  async function fetchJSON(url, opts) {
    const res = await fetch(url, opts);
    if (!res.ok) throw new Error('HTTP ' + res.status);
    return res.json();
  }

  function escapeHtml(s) {
    return String(s ?? '')
      .replaceAll('&','&amp;').replaceAll('<','&lt;')
      .replaceAll('>','&gt;').replaceAll('"','&quot;')
      .replaceAll("'","&#039;");
  }

  // ==== SweetAlert2 (SOLO TOASTS) ====
  const Toast = (window.Swal ? Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 2300,
    timerProgressBar: true,
    background: '#fff',
    color: '#111',
    didOpen: (t) => {
      t.addEventListener('mouseenter', Swal.stopTimer);
      t.addEventListener('mouseleave', Swal.resumeTimer);
    }
  }) : null);
  const toast = {
    ok:   (m) => Toast && Toast.fire({ icon: 'success', title: m || 'Operación exitosa' }),
    warn: (m) => Toast && Toast.fire({ icon: 'warning', title: m || 'Revisa los datos' }),
    err:  (m) => Toast && Toast.fire({ icon: 'error',   title: m || 'Ocurrió un error' }),
    info: (m) => Toast && Toast.fire({ icon: 'info',    title: m || 'Información' })
  };

  // ==== Cargar Programas para filtros y modal ====
  async function loadPrograms() {
    const data  = await fetchJSON(`${API_RAES}?accion=programas`);
    const progs = Array.isArray(data) ? data : (data.data || []);

    // Filtro de programas
    if (selProgFilter) {
      const current = selProgFilter.value || 'all';
      selProgFilter.innerHTML = `<option value="all">Todos los programas</option>`;
      for (const p of progs) {
        const opt = document.createElement('option');
        opt.value = String(p.id_programa);
        opt.textContent = `${p.id_programa} – ${p.nombre_programa}`;
        selProgFilter.appendChild(opt);
      }
      const exists = Array.from(selProgFilter.options).some(o => o.value === current);
      selProgFilter.value = exists ? current : 'all';
    }

    // Modal: Programas
    if (selProgInForm) {
      const current = selProgInForm.value || '';
      selProgInForm.innerHTML = `<option value="">Seleccione un programa</option>`;
      for (const p of progs) {
        const opt = document.createElement('option');
        opt.value = String(p.id_programa);
        opt.textContent = `${p.id_programa} – ${p.nombre_programa}`;
        selProgInForm.appendChild(opt);
      }
      const exists = Array.from(selProgInForm.options).some(o => o.value === current);
      selProgInForm.value = exists ? current : '';
    }
  }

  // ==== Cargar Competencias por programa (para filtro y modal) ====
  async function loadCompetenciasFor(programId, targetSelect, withNames = false) {
    if (targetSelect === selCompFilter && (!programId || programId === 'all')) {
      targetSelect.innerHTML = `<option value="all">Todas las competencias</option>`;
      return;
    }
    if (!programId || programId === 'all') {
      targetSelect.innerHTML = `<option value="">Seleccione una competencia</option>`;
      return;
    }

    const data  = await fetchJSON(`${API_RAES}?accion=competenciasPorPrograma&${q({id_programa: programId})}`);
    const comps = Array.isArray(data) ? data : (data.data || []);

    const current = targetSelect.value || '';
    targetSelect.innerHTML = targetSelect === selCompFilter
      ? `<option value=\"all\">Todas las competencias</option>`
      : `<option value=\"\">Seleccione una competencia</option>`;

    for (const c of comps) {
      const opt = document.createElement('option');
      opt.value = String(c.id_competencia);
      opt.textContent = (targetSelect === selCompFilter || !withNames)
        ? String(c.id_competencia)
        : `${c.id_competencia} – ${c.nombre_competencia}`;
      targetSelect.appendChild(opt);
    }
    const exists = Array.from(targetSelect.options).some(o => o.value === current);
    if (exists) targetSelect.value = current;
  }

  // Badge de estado para cada RAE
  function statusChipRAE(estado) {
    const on = Number(estado) === 1;
    return on
      ? '<span class="text-xs px-2 py-1 rounded-full" style="background:#eaf7e6;border:1px solid rgba(57,169,0,.22);color:#39a900">Activo</span>'
      : '<span class="text-xs px-2 py-1 rounded-full" style="background:#f3f4f6;border:1px solid #e5e7eb;color:#6b7280">Inactivo</span>';
  }

  // Switch accesible para activar/inhabilitar RAEs
  function renderSwitchRae(id, estado) {
    const on = Number(estado) === 1;
    return `
      <label class="switch cursor-pointer" data-switch-rae="${escapeHtml(id)}" title="${on ? 'Activo' : 'Inactivo'}" aria-label="Cambiar estado">
        <input type="checkbox" ${on ? 'checked' : ''} />
        <span class="dot"></span>
      </label>
    `;
  }

  // Pinta el aspecto del switch según su estado (color de fondo)
  function paintSwitchRae(el){
    try {
      const input = el.querySelector('input');
      const setBg = () => { el.style.background = input.checked ? '#39a900' : '#e5e7eb'; };
      setBg();
      input.addEventListener('change', setBg);
    } catch {}
  }

  // Obtiene el primer valor disponible entre varias claves posibles
  function pick(obj, arr, fallback=''){
    for (const k of arr){ if (obj && obj[k] != null && obj[k] !== '') return obj[k]; }
    return fallback;
  }

  // ==== Listar RAEs con filtros ====
  async function loadRaes() {
    const id_programa    = selProgFilter?.value || 'all';
    const id_competencia = selCompFilter?.value || 'all';

    const url  = `${API_RAES}?accion=listar&${q({ id_programa, id_competencia })}`;
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
      const idRae   = pick(r, ['id_rae','codigo','codigo_rae','idRAE'], '');
      const estado  = pick(r, ['estado'], 1);
      const titulo  = pick(r, ['descripcion','nombre','titulo','detalle'], '(Sin descripción)');
      const compId  = pick(r, ['id_competencia','competencia_id','idCompetencia'], '');
      const compNom = pick(r, ['nombre_competencia','competencia_nombre','nombreCompetencia'], '');
      const progId  = pick(r, ['id_programa','programa_id','idPrograma'], '');
      const progNom = pick(r, ['nombre_programa','programa','sigla_programa'], '');

      const card = document.createElement('div');
      card.className = 'rounded-2xl ring-1 ring-zinc-200 shadow-sm bg-white overflow-hidden';

      card.innerHTML = `
        <div class="p-4 md:p-5">
          <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
              <div class="flex items-center gap-3 flex-wrap">
                <span class="inline-flex items-center whitespace-nowrap rounded-full bg-zinc-100 ring-1 ring-zinc-300 px-3 py-1 text-xs font-semibold text-zinc-700">
                  ${escapeHtml(idRae)}
                </span>
                <h3 class="text-[15px] sm:text-base md:text-lg font-bold text-zinc-900 leading-snug">
                  ${escapeHtml(titulo)}
                </h3>
              </div>

              <p class="mt-2 text-[13px] sm:text-sm text-zinc-600 flex items-center gap-2 flex-wrap">
                <span class="whitespace-nowrap">
                  <span class="text-zinc-500">Competencia:</span> <b>${escapeHtml(String(compId || '—'))}</b>
                </span>
                ${compNom ? `<span class="text-zinc-300">•</span><span class="truncate">${escapeHtml(compNom)}</span>` : ''}
                ${progNom ? `
                  <span class="text-zinc-300">•</span>
                  <span class="inline-flex items-center rounded-full bg-zinc-100 text-zinc-700 px-2.5 py-1 text-[11px] font-medium">${escapeHtml(progNom)}</span>
                ` : ''}
                <span class="text-zinc-300">•</span>
                ${statusChipRAE(estado)}
              </p>
            </div>

            <div class="shrink-0 flex items-center gap-3">
              <button class="btn-edit-rae inline-flex items-center justify-center p-2 text-zinc-600 hover:text-zinc-900"
                      data-id="${escapeHtml(idRae)}"
                      data-prog="${escapeHtml(String(progId))}"
                      data-comp="${escapeHtml(String(compId))}"
                      data-desc="${escapeHtml(titulo)}"
                      title="Editar">
                <img src="${ICON_PENCIL}" class="w-5 h-5" alt="Editar" />
              </button>
              ${renderSwitchRae(idRae, estado)}
            </div>
          </div>
        </div>
      `;
      list.appendChild(card);
    }

    // Switch estado
    list.querySelectorAll('[data-switch-rae]').forEach(sw => {
      paintSwitchRae(sw);
      const input = sw.querySelector('input');
      const id = sw.getAttribute('data-switch-rae');
      input.addEventListener('change', async () => {
        const next = input.checked ? 1 : 0;
        try {
          const res = await fetchJSON(`${API_RAES}?accion=inhabilitar&${q({ id_rae: id, estado: next })}`);
          if (res?.error) throw new Error(res.error);
          await loadRaes();
          window.dispatchEvent(new CustomEvent('raes:changed', { detail: { rae: { id_rae: id }}}));
          toast.ok('Estado actualizado');
        } catch (err) {
          input.checked = !input.checked;
          paintSwitchRae(sw);
          toast.err('No se pudo cambiar el estado');
          console.error('[RAEs] cambiar estado:', err);
        }
      });
    });

    // Editar -> abre modal y precarga
    list.querySelectorAll('.btn-edit-rae').forEach(b => {
      b.addEventListener('click', async (ev) => {
        const btn = ev.currentTarget;
        const idRae  = btn.getAttribute('data-id') || '';
        const pid    = btn.getAttribute('data-prog') || '';
        const cid    = btn.getAttribute('data-comp') || '';
        const desc   = btn.getAttribute('data-desc') || '';

        editingRaeId = idRae;
        editingSnap = { id:idRae, prog:pid, comp:cid, desc };

        if (form) form.reset();
        backdrop.classList.remove('hidden');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        if (titleRae) titleRae.textContent = 'Editar RAE';

        // Cargar programas y seleccionar
        await loadPrograms();
        if (selProgInForm) {
          const hasProg = Array.from(selProgInForm.options).some(o => o.value === String(pid));
          selProgInForm.value = hasProg ? String(pid) : '';
        }

        // Cargar competencias del programa y seleccionar
        if (selProgInForm && selProgInForm.value) {
          await loadCompetenciasFor(selProgInForm.value, selComp, true);
        } else {
          selComp.innerHTML = `<option value="">Seleccione una competencia</option>`;
        }
        if (selComp) {
          const hasComp = Array.from(selComp.options).some(o => o.value === String(cid));
          selComp.value = hasComp ? String(cid) : '';
        }

        if (inCode) inCode.value = idRae || '';
        if (inDesc) inDesc.value = desc || '';
      });
    });
  }

  // ==== Abrir / Cerrar modal ====
  function openModal() {
    if (form) form.reset();
    editingRaeId = null; editingSnap = null;
    if (titleRae) titleRae.textContent = 'Nuevo RAE';
    if (inCode) inCode.value = '';
    if (selComp) selComp.innerHTML = `<option value=\"\">Seleccione una competencia</option>`;
    backdrop.classList.remove('hidden');
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    window.lucide?.createIcons();
  }
  function closeModal() {
    backdrop.classList.add('hidden');
    modal.classList.add('hidden');
    document.body.style.overflow = '';
    editingRaeId = null; editingSnap = null;
  }

  // ==== Eventos UI ====
  btnNew    && btnNew.addEventListener('click', openModal);
  btnClose  && btnClose.addEventListener('click', closeModal);
  btnCancel && btnCancel.addEventListener('click', closeModal);
  backdrop?.addEventListener('click', closeModal);
  modal?.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal(); });

  // Filtros
  selProgFilter?.addEventListener('change', async () => {
    await loadCompetenciasFor(selProgFilter.value, selCompFilter, false);
    await loadRaes();
  });
  selCompFilter?.addEventListener('change', loadRaes);

  // Modal: cambios
  selProgInForm?.addEventListener('change', async () => {
    const pid = selProgInForm.value;
    if (!editingRaeId && inCode) inCode.value = ''; // solo limpiar en modo crear
    if (pid) {
      await loadCompetenciasFor(pid, selComp, true);
    } else {
      selComp.innerHTML = `<option value=\"\">Seleccione una competencia</option>`;
    }
  });

  selComp?.addEventListener('change', () => {
    const cid = selComp.value || '';
    if (!editingRaeId && inCode) {
      inCode.value = cid ? `${cid}-` : '';
    }
  });

  // Submit: crear o actualizar RAE (validaciones y toasts)
  form?.addEventListener('submit', async (e) => {
    e.preventDefault();

    const nuevo_id_rae   = (inCode?.value || '').trim();
    const descripcion    = (inDesc?.value || '').trim();
    const id_competencia = (selComp?.value || '').trim();
    const id_programa    = (selProgInForm?.value || '').trim();

    try {
      if (editingRaeId) {
        // ====== VALIDACIONES EDITAR (sólo toast) ======
        const final_id   = nuevo_id_rae || (editingSnap?.id || '');
        const final_comp = id_competencia || (editingSnap?.comp || '');
        const final_desc = descripcion || (editingSnap?.desc || '');

        // 1) Aviso si no hay cambios
        const sinCambios =
          final_id === (editingSnap?.id || '') &&
          final_comp === (editingSnap?.comp || '') &&
          final_desc === (editingSnap?.desc || '');
        if (sinCambios) { toast.warn('No has hecho cambios aún'); return; }

        // 2) Reglas mínimas
        if (!final_desc)  { toast.warn('La descripción es obligatoria'); return; }
        if (!final_id)    { toast.warn('El código no puede quedar vacío'); return; }
        if (!final_comp)  { toast.warn('Selecciona una competencia'); return; }

        // Intento 1: querystring
        let res = await fetchJSON(`${API_RAES}?accion=actualizar&${q({
          id_rae: editingRaeId,
          nuevo_id_rae: final_id,
          descripcion: final_desc,
          id_competencia: final_comp
        })}`).catch(() => null);

        // Intento 2: POST JSON
        if (!res) {
          res = await fetchJSON(`${API_RAES}?accion=actualizar`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              id_rae: editingRaeId,
              nuevo_id_rae: final_id,
              descripcion: final_desc,
              id_competencia: final_comp
            })
          });
        }

        if (res?.error) throw new Error(res.error);

        closeModal();
        await loadRaes();
        window.dispatchEvent(new CustomEvent('raes:changed', {
          detail: { rae: { id_rae: final_id, id_competencia: final_comp, descripcion: final_desc } }
        }));
        toast.ok('RAE actualizado');
      } else {
        // ====== VALIDACIONES CREAR (sólo toast) ======
        // 1) Si todo está vacío -> toast global
        if (!id_programa && !id_competencia && !nuevo_id_rae && !descripcion) {
          toast.warn('Todos los campos son obligatorios');
          return;
        }
        // 2) Restantes
        if (!id_programa)    { toast.warn('Programa requerido'); return; }
        if (!id_competencia) { toast.warn('Competencia requerida'); return; }
        if (!nuevo_id_rae)   { toast.warn('Código requerido'); return; }
        if (!descripcion)    { toast.warn('Descripción requerida'); return; }

        const url = `${API_RAES}?accion=crear&${q({ id_rae: nuevo_id_rae, descripcion, id_competencia })}`;
        const res = await fetchJSON(url);
        if (res?.error) throw new Error(res.error);

        closeModal();
        await loadRaes();
        window.dispatchEvent(new CustomEvent('raes:changed', {
          detail: { rae: { id_rae: nuevo_id_rae, id_competencia, descripcion } }
        }));
        toast.ok('RAE creado');
      }
    } catch (err) {
      console.error('[RAEs] guardar/actualizar:', err);
      toast.err('No fue posible guardar los cambios del RAE');
    }
  });

  // ============================================================
  // 🔔 ACTUALIZACIÓN EN VIVO: escuchar cambios de Programas y Competencias
  // ============================================================
  window.addEventListener('programs:changed', async (ev) => {
    const pid = ev?.detail?.program?.id_programa ? String(ev.detail.program.id_programa) : null;
    await loadPrograms();
    if (selProgFilter && (selProgFilter.value === pid || (selProgFilter.value === 'all' && pid))) {
      await loadCompetenciasFor(selProgFilter.value, selCompFilter, false);
    }
    if (isModalOpen() && selProgInForm && selProgInForm.value) {
      await loadCompetenciasFor(selProgInForm.value, selComp, true);
    }
    await loadRaes();
  });

  window.addEventListener('competencies:changed', async (ev) => {
    const cid = ev?.detail?.competency?.id_competencia ? String(ev.detail.competency.id_competencia) : null;
    const pid = ev?.detail?.competency?.id_programa   ? String(ev.detail.competency.id_programa)   : null;

    const mustRefreshFilter =
      !!selProgFilter &&
      (selProgFilter.value === 'all' || (pid && selProgFilter.value === pid));

    if (mustRefreshFilter) {
      await loadCompetenciasFor(selProgFilter.value, selCompFilter, false);
    }
    if (isModalOpen() && selProgInForm && pid && selProgInForm.value === pid) {
      await loadCompetenciasFor(pid, selComp, true);
      if (cid && selComp) {
        const has = Array.from(selComp.options).some(o => o.value === cid);
        if (has && !editingRaeId && !inCode.value) inCode.value = `${cid}-`;
      }
    }
    await loadRaes();
  });

  // ==== Init ====
  (async function init(){
    await loadPrograms();
    await loadCompetenciasFor('all', selCompFilter, false);
    await loadRaes();
  })();
})();