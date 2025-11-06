<?php
/* ==========================================
   VISTA LIMPIA – SOLO FRONTEND (SIN ARRAYS)
   ========================================== */
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Sistema de Gestión Académica</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="<?= BASE_URL ?>src/assets/js/sweetalert2.all.min.js"></script>


  <style>
    .switch{--h:22px;--w:42px;position:relative;width:var(--w);height:var(--h);border-radius:999px;background:#e5e7eb;transition:.2s}
    .switch input{display:none}
    .switch .dot{position:absolute;inset:3px auto auto 3px;width:16px;height:16px;border-radius:999px;background:#fff;box-shadow:0 1px 2px rgba(0,0,0,.15);transition:.2s}
    .switch input:checked + .dot{transform:translateX(20px)}
    .switch .track{transition:background-color .2s ease}
    .tabs-pill-active{background:#fff;border:1px solid #e5e7eb;box-shadow:0 1px 0 rgba(0,0,0,.04)}
    .rotate-90{transform:rotate(90deg)}
    /* utilidades mínimas */
    .hidden{display:none}
  </style>
</head>
<body class="bg-white text-zinc-900 min-h-screen">

  <main class="max-w-7xl mx-auto px-4 py-8">
    <div class="w-full">

      <!-- Tabs -->
      <div class="bg-zinc-100 rounded-2xl p-1 flex items-center gap-1 justify-around">
        <button data-tab-btn="upload" class="tab-btn flex items-center justify-center gap-2 px-4 py-2 rounded-xl w-full sm:w-auto text-zinc-700">
          <i data-lucide="upload" class="w-4 h-4"></i><span class="sm:inline">Carga Excel</span>
        </button>
        <button data-tab-btn="programs" class="tab-btn flex items-center justify-center gap-2 px-4 py-2 rounded-xl w-full sm:w-auto text-zinc-700">
          <i data-lucide="graduation-cap" class="w-4 h-4"></i><span class=" sm:inline">Programas</span>
        </button>
        <button data-tab-btn="competencies" class="tab-btn flex items-center justify-center gap-2 px-4 py-2 rounded-xl w-full sm:w-auto text-zinc-700">
          <i data-lucide="book-open" class="w-4 h-4"></i><span class="sm:inline">Competencias</span>
        </button>
        <button data-tab-btn="raes" class="tab-btn flex items-center justify-center gap-2 px-4 py-2 rounded-xl w-full sm:w-auto text-zinc-700">
          <i data-lucide="target" class="w-4 h-4"></i><span class="sm:inline">RAE</span>
        </button>
      </div>

      <!-- ========== CARGA EXCEL ========== -->
      <section data-tab="upload" class="tab-pane mt-8">
        <h2 class="text-3xl font-bold mb-1" style="color:#39a900">Carga Masiva desde Excel</h2>
        <p class="text-sm text-zinc-500 mb-6">Importe programas, competencias y RAE desde un archivo Excel</p>

        <div class="max-w-8xl">
          <div class="rounded-2xl ring-1 ring-zinc-200 shadow-sm overflow-hidden bg-white">
            <div class="px-6 pt-6">
              <h3 class="text-lg font-semibold flex items-center gap-2">
                <i data-lucide="upload" class="w-5 h-5"></i> Subir Archivo
              </h3>
              <p class="text-sm text-zinc-500">Seleccione un archivo Excel (.xlsx) para importar</p>
            </div>

            <div class="px-6 mt-4">
              <label class="block text-sm font-medium mb-1">Programa de formación <span class="text-red-500">*</span></label>
              <select id="upload_program" class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm outline-none bg-white">
                <option value="">Seleccione un programa</option>
              </select>
              <p id="err_upload_program" class="hidden mt-1 text-xs" style="color:#dc2626">Seleccione un programa para asociar la carga.</p>
            </div>

            <div class="px-6 pb-6 space-y-4 mt-4">
              <label class="flex h-36 w-full cursor-pointer items-center justify-center rounded-xl border-2 border-dashed border-zinc-300 bg-zinc-50">
                <div class="text-center">
                  <i data-lucide="upload" class="mx-auto h-8 w-8" style="color:#a1a1aa"></i>
                  <p class="mt-2 text-sm text-zinc-500">Click para seleccionar archivo</p>
                </div>
                <input type="file" class="hidden" />
              </label>
              <button class="w-full rounded-xl" style="background:#00324d;color:#fff;padding:.65rem 1rem;font-size:.875rem;font-weight:500">Subir y Procesar</button>
            </div>
          </div>
        </div>
      </section>

      <!-- ========== PROGRAMAS ========== -->
      <section data-tab="programs" class="tab-pane mt-8 hidden">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h2 class="text-3xl font-bold">Programas de Formación</h2>
            <p class="text-sm text-zinc-500">Gestione los programas de formación disponibles</p>
          </div>
          <button id="btnNewProgram" class="rounded-xl px-4 py-2 text-sm font-medium flex items-center gap-2"
                  style="background:#0a0a0a;color:#fff">
            <i data-lucide="plus" class="w-4 h-4"></i> Nuevo Programa
          </button>
        </div>

        <div id="programsGrid" class="grid gap-5 md:grid-cols-2 lg:grid-cols-3"></div>
        <div id="programsEmpty" class="hidden rounded-2xl ring-1 ring-zinc-200 shadow-sm"></div>
      </section>

      <!-- ========== COMPETENCIAS ========== -->
      <section data-tab="competencies" class="tab-pane mt-8 hidden">
        <div class="flex items-center justify-between flex-wrap gap-4 mb-6">
          <div>
            <h2 class="text-3xl font-bold" style="color:#39a900">Competencias</h2>
            <p class="text-sm text-zinc-500">Visualice y edite las competencias cargadas desde Excel</p>
          </div>
          <div class="flex items-center gap-3">
            <select id="competencyProgramFilter" class="w-[260px] border border-zinc-300 rounded-xl px-3 py-2 text-sm">
              <option value="all">Todos los programas</option>
            </select>
            <button id="btnNewCompetency" class="rounded-xl px-4 py-2 text-sm font-medium flex items-center gap-2" style="background:#00324d;color:#fff">
              <img src="src/assets/img/plus.svg" class="w-4 h-4" alt="icono añadir"> Nueva Competencia
            </button>

          </div>
        </div>
        <div id="competenciesList" class="space-y-5"></div>
        <div id="competenciesEmpty" class="hidden rounded-2xl ring-1 ring-zinc-200 shadow-sm">
          <div class="py-12 text-center text-zinc-500">No hay competencias que coincidan con el filtro seleccionado.</div>
        </div>
      </section>

      <!-- ========== RAES ========== -->
      <section data-tab="raes" class="tab-pane mt-8 hidden">
        <div class="flex items-center justify-between flex-wrap gap-4 mb-6">
          <div>
            <h2 class="text-3xl font-bold">Resultados de Aprendizaje Esperados (RAE)</h2>
            <p class="text-sm text-zinc-500">Visualice y edite los RAE cargados desde Excel</p>
          </div>
          <button class="rounded-xl px-4 py-2 text-sm font-medium flex items-center gap-2"
                  style="background:#0a0a0a;color:#fff">
            <i data-lucide="plus" class="w-4 h-4"></i> Nuevo RAE
          </button>
        </div>

        <div class="flex gap-3 flex-wrap mb-5">
          <select id="raeProgramFilter" class="w-[260px] border border-zinc-300 rounded-xl px-3 py-2 text-sm">
            <option value="all">Todos los programas</option>
          </select>
          <select id="raeCompetencyFilter" class="w-[260px] border border-zinc-300 rounded-xl px-3 py-2 text-sm">
            <option value="all">Todas las competencias</option>
          </select>
        </div>

        <div id="raesList" class="space-y-4"></div>
        <div id="raesEmpty" class="hidden rounded-2xl ring-1 ring-zinc-200 shadow-sm">
          <div class="py-12 text-center text-zinc-500">No hay RAE que coincidan con los filtros seleccionados.</div>
        </div>
      </section>

    </div>
  </main>

  <!-- ===== MODAL: Nuevo/Editar Programa ===== -->
  <div id="modalProgramBackdrop" class="hidden fixed inset-0 z-40" style="background:rgba(0,0,0,.4)"></div>
  <section id="modalProgram" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="w-full max-w-2xl rounded-2xl bg-white shadow-2xl" style="border:1px solid #e5e7eb">
      <div class="flex items-start justify-between p-6 pb-2">
        <div>
          <h3 class="text-2xl font-bold">Nuevo Programa</h3>
          <p class="text-sm text-zinc-500">Complete la información del programa de formación</p>
        </div>
        <button id="btnCloseProgram" class="p-2 rounded-lg hover:bg-zinc-100"><i data-lucide="x" class="w-5 h-5"></i></button>
      </div>
      <form id="formProgramNew" class="p-6 pt-4 space-y-4">
        <div>
          <label class="block text-sm font-medium mb-1">Código *</label>
          <input id="pg_code" name="id_programa" type="text" class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm outline-none">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Nombre *</label>
          <input id="pg_name" type="text" class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm outline-none">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Descripción</label>
          <textarea id="pg_desc" rows="3" class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm outline-none"></textarea>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Duración (horas)</label>
          <input id="pg_hours" type="number" min="0" class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm outline-none">
        </div>
        <div class="flex justify-end gap-3 pt-2">
          <button type="button" id="btnCancelProgram" class="rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium">Cancelar</button>
          <button type="submit" id="btnSubmitProgram" class="rounded-xl px-4 py-2.5 text-sm font-medium" style="background:#0a0a0a;color:#fff">Guardar</button>
        </div>
      </form>
    </div>
  </section>

  <!-- ===== MODAL: Nueva Competencia ===== -->
  <div id="modalCompetencyBackdrop" class="hidden fixed inset-0 z-40" style="background:rgba(0,0,0,.4)"></div>
  <section id="modalCompetency" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="w-full max-w-2xl rounded-2xl bg-white shadow-2xl" style="border:1px solid #e5e7eb">
      <div class="flex items-start justify-between p-6 pb-2">
        <div>
          <h3 class="text-2xl font-bold">Nueva Competencia</h3>
          <p class="text-sm text-zinc-500">Complete la información de la competencia</p>
        </div>
        <button id="btnCloseCompetency" class="p-2 rounded-lg hover:bg-zinc-100"><i data-lucide="x" class="w-5 h-5"></i></button>
      </div>
      <form id="formCompetencyNew" class="p-6 pt-4 space-y-4">
        <div>
          <label class="block text-sm font-medium mb-1">Programa *</label>
          <select id="cp_program" class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm bg-white">
            <option value="">Seleccione un programa</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Código *</label>
          <input id="cp_code" type="text" class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm outline-none">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Nombre *</label>
          <input id="cp_name" type="text" class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm outline-none">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Descripción</label>
          <textarea id="cp_desc" rows="3" class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm outline-none"></textarea>
        </div>
        <div class="flex justify-end gap-3 pt-2">
          <button type="button" id="btnCancelCompetency" class="rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium">Cancelar</button>
          <button type="submit" id="btnSubmitCompetency" class="rounded-xl px-4 py-2.5 text-sm font-medium" style="background:#0a0a0a;color:#fff">Guardar</button>
        </div>
      </form>
    </div>
  </section>

  <!-- ===== MODAL: Nuevo RAE ===== -->
  <div id="modalRaeBackdrop" class="hidden fixed inset-0 z-40" style="background:rgba(0,0,0,.4)"></div>
  <section id="modalRae" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="w-full max-w-2xl rounded-2xl bg-white shadow-2xl" style="border:1px solid #e5e7eb">
      <div class="flex items-start justify-between p-6 pb-2">
        <div>
          <h3 class="text-2xl font-bold">Nuevo RAE</h3>
          <p class="text-sm text-zinc-500">Complete la información del Resultado de Aprendizaje Esperado</p>
        </div>
        <button id="btnCloseRae" class="p-2 rounded-lg hover:bg-zinc-100"><i data-lucide="x" class="w-5 h-5"></i></button>
      </div>
      <form id="formRaeNew" class="p-6 pt-4 space-y-4">
        <div>
          <label class="block text-sm font-medium mb-1">Competencia *</label>
          <select id="rae_competency" class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm bg-white">
            <option value="">Seleccione una competencia</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Código *</label>
          <input id="rae_code" type="text" class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm outline-none">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Descripción *</label>
          <textarea id="rae_desc" rows="3" class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm outline-none"></textarea>
        </div>
        <div class="flex justify-end gap-3 pt-2">
          <button type="button" id="btnCancelRae" class="rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium">Cancelar</button>
          <button type="submit" id="btnSubmitRae" class="rounded-xl px-4 py-2.5 text-sm font-medium" style="background:#0a0a0a;color:#fff">Guardar</button>
        </div>
      </form>
    </div>
  </section>

  <!-- ========= SCRIPTS ========= -->
  <!-- 1) Script chiquito: pestañas + íconos (no toca Programas) -->
  <script>
    (function(){
      const btns = document.querySelectorAll('[data-tab-btn]');
      const panes = document.querySelectorAll('.tab-pane');
      function activate(key){
        panes.forEach(p => p.classList.toggle('hidden', p.getAttribute('data-tab') !== key));
        btns.forEach(b => {
          const on = b.getAttribute('data-tab-btn') === key;
          b.classList.toggle('tabs-pill-active', on);
          b.setAttribute('aria-selected', on ? 'true' : 'false');
          b.classList.toggle('text-zinc-900', on);
          b.classList.toggle('text-zinc-700', !on);
        });
        window.lucide?.createIcons();
      }
      btns.forEach(b => b.addEventListener('click', () => activate(b.getAttribute('data-tab-btn'))));
      // tab inicial: programs (tu JS de funcionalidad cargará la lista)
      activate('programs');
    })();
  </script>

  <!-- 2) Exponer endpoint para gestionProgramas.js -->
  <script>
    window.API_PROGRAMAS     = encodeURI('<?= BASE_URL ?? '' ?>src/controllers/ProgramasController.php');
    window.PROGRAMS_MANAGED_BY_API = true;

    /* NUEVO: endpoint de competencias */
    window.API_COMPETENCIAS  = encodeURI('<?= BASE_URL ?? '' ?>src/controllers/CompetenciaController.php');

    window.API_RAES = encodeURI('<?= BASE_URL ?? '' ?>src/controllers/RaeController.php');
  </script>


  <!-- 3) Tu JS de funcionalidad (Programas) -->
<script src="<?= BASE_URL ?? '' ?>src/assets/js/gestionProgramas.js?v=3"></script>

<!-- NUEVO: JS de Competencias -->
<script src="<?= BASE_URL ?? '' ?>src/assets/js/gestionCompetencias.js?v=1" defer></script>

<script src="<?= BASE_URL ?? '' ?>src/assets/js/gestionRaes.js?v=1" defer></script>



</body>
</html>
