(function initWhenReady(){
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }

  function init(){
    // ====== Feature flag: desactivar "Programas" sin borrar base ======
    const REMOVE_PROGRAMS = true;

    // ====== Datos (desde window.* si existen; si no, arrays vacíos) ======
    const PROGRAMS = Array.isArray(window.PROGRAMS) ? window.PROGRAMS : [];
    const COMPETENCIES = Array.isArray(window.COMPETENCIES) ? window.COMPETENCIES : [];
    const RAES = Array.isArray(window.RAES) ? window.RAES : [];

    // ====== Util ======
    const e = s => String(s ?? '')
      .replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;')
      .replaceAll('"','&quot;').replaceAll("'","&#039;");

    const emptyHero = (labelPlural, btnId, btnText) => `
      <div class="py-16 text-center">
        <p class="text-zinc-500 text-lg">No hay ${labelPlural.toLowerCase()} registrados</p>
        <button id="${btnId}" class="mt-6 inline-flex items-center gap-2 rounded-xl bg-zinc-900 text-white px-4 py-2.5 text-sm font-medium hover:bg-black">
          <i data-lucide="plus" class="w-4 h-4"></i> ${btnText}
        </button>
      </div>
    `;

    const editingState = { type: null, id: null };
    function clearEditingState(){ editingState.type = null; editingState.id = null; }

    const tabButtons = document.querySelectorAll('[data-tab-btn]');
    const tabPanes = document.querySelectorAll('.tab-pane');

    function activateTab(key){
      // Si Programas está deshabilitado, redirigimos a upload
      if (REMOVE_PROGRAMS && key === 'programs') key = 'upload';
      tabPanes.forEach(p => p.classList.toggle('hidden', p.dataset.tab !== key));
      tabButtons.forEach(b=>{
        const active = b.dataset.tabBtn === key;
        b.classList.toggle('tabs-pill-active', active);
        b.setAttribute('aria-selected', active ? 'true' : 'false');
        b.classList.toggle('text-zinc-900', active);
        b.classList.toggle('text-zinc-700', !active);
      });
      if(key==='programs') renderPrograms();
      if(key==='competencies') renderCompetencies();
      if(key==='raes') { refreshRaeFilters(); renderRaes(); }
      window.lucide?.createIcons();
    }
    tabButtons.forEach(b=>b.addEventListener('click',()=>activateTab(b.dataset.tabBtn)));

    // ====== Ocultar todo lo de Programas si está deshabilitado
    if (REMOVE_PROGRAMS) {
      document.body.classList.add('no-programs');
      // Evitar render de Programas
      window.renderPrograms = function(){ /* no-op: programas desactivado */ };
      // Botón de pestaña Programas no interactivo
      const btnPrograms = document.querySelector('[data-tab-btn="programs"]');
      if (btnPrograms) {
        btnPrograms.setAttribute('tabindex', '-1');
        btnPrograms.setAttribute('aria-hidden', 'true');
      }
    }

    // Tab inicial
    activateTab('upload');

    // ====== Programs (mantengo funciones originales por compatibilidad) ======
    function renderPrograms(){
      const grid = document.getElementById('programsGrid');
      const empty = document.getElementById('programsEmpty');
      if(!grid || !empty) return;
      grid.innerHTML='';
      PROGRAMS.forEach(p=>{
        const activeBadge = p.active
          ? '<span class="inline-flex items-center rounded-full bg-green-100 text-green-700 px-2 py-0.5 text-xs font-medium">Activo</span>'
          : '<span class="inline-flex items-center rounded-full bg-zinc-100 text-zinc-500 px-2 py-0.5 text-xs font-medium">Inactivo</span>';
        const card = document.createElement('div');
        card.className = "rounded-2xl ring-1 ring-zinc-200 shadow-sm overflow-hidden hover:shadow transition program-card";
        card.setAttribute('data-program-id', p.id);
        card.setAttribute('role','button');
        card.setAttribute('tabindex','0');
        card.innerHTML = `
          <div class="px-6 pt-6 pb-2">
            <div class="flex items-start justify-between gap-4">
              <div class="flex-1">
                <h3 class="text-lg font-semibold leading-snug">${e(p.name)}</h3>
                <p class="mt-1 text-sm text-zinc-500">Código: ${e(p.code)}</p>
              </div>
              <div class="flex items-center gap-2">
                <button class="p-2 rounded-lg hover:bg-zinc-100" title="Editar" data-edit-program="${p.id}">
                   <img src="src/assets/img/pencil-line.svg" alt="Icono añadir" class="w-4 h-4">
                </button>
                <label class="switch" data-stop-prop="1">
                  <input type="checkbox" ${p.active?'checked':''} data-program="${p.id}">
                  <span class="dot"></span>
                  <span class="track absolute inset-0 rounded-full"></span>
                </label>
              </div>
            </div>
          </div>
          <div class="px-6 pb-6">
            <p class="text-sm text-zinc-600">${e(p.description || 'Sin descripción')}</p>
            ${p.duration_hours? `<p class="mt-2 text-sm font-medium">Duración: ${p.duration_hours} horas</p>`:''}
            <div class="mt-2">${activeBadge}</div>
          </div>
        `;
        grid.appendChild(card);
      });

      grid.querySelectorAll('[data-program]').forEach(chk=>{
        chk.addEventListener('change',ev=>{
          ev.stopPropagation();
          const id = Number(ev.target.dataset.program);
          const idx = PROGRAMS.findIndex(x=>x.id===id);
          if(idx>-1){ PROGRAMS[idx].active = !PROGRAMS[idx].active; renderPrograms(); window.lucide?.createIcons(); }
        });
      });

      grid.querySelectorAll('.program-card').forEach(card=>{
        card.addEventListener('click',(ev)=>{
          const isControl = ev.target.closest('[data-stop-prop],label.switch,input,button');
          if(isControl) return;
          const id = Number(card.getAttribute('data-program-id'));
          openProgramForEdit(id);
        });
      });
      grid.querySelectorAll('[data-edit-program]').forEach(btn=>{
        btn.addEventListener('click', (ev)=>{
          ev.stopPropagation();
          const id = Number(btn.getAttribute('data-edit-program'));
          openProgramForEdit(id);
        });
      });

      const newBtn = document.querySelector('section[data-tab="programs"] button i[data-lucide="plus"]')?.parentElement;
      if (newBtn && !newBtn.dataset.bound) {
        newBtn.dataset.bound = "1";
        newBtn.addEventListener('click', ()=>{ clearEditingState(); openProgramModal(); setProgramSubmitMode('create'); });
      }

      if (PROGRAMS.length === 0) {
        grid.classList.add('hidden');
        empty.classList.remove('hidden');
        empty.innerHTML = emptyHero('programas','btnFirstProgram','Crear Primer Programa');
      } else {
        grid.classList.remove('hidden');
        empty.classList.add('hidden');
      }
      window.lucide?.createIcons();
    }

    // ====== Competencies ======
    function renderCompetencies(){
      const list = document.getElementById('competenciesList');
      const empty = document.getElementById('competenciesEmpty');
      if(!list || !empty) return;

      // Si Programas está deshabilitado, ignoramos filtro por programa
      let filtered = COMPETENCIES;
      const progFilterEl = document.getElementById('competencyProgramFilter');
      if (!REMOVE_PROGRAMS && progFilterEl) {
        const filter = progFilterEl.value;
        filtered = filter==='all' ? COMPETENCIES : COMPETENCIES.filter(c=>String(c.program_id)===String(filter));
      }

      list.innerHTML='';
      filtered.forEach(c=>{
        const hasRaes = Array.isArray(c.raes) && c.raes.length>0;
        const badgeActive = c.active
          ? '<span class="inline-flex items-center rounded-full bg-green-100 text-green-700 px-2 py-0.5 text-xs font-medium">Activo</span>'
          : '<span class="inline-flex items-center rounded-full bg-zinc-100 text-zinc-500 px-2 py-0.5 text-xs font-medium">Inactivo</span>';

        const card = document.createElement('div');
        card.className = "rounded-2xl ring-1 ring-zinc-200 shadow-sm overflow-hidden competency-card";
        card.setAttribute('data-competency-id', c.id);
        card.setAttribute('role','button');
        card.setAttribute('tabindex','0');

        card.innerHTML = `
          <div class="px-6 pt-6 pb-2">
            <div class="flex items-start justify-between gap-4">
              <div class="flex-1">
                <div class="flex items-center gap-2">
                  ${hasRaes ? `<button class="p-1.5 rounded-md hover:bg-zinc-100" data-expand="${c.id}" aria-expanded="false" title="Mostrar RAE"><i data-lucide="chevron-right" class="w-4 h-4"></i></button>` : ''}
                  <h3 class="text-lg font-semibold">${e(c.name)}</h3>
                </div>
                <div class="mt-2 flex flex-wrap items-center gap-2 text-sm">
                  <span class="text-zinc-700">Código: ${e(c.code)}</span>
                  <span class="text-zinc-400">•</span>
                  <span class="inline-flex items-center rounded-full bg-zinc-100 text-zinc-700 px-2 py-0.5 text-xs font-medium" data-role="program-chip">${e(c.program_name || 'Sin programa')}</span>
                  <span class="text-zinc-400">•</span>
                  ${badgeActive}
                </div>
              </div>
              <div class="flex items-center gap-2">
                <button class="p-2 rounded-lg hover:bg-zinc-100" title="Editar" data-edit-competency="${c.id}"><img src="src/assets/img/pencil-line.svg" alt="Icono añadir" class="w-4 h-4"></button>
                <label class="switch" data-stop-prop="1">
                  <input type="checkbox" ${c.active?'checked':''} data-competency="${c.id}">
                  <span class="dot"></span>
                  <span class="track absolute inset-0 rounded-full"></span>
                </label>
              </div>
            </div>
          </div>
          <div class="px-6 pb-6">
            <p class="text-sm text-zinc-600">${e(c.description || 'Sin descripción')}</p>
            ${hasRaes ? `
              <div class="mt-3"><span class="inline-flex items-center rounded-full border border-zinc-300 text-zinc-700 px-2 py-0.5 text-xs font-medium">${c.raes.length} RAE${c.raes.length!==1?'s':''}</span></div>
              <div class="mt-4 space-y-2 border-t border-zinc-200 pt-4 hidden" data-rae-list="${c.id}">
                <h4 class="text-sm font-semibold">Resultados de Aprendizaje Esperados (RAE)</h4>
                <div class="space-y-2">
                  ${c.raes.map(rae=>`
                    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 rae-mini" data-mini-rae="${rae.id}">
                      <div class="flex items-start gap-2">
                        <span class="inline-flex items-center rounded-full bg-zinc-100 text-zinc-700 px-2 py-0.5 text-xs font-medium">${e(rae.code)}</span>
                        <p class="text-sm">${e(rae.description)}</p>
                      </div>
                    </div>
                  `).join('')}
                </div>
              </div>` : '' }
          </div>
        `;
        list.appendChild(card);
      });

      list.querySelectorAll('[data-competency]').forEach(chk=>{
        chk.addEventListener('change',ev=>{
          ev.stopPropagation();
          const id = Number(ev.target.dataset.competency);
          const i = COMPETENCIES.findIndex(x=>x.id===id);
          if(i>-1){ COMPETENCIES[i].active = !COMPETENCIES[i].active; renderCompetencies(); window.lucide?.createIcons(); }
        });
      });

      list.querySelectorAll('[data-expand]').forEach(btn=>{
        btn.addEventListener('click',(ev)=>{
          ev.stopPropagation();
          const id = btn.dataset.expand;
          const box = list.querySelector(`[data-rae-list="${id}"]`);
          if(!box) return;
          const isHidden = box.classList.contains('hidden');
          box.classList.toggle('hidden', !isHidden);
          btn.setAttribute('aria-expanded', String(isHidden));
          btn.innerHTML = `<i data-lucide="${isHidden?'chevron-down':'chevron-right'}" class="w-4 h-4"></i>`;
          window.lucide?.createIcons();
        });
      });

      list.querySelectorAll('.competency-card').forEach(card=>{
        card.addEventListener('click',(ev)=>{
          const isControl = ev.target.closest('[data-stop-prop],label.switch,input,button,[data-expand]');
          if(isControl) return;
          const id = Number(card.getAttribute('data-competency-id'));
          openCompetencyForEdit(id);
        });
      });
      list.querySelectorAll('[data-edit-competency]').forEach(btn=>{
        btn.addEventListener('click',(ev)=>{
          ev.stopPropagation();
          const id = Number(btn.getAttribute('data-edit-competency'));
          openCompetencyForEdit(id);
        });
      });

      // Estado vacío
      if (COMPETENCIES.length === 0) {
        empty.classList.remove('hidden');
        empty.innerHTML = emptyHero('competencias','btnFirstCompetency','Crear Primera Competencia');
        document.getElementById('btnFirstCompetency')?.addEventListener('click', ()=>{ clearEditingState(); openCompetencyModal(); setCompetencySubmitMode('create'); });
      } else {
        empty.classList.add('hidden');
      }

      window.lucide?.createIcons();

      const btnCompetency = document.querySelector('section[data-tab="competencies"] button i[data-lucide="plus"]')?.parentElement;
      if (btnCompetency && !btnCompetency.dataset.bound) {
        btnCompetency.dataset.bound = "1";
        btnCompetency.addEventListener('click', ()=>{ clearEditingState(); openCompetencyModal(); setCompetencySubmitMode('create'); });
      }
    }

    // Si existe el select, mantenemos compat; si no, no pasa nada
    document.getElementById('competencyProgramFilter')?.addEventListener('change', renderCompetencies);

    // ====== RAEs ======
    function refreshRaeFilters(){
      const pf = document.getElementById('raeProgramFilter');
      const cf = document.getElementById('raeCompetencyFilter');
      if(!pf || !cf) return;

      const programs = Array.from(new Set(RAES.map(r=>r.program_name).filter(Boolean)));
      const comps = Array.from(new Set(RAES.map(r=>r.competency_code).filter(Boolean)));

      const keepFirst = (sel, placeholder) => {
        const current = sel.value;
        sel.innerHTML = '';
        const optAll = document.createElement('option');
        optAll.value = 'all'; optAll.textContent = placeholder;
        sel.appendChild(optAll);
        return current;
      };

      const curP = keepFirst(pf,'Todos los programas');
      programs.forEach(pn=>{
        const o = document.createElement('option'); o.value = pn; o.textContent = pn; pf.appendChild(o);
      });
      pf.value = programs.includes(curP) ? curP : 'all';

      const curC = keepFirst(cf,'Todas las competencias');
      comps.forEach(cc=>{
        const o = document.createElement('option'); o.value = cc; o.textContent = cc; cf.appendChild(o);
      });
      cf.value = comps.includes(curC) ? curC : 'all';
    }

    function renderRaes(){
      const list = document.getElementById('raesList');
      const empty = document.getElementById('raesEmpty');
      if(!list || !empty) return;

      const pf = document.getElementById('raeProgramFilter')?.value ?? 'all';
      const cf = document.getElementById('raeCompetencyFilter')?.value ?? 'all';

      const filtered = RAES.filter(r=>{
        const okP = (pf==='all') || (r.program_name===pf);
        const okC = (cf==='all') || (r.competency_code===cf);
        return okP && okC;
      });

      list.innerHTML='';
      filtered.forEach(r=>{
        const badgeActive = r.active
          ? '<span class="inline-flex items-center rounded-full bg-green-100 text-green-700 px-2 py-0.5 text-xs font-medium">Activo</span>'
          : '<span class="inline-flex items-center rounded-full bg-zinc-100 text-zinc-500 px-2 py-0.5 text-xs font-medium">Inactivo</span>';

        const card = document.createElement('div');
        card.className = "rounded-2xl ring-1 ring-zinc-200 shadow-sm px-6 py-4 rae-card";
        card.setAttribute('data-rae-id', r.id);
        card.setAttribute('role','button');
        card.setAttribute('tabindex','0');

        card.innerHTML = `
          <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
              <div class="flex items-center gap-2 flex-wrap">
                <span class="inline-flex items-center rounded-full bg-zinc-100 text-zinc-700 px-2 py-0.5 text-xs font-medium">${e(r.code)}</span>
                <h3 class="text-base font-semibold">${e(r.description)}</h3>
              </div>
              <div class="mt-2 flex flex-wrap items-center gap-2 text-sm">
                <span>Competencia: ${e(r.competency_code)}</span>
                <span class="text-zinc-400">•</span>
                <span>${e(r.competency_name || '')}</span>
                ${r.program_name ? `<span class="text-zinc-400">•</span><span class="inline-flex items-center rounded-full border border-zinc-300 text-zinc-700 px-2 py-0.5 text-xs font-medium" data-role="program-chip">${e(r.program_name)}</span>`:''}
                <span class="text-zinc-400">•</span>
                ${badgeActive}
              </div>
            </div>
            <div class="flex items-center gap-2">
              <button class="p-2 rounded-lg hover:bg-zinc-100" title="Editar" data-edit-rae="${r.id}"><i data-lucide="pencil" class="w-4 h-4"></i></button>
              <label class="switch" data-stop-prop="1">
                <input type="checkbox" ${r.active?'checked':''} data-rae="${r.id}">
                <span class="dot"></span>
                <span class="track absolute inset-0 rounded-full"></span>
              </label>
            </div>
          </div>
        `;
        list.appendChild(card);
      });

      list.querySelectorAll('[data-rae]').forEach(chk=>{
        chk.addEventListener('change',ev=>{
          ev.stopPropagation();
          const id = Number(ev.target.dataset.rae);
          const i = RAES.findIndex(x=>x.id===id);
          if(i>-1){ RAES[i].active = !RAES[i].active; renderRaes(); window.lucide?.createIcons(); }
        });
      });

      list.querySelectorAll('.rae-card').forEach(card=>{
        card.addEventListener('click',(ev)=>{
          const isControl = ev.target.closest('[data-stop-prop],label.switch,input,button');
          if(isControl) return;
          const id = Number(card.getAttribute('data-rae-id'));
          openRaeForEdit(id);
        });
      });
      list.querySelectorAll('[data-edit-rae]').forEach(btn=>{
        btn.addEventListener('click',(ev)=>{
          ev.stopPropagation();
          const id = Number(btn.getAttribute('data-edit-rae'));
          openRaeForEdit(id);
        });
      });

      if (RAES.length === 0) {
        empty.classList.remove('hidden');
        empty.innerHTML = emptyHero('RAE','btnFirstRae','Crear Primer RAE');
        document.getElementById('btnFirstRae')?.addEventListener('click', ()=>{ clearEditingState(); openRaeModal(); setRaeSubmitMode('create'); });
      } else {
        if (filtered.length === 0) {
          empty.classList.remove('hidden');
          empty.innerHTML = `<div class="py-12 text-center"><p class="text-zinc-500">No hay RAE que coincidan con los filtros seleccionados.</p></div>`;
        } else {
          empty.classList.add('hidden');
        }
      }

      window.lucide?.createIcons();

      const btnRae = document.querySelector('section[data-tab="raes"] button i[data-lucide="plus"]')?.parentElement;
      if (btnRae && !btnRae.dataset.bound) {
        btnRae.dataset.bound = "1";
        btnRae.addEventListener('click', ()=>{ clearEditingState(); openRaeModal(); setRaeSubmitMode('create'); });
      }
    }
    document.getElementById('raeProgramFilter')?.addEventListener('change', renderRaes);
    document.getElementById('raeCompetencyFilter')?.addEventListener('change', renderRaes);

    // ====== Modal Programa (se mantiene por compatibilidad)
    const $modal = document.getElementById('modalProgram');
    const $backdrop = document.getElementById('modalProgramBackdrop');
    const $form = document.getElementById('formProgramNew');
    const $btnSubmitProgram = document.getElementById('btnSubmitProgram');
    let labelProgramDefault = $btnSubmitProgram?.textContent ?? '';

    function openProgramModal(){ $backdrop?.classList.remove('hidden'); $modal?.classList.remove('hidden'); document.getElementById('pg_code')?.focus(); window.lucide?.createIcons(); }
    function closeProgramModal(){
      $backdrop?.classList.add('hidden'); $modal?.classList.add('hidden');
      ['pg_code','pg_name','pg_hours','pg_desc'].forEach(id=>document.getElementById(id)?.classList.remove('ring-2','ring-red-300'));
      ['err_code','err_name','err_hours'].forEach(id=>document.getElementById(id)?.classList.add('hidden'));
      $form?.reset();
      setProgramSubmitMode('create');
      clearEditingState();
    }
    function setProgramSubmitMode(mode){
      if(!$btnSubmitProgram) return;
      if(mode==='edit') $btnSubmitProgram.textContent = 'Actualizar';
      else $btnSubmitProgram.textContent = labelProgramDefault || 'Guardar';
    }
    function openProgramForEdit(id){
      const p = PROGRAMS.find(x=>Number(x.id)===Number(id));
      if(!p) return;
      editingState.type = 'program'; editingState.id = Number(id);
      openProgramModal();
      setProgramSubmitMode('edit');
      const $pgCode = document.getElementById('pg_code');
      const $pgName = document.getElementById('pg_name');
      const $pgDesc = document.getElementById('pg_desc');
      const $pgHours = document.getElementById('pg_hours');
      if($pgCode) $pgCode.value = p.code || '';
      if($pgName) $pgName.value = p.name || '';
      if($pgDesc) $pgDesc.value = p.description || '';
      if($pgHours) $pgHours.value = (p.duration_hours ?? '') || '';
    }
    document.getElementById('btnCloseProgram')?.addEventListener('click', closeProgramModal);
    document.getElementById('btnCancelProgram')?.addEventListener('click', closeProgramModal);
    $backdrop?.addEventListener('click', closeProgramModal);
    document.addEventListener('keydown', (ev)=>{ if(ev.key==='Escape' && !$modal?.classList.contains('hidden')) closeProgramModal(); });
    $form?.addEventListener('submit', (ev)=>{
      ev.preventDefault();
      closeProgramModal();
      activateTab('upload');
    });

    // ====== Modal Competencia (sin exigir programa cuando REMOVE_PROGRAMS = true)
    const $modalC = document.getElementById('modalCompetency');
    const $backdropC = document.getElementById('modalCompetencyBackdrop');
    const $formC = document.getElementById('formCompetencyNew');
    const $btnSubmitCompetency = document.getElementById('btnSubmitCompetency');
    let labelCompetencyDefault = $btnSubmitCompetency?.textContent ?? '';

    function openCompetencyModal(){ $backdropC?.classList.remove('hidden'); $modalC?.classList.remove('hidden'); (document.getElementById('cp_program')||document.getElementById('cp_code'))?.focus(); window.lucide?.createIcons(); }
    function closeCompetencyModal(){
      $backdropC?.classList.add('hidden'); $modalC?.classList.add('hidden');
      ['cp_program','cp_code','cp_name','cp_desc'].forEach(id=>document.getElementById(id)?.classList.remove('ring-2','ring-red-300'));
      ['err_cprog','err_ccode','err_cname'].forEach(id=>document.getElementById(id)?.classList.add('hidden'));
      $formC?.reset();
      setCompetencySubmitMode('create');
      clearEditingState();
    }
    function setCompetencySubmitMode(mode){
      if(!$btnSubmitCompetency) return;
      if(mode==='edit') $btnSubmitCompetency.textContent = 'Actualizar';
      else $btnSubmitCompetency.textContent = labelCompetencyDefault || 'Guardar';
    }
    function openCompetencyForEdit(id){
      const c = COMPETENCIES.find(x=>Number(x.id)===Number(id));
      if(!c) return;
      editingState.type = 'competency'; editingState.id = Number(id);
      openCompetencyModal();
      setCompetencySubmitMode('edit');
      const sel = document.getElementById('cp_program');
      if (sel) {
        sel.value = String(c.program_id ?? '');
        if(!Array.from(sel.options).some(o=>o.value===String(c.program_id))) sel.value = '';
      }
      const $cpCode = document.getElementById('cp_code');
      const $cpName = document.getElementById('cp_name');
      const $cpDesc = document.getElementById('cp_desc');
      if($cpCode) $cpCode.value = c.code || '';
      if($cpName) $cpName.value = c.name || '';
      if($cpDesc) $cpDesc.value = c.description || '';
    }
    document.getElementById('btnCloseCompetency')?.addEventListener('click', closeCompetencyModal);
    document.getElementById('btnCancelCompetency')?.addEventListener('click', closeCompetencyModal);
    $backdropC?.addEventListener('click', closeCompetencyModal);
    document.addEventListener('keydown', (ev)=>{ if(ev.key==='Escape' && !$modalC?.classList.contains('hidden')) closeCompetencyModal(); });

    $formC?.addEventListener('submit', (ev)=>{
      ev.preventDefault();
      const sel = document.getElementById('cp_program');
      // Si programas están deshabilitados, ignoramos selección de programa
      const programId = (REMOVE_PROGRAMS || !sel) ? '0' : sel.value.trim();
      const programOpt = sel ? sel.options[sel.selectedIndex] : null;
      const programName = (REMOVE_PROGRAMS || !programOpt) ? '' : (programOpt ? programOpt.text : '');
      const programCode = (REMOVE_PROGRAMS || !programOpt) ? '' : (programOpt.getAttribute('data-code') || '');

      const code = document.getElementById('cp_code')?.value.trim() ?? '';
      const name = document.getElementById('cp_name')?.value.trim() ?? '';
      const desc = document.getElementById('cp_desc')?.value.trim() ?? '';

      let ok = true;
      if(!code){ ok=false; markErr('cp_code','err_ccode'); } else clearErr('cp_code','err_ccode');
      if(!name){ ok=false; markErr('cp_name','err_cname'); } else clearErr('cp_name','err_cname');

      // Solo validamos programa si está habilitado
      if(!REMOVE_PROGRAMS && sel && !programId){ ok=false; markErr('cp_program','err_cprog'); } 
      else { clearErr('cp_program','err_cprog'); }

      if(!ok) return;

      if(editingState.type === 'competency' && editingState.id != null){
        const idx = COMPETENCIES.findIndex(c=>Number(c.id)===Number(editingState.id));
        if(idx>-1){
          // Si programas deshabilitados, no tocamos program_* (se preserva lo que ya tenga)
          if(!REMOVE_PROGRAMS){
            COMPETENCIES[idx].program_id = Number(programId);
            COMPETENCIES[idx].program_name = programName;
            COMPETENCIES[idx].program_code = programCode;
          }
          COMPETENCIES[idx].code = code;
          COMPETENCIES[idx].name = name;
          COMPETENCIES[idx].description = desc;
        }
      } else {
        const nextId = (COMPETENCIES.reduce((m,c)=>Math.max(m, Number(c.id)||0),0) || 0) + 1;
        COMPETENCIES.push({
          id: nextId,
          program_id: REMOVE_PROGRAMS ? null : Number(programId),
          code,
          name,
          description: desc,
          program_name: REMOVE_PROGRAMS ? '' : programName,
          program_code: REMOVE_PROGRAMS ? '' : programCode,
          active: true,
          raes: []
        });
      }

      closeCompetencyModal();
      activateTab('competencies');
    });

    // ====== Modal RAE
    const $modalR = document.getElementById('modalRae');
    const $backdropR = document.getElementById('modalRaeBackdrop');
    const $formR = document.getElementById('formRaeNew');
    const $btnSubmitRae = document.getElementById('btnSubmitRae');
    let labelRaeDefault = $btnSubmitRae?.textContent ?? '';

    function openRaeModal(){ $backdropR?.classList.remove('hidden'); $modalR?.classList.remove('hidden'); document.getElementById('rae_competency')?.focus(); window.lucide?.createIcons(); }
    function closeRaeModal(){
      $backdropR?.classList.add('hidden'); $modalR?.classList.add('hidden');
      ['rae_competency','rae_code','rae_desc'].forEach(id=>document.getElementById(id)?.classList.remove('ring-2','ring-red-300'));
      ['err_rcomp','err_rcode','err_rdesc'].forEach(id=>document.getElementById(id)?.classList.add('hidden'));
      $formR?.reset();
      setRaeSubmitMode('create');
      clearEditingState();
    }
    function setRaeSubmitMode(mode){
      if(!$btnSubmitRae) return;
      if(mode==='edit') $btnSubmitRae.textContent = 'Actualizar';
      else $btnSubmitRae.textContent = labelRaeDefault || 'Guardar';
    }
    function openRaeForEdit(id){
      const r = RAES.find(x=>Number(x.id)===Number(id));
      if(!r) return;
      editingState.type = 'rae'; editingState.id = Number(id);
      openRaeModal();
      setRaeSubmitMode('edit');
      const sel = document.getElementById('rae_competency');
      if (sel) {
        sel.value = String(r.competency_id ?? '');
        if(!Array.from(sel.options).some(o=>o.value===String(r.competency_id))) sel.value = '';
      }
      const $rc = document.getElementById('rae_code');
      const $rd = document.getElementById('rae_desc');
      if($rc) $rc.value = r.code || '';
      if($rd) $rd.value = r.description || '';
    }
    document.getElementById('btnCloseRae')?.addEventListener('click', closeRaeModal);
    document.getElementById('btnCancelRae')?.addEventListener('click', closeRaeModal);
    $backdropR?.addEventListener('click', closeRaeModal);
    document.addEventListener('keydown', (ev)=>{ if(ev.key==='Escape' && !$modalR?.classList.contains('hidden')) closeRaeModal(); });

    $formR?.addEventListener('submit', (ev)=>{
      ev.preventDefault();
      const sel = document.getElementById('rae_competency');
      const compId = sel?.value.trim() ?? '';
      const opt = sel?.options[sel.selectedIndex];
      const cCode = opt?.getAttribute('data-ccode') || '';
      const cName = opt?.getAttribute('data-cname') || '';
      const pName = opt?.getAttribute('data-pname') || '';
      const code = document.getElementById('rae_code')?.value.trim() ?? '';
      const desc = document.getElementById('rae_desc')?.value.trim() ?? '';

      let ok = true;
      if(!compId){ ok=false; markErr('rae_competency','err_rcomp'); } else clearErr('rae_competency','err_rcomp');
      if(!code){ ok=false; markErr('rae_code','err_rcode'); } else clearErr('rae_code','err_rcode');
      if(!desc){ ok=false; markErr('rae_desc','err_rdesc'); } else clearErr('rae_desc','err_rdesc');
      if(!ok) return;

      if(editingState.type === 'rae' && editingState.id != null){
        const idx = RAES.findIndex(r=>Number(r.id)===Number(editingState.id));
        if(idx>-1){
          RAES[idx].competency_id = Number(compId);
          RAES[idx].competency_code = cCode;
          RAES[idx].competency_name = cName;
          RAES[idx].program_name = pName;
          RAES[idx].code = code;
          RAES[idx].description = desc;
        }
        const ci = COMPETENCIES.findIndex(c=>Array.isArray(c.raes) && c.raes.some(rr=>rr.id===editingState.id));
        if(ci>-1){
          const ri = COMPETENCIES[ci].raes.findIndex(rr=>rr.id===editingState.id);
          if(ri>-1){
            COMPETENCIES[ci].raes[ri].code = code;
            COMPETENCIES[ci].raes[ri].description = desc;
          }
        }
      } else {
        const nextId = (RAES.reduce((m,r)=>Math.max(m, Number(r.id)||0),0) || 0) + 1;
        RAES.push({
          id: nextId,
          competency_id: Number(compId),
          code,
          description: desc,
          competency_code: cCode,
          competency_name: cName,
          program_name: pName,
          active: true
        });
        const ci = COMPETENCIES.findIndex(c=>String(c.id)===String(compId));
        if(ci>-1){
          if(!Array.isArray(COMPETENCIES[ci].raes)) COMPETENCIES[ci].raes = [];
          COMPETENCIES[ci].raes.push({ id: nextId, code, description: desc });
        }
      }

      closeRaeModal();
      refreshRaeFilters();
      activateTab('raes');
      renderCompetencies();
    });

    function markErr(inputId, errId){ const el = document.getElementById(inputId); if(!el) return; el.classList.add('ring-2','ring-red-300'); document.getElementById(errId)?.classList.remove('hidden'); }
    function clearErr(inputId, errId){ const el = document.getElementById(inputId); if(!el) return; el.classList.remove('ring-2','ring-red-300'); document.getElementById(errId)?.classList.add('hidden'); }

    // ====== NUEVO: Lógica para el selector de programa en Carga ======
    (function setupUploadProgramPicker(){
      const select = document.getElementById('upload_program');
      const error  = document.getElementById('err_upload_program');

      // Exponer selección globalmente por si el backend/otro script la necesita
      window.UPLOAD_SELECTED_PROGRAM_ID = '';

      if (select) {
        select.addEventListener('change', () => {
          window.UPLOAD_SELECTED_PROGRAM_ID = select.value.trim();
          if (window.UPLOAD_SELECTED_PROGRAM_ID) {
            error?.classList.add('hidden');
            select.classList.remove('ring-2','ring-red-300');
          }
        });
      }

      // Interceptar el botón "Subir y Procesar" sin modificar el HTML base:
      const uploadBtn = document.querySelector('section[data-tab="upload"] .px-6.pb-6 button');
      if (uploadBtn) {
        uploadBtn.addEventListener('click', (ev) => {
          // Validar que haya un programa seleccionado
          if (!window.UPLOAD_SELECTED_PROGRAM_ID) {
            ev.preventDefault();
            error?.classList.remove('hidden');
            select?.classList.add('ring-2','ring-red-300');
            // Puedes cambiar por SweetAlert si lo agregas en la página
            alert('Selecciona un programa de formación antes de procesar la carga.');
            return false;
          }
          // Aquí podrías adjuntar el program_id al FormData cuando implementes el POST
          // ej: formData.append('program_id', window.UPLOAD_SELECTED_PROGRAM_ID);
          return true;
        });
      }
    })();

    // Delegados (compat con Lucide)
    document.addEventListener('click', (ev) => {
      const btnP = ev.target.closest('section[data-tab="programs"] button');
      if (btnP && (btnP.textContent || '').toLowerCase().includes('nuevo programa')) { clearEditingState(); openProgramModal(); setProgramSubmitMode('create'); }
      const btnC = ev.target.closest('section[data-tab="competencies"] button');
      if (btnC && (btnC.textContent || '').toLowerCase().includes('nueva competencia')) { clearEditingState(); openCompetencyModal(); setCompetencySubmitMode('create'); }
      const btnR = ev.target.closest('section[data-tab="raes"] button');
      if (btnR && (btnR.textContent || '').toLowerCase().includes('nuevo rae')) { clearEditingState(); openRaeModal(); setRaeSubmitMode('create'); }
    });

    window.lucide?.createIcons();
  }
})();
