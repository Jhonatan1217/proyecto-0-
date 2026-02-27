// src/assets/js/gestionRaes.js
(function () {
  const section = document.querySelector('section[data-tab="raes"]');
  if (!section) return; // Si no existe la pestaña RAEs, no ejecuta nada

  // ==== Endpoints ====
  const API_RAES = (window.API_RAES || (window.BASE_URL || '') + 'src/controllers/RaeController.php').replace(/\/+$/, '');

  const BASE = (window.BASE_URL || '').replace(/\/+$/, '');
  const ICON_PENCIL = `${BASE}src/assets/img/pencil-line.svg`;

  // <<< NUEVO >>> bandera para (des)activar autocompletado de código RAE
  const AUTOCOMPLETE_RAE = false;

  // ==== Selectores filtros/listado ====
  const selProgFilter  = section.querySelector('#raeProgramFilter');
  const selCompFilter  = section.querySelector('#raeCompetencyFilter');
  const list           = section.querySelector('#raesList');
  const emptyBox       = section.querySelector('#raesEmpty');

  // <<< NUEVO >>> plantillas para estados vacíos
  const EMPTY_TEMPLATE_FIRST = `
    <div class="flex flex-col items-center justify-center py-12 px-4 text-center">
      <p class="text-sm text-zinc-500 max-w-md mb-4">
        No hay RAEs registrados 
      </p>
       <button id="btnFirstCompetency"
                      class="flex items-center gap-2  bg-[#00324d] text-white px-4 py-2 rounded-xl font-medium text-sm">
                <img src="src/assets/img/plus.svg" class="w-4 h-4" alt="simbolo de mas" />
                Crear Primera RAE
              </button>
    </div>
  `;

  const EMPTY_TEMPLATE_FILTER = `
    <div class="flex items-center justify-center py-12 px-4 text-center">
      <p class="text-sm md:text-base text-zinc-500">
        No hay RAE que coincidan con los filtros seleccionados.
      </p>
    </div>
  `;

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
  const selProgInForm = document.getElementById('rae_program');

  // Título del modal (no tocamos tu HTML; intentamos encontrarlo)
  const titleRae = document.getElementById('titleRae')
                 || modal?.querySelector('[data-title]')
                 || modal?.querySelector('h2, h3, [role="heading"]');

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

  // ================================
  // CASO 1: Es el filtro y no hay programa
  // ================================
  if (targetSelect === selCompFilter && (!programId || programId === 'all')) {
    targetSelect.innerHTML = `<option value="all">Todas las competencias</option>`;
    targetSelect.disabled = false; // el filtro nunca se bloquea
    return;
  }

  // ================================
  // CASO 2: No hay programa seleccionado (modal)
  // ================================
  if (!programId || programId === 'all') {
    targetSelect.innerHTML = `<option value="">Seleccione una competencia</option>`;
    targetSelect.disabled = true; // 🔴 se bloquea hasta que elijan programa
    return;
  }

  try {
    const data  = await fetchJSON(`${API_RAES}?accion=competenciasPorPrograma&${q({ id_programa: programId })}`);
    const comps = Array.isArray(data) ? data : (data.data || []);

    const current = targetSelect.value || '';

    targetSelect.innerHTML = targetSelect === selCompFilter
      ? `<option value="all">Todas las competencias</option>`
      : `<option value="">Seleccione una competencia</option>`;

    for (const c of comps) {
      const opt = document.createElement('option');
      opt.value = String(c.id_competencia);
      opt.textContent = (targetSelect === selCompFilter || !withNames)
        ? String(c.id_competencia)
        : `${c.id_competencia} – ${c.nombre_competencia}`;
      targetSelect.appendChild(opt);
    }

    // HABILITAR porque ya cargó competencias
    targetSelect.disabled = false;

    // Mantener valor si aún existe
    const exists = Array.from(targetSelect.options).some(o => o.value === current);
    if (exists) targetSelect.value = current;

  } catch (err) {
    console.error('[RAEs] Error cargando competencias:', err);
    targetSelect.innerHTML = `<option value="">Error cargando competencias</option>`;
    targetSelect.disabled = true;
  }
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

    list.innerHTML = '';
    const data = Array.isArray(rows) ? rows : (rows.data || []);

    // <<< NUEVO >>> manejo de estados vacíos
    if (!data.length) {
      if (!emptyBox) return;

      // comprobamos si existen RAEs sin filtros (para diferenciar vacío total vs sin coincidencias)
      let anyRaes = false;
      try {
        const allRes = await fetchJSON(`${API_RAES}?accion=listar&${q({ id_programa: 'all', id_competencia: 'all' })}`);
        const allData = Array.isArray(allRes) ? allRes : (allRes.data || []);
        anyRaes = allData.length > 0;
      } catch (e) {
        console.error('[RAEs] comprobar RAEs existentes:', e);
      }

      emptyBox.classList.remove('hidden');

      if (!anyRaes) {
        // No hay NINGÚN RAE en la base → mostrar recuadro para crear el primero
        emptyBox.innerHTML = EMPTY_TEMPLATE_FIRST;

        const btnCreate = emptyBox.querySelector('[data-create-first-rae]');
        if (btnCreate && !btnCreate.dataset.bound) {
          btnCreate.dataset.bound = '1';
          btnCreate.addEventListener('click', () => {
            // reutilizamos el mismo flujo del botón "+ Nuevo RAE"
            openModal();
          });
        }
      } else {
        // Sí hay RAEs, pero los filtros actuales no devuelven resultados
        emptyBox.innerHTML = EMPTY_TEMPLATE_FILTER;
      }

      return;
    }

    // Hay datos → ocultar recuadro vacío y renderizar tarjetas
    if (emptyBox) emptyBox.classList.add('hidden');

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

  // El panel "card" del modal (ajusta el selector si tu HTML es distinto)
  const modalPanel = modal?.querySelector('[data-panel], [role="dialog"], .panel, .box')
                  || modal?.firstElementChild || modal;

  // Reinicia y aplica una clase de animación
  function play(el, cls, remove = []) {
    if (!el) return;
    // quita clases anteriores para reiniciar la animación
    ['animate-modal','animate-backdrop','animate-modal-out','animate-backdrop-out', ...remove].forEach(c => el.classList.remove(c));
    // "reflow" para reiniciar animación
    void el.offsetWidth;
    el.classList.add(cls);
  }


  // ==== Abrir / Cerrar modal ====
  function openModal() {
    if (form) form.reset();
    editingRaeId = null; editingSnap = null;
    if (titleRae) titleRae.textContent = 'Nuevo RAE';
    if (inCode) inCode.value = '';
    if (selComp) selComp.innerHTML = `<option value="">Seleccione una competencia</option>`;

    // Mostrar
    backdrop.classList.remove('hidden');
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    // Animaciones de ENTRADA
    play(backdrop,  'animate-backdrop');
    play(modalPanel,'animate-modal');

    window.lucide?.createIcons?.();
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
    if (!editingRaeId && inCode) inCode.value = ''; // solo limpiar en modo crear (se mantiene)
    if (pid) {
      await loadCompetenciasFor(pid, selComp, true);
    } else {
      selComp.innerHTML = `<option value=\"\">Seleccione una competencia</option>`;
    }
  });

  // <<< MODIFICADO >>> ya no autocompleta el código al cambiar la competencia
  selComp?.addEventListener('change', () => {
    const cid = selComp.value || '';
    if (!editingRaeId && inCode && AUTOCOMPLETE_RAE) {
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
      // <<< MODIFICADO >>> ya no autocompleta si llega una nueva competencia
      if (cid && selComp && AUTOCOMPLETE_RAE) {
        const has = Array.from(selComp.options).some(o => o.value === cid);
        if (has && !editingRaeId && !inCode.value) inCode.value = `${cid}-`;
      }
    }
    await loadRaes();
  });

  // 🔁 NUEVO: cuando se suba el Excel, recargar Programas + Competencias (filtros) + RAEs
  window.addEventListener('excel-subido-ok', async () => {
    try {
      // recarga lista de programas para filtros y modal
      await loadPrograms();

      // actualiza las competencias según el programa seleccionado en el filtro
      if (selProgFilter && selCompFilter) {
        await loadCompetenciasFor(selProgFilter.value || 'all', selCompFilter, false);
      }

      // recargar la lista de RAEs con los datos recién importados
      await loadRaes();
    } catch (e) {
      console.error('[RAEs] recarga tras Excel:', e);
      toast.err('No fue posible actualizar los RAEs después de la carga Excel');
    }
  });

  // ==== Init ====
  (async function init(){
    await loadPrograms();
    await loadCompetenciasFor('all', selCompFilter, false);
    await loadRaes();
  })();
})();
