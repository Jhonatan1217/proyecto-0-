<?php
/* ==========================================
   VISTA LIMPIA – SOLO FRONTEND (SIN ARRAYS)
   - Aquí solo armamos la UI. Los datos los maneja JS.
   ========================================== */
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Sistema de Gestión Académica</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <!-- Alertas -->
  <script src="<?= BASE_URL ?>src/assets/js/sweetalert2.all.min.js"></script>
  <!-- Estilos propios (competencias) -->
  <link rel="stylesheet" href="src/assets/css/gestionCompetencias.css" />
</head>
<body class="bg-white text-zinc-900 min-h-screen">

  <main class="max-w-7xl mx-auto px-4 py-8">
    <div class="w-full">

      <!-- Tabs: cambiamos de sección sin recargar -->
      <div class="bg-zinc-100 rounded-2xl p-1 flex items-center gap-1 justify-around">
        <button data-tab-btn="upload" class="tab-btn flex items-center justify-center gap-2 px-4 py-2 rounded-xl w-full sm:w-auto text-zinc-700">
          <img src="src/assets/img/upload-grey.svg" class="w-4 h-4">Carga Excel</span>
        </button> 
        <button data-tab-btn="programs" class="tab-btn flex items-center justify-center gap-2 px-4 py-2 rounded-xl w-full sm:w-auto text-zinc-700">
          <img src="src/assets/img/graduation-cap.svg" class="w-4 h-4"></i><span class=" sm:inline">Programas</span>
        </button>
        <button data-tab-btn="competencies" class="tab-btn flex items-center justify-center gap-2 px-4 py-2 rounded-xl w-full sm:w-auto text-zinc-700">
          <img src="src/assets/img/book-open.svg" class="w-4 h-4"><span class="sm:inline">Competencias</span>
        </button>
        <button data-tab-btn="raes" class="tab-btn flex items-center justify-center gap-2 px-4 py-2 rounded-xl w-full sm:w-auto text-zinc-700">
          <img src="src/assets/img/target.svg" class="w-4 h-4"><span class="sm:inline">RAE</span>
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
                <img src="src/assets/img/upload.svg" class="w-4 h-4"> Subir Archivo
              </h3>
              <p class="text-sm text-zinc-500">Seleccione un archivo Excel (.xlsx) para importar</p>
            </div>

            <div class="px-6 mt-4">
              <label class="block text-sm font-medium mb-1">Programa de formación <span class="text-red-500">*</span></label>
              <select id="upload_program" class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm outline-none bg-white">
                  <option value="">Seleccione un programa</option>

                  <?php foreach($programas as $p): ?>
                      <option value="<?= $p['id_programa'] ?>">
                          <?= $p['nombre_programa'] ?>
                      </option>
                  <?php endforeach; ?>

              </select>

              <p id="err_upload_program" class="hidden mt-1 text-xs" style="color:#dc2626">Seleccione un programa para asociar la carga.</p>
            </div>

            <div class="px-6 pb-6 space-y-4 mt-4">
              <label class="flex h-36 w-full cursor-pointer items-center justify-center rounded-xl border-2 border-dashed border-zinc-300 bg-zinc-50">
                <div class="flex flex-col items-center text-center">
                  <img src="src/assets/img/upload-white.svg" class="w-4 h-4 ">
                  <p class="mt-2 text-sm text-zinc-500">Click para seleccionar archivo</p>
                </div>
                <input type="file" id="inputExcel" name="archivo" class="hidden" accept=".xlsx,.xls" required />
              </label>
              <button id="btnProcesarExcel" class="w-full rounded-xl" style="background:#00324d;color:#fff;padding:.65rem 1rem;font-size:.875rem;font-weight:500">
                Subir y Procesar
              </button>

            </div>
          </div>
        </div>
      </section>

      <!-- ========== PROGRAMAS ========== -->
      <!-- Grid de tarjetas y estado vacío (JS se encarga) -->
      <section data-tab="programs" class="tab-pane mt-8 hidden">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h2 class="text-3xl text-[#39a900] font-bold">Programas de Formación</h2>
            <p class="text-sm text-zinc-500">Gestione los programas de formación disponibles</p>
          </div>
          <button id="btnNewProgram" class="rounded-xl px-4 py-2 text-sm font-medium flex items-center gap-2 bg-[#00324d] text-[#fff]">
            <img src="src/assets/img/plus.svg" class="w-4 h-4" alt="signo de mas"> Nuevo Programa
          </button>
        </div>

        <div id="programsGrid" class="grid gap-5 md:grid-cols-2 lg:grid-cols-3"></div>
        <div id="programsEmpty" class="hidden rounded-2xl ring-1 ring-zinc-200 shadow-sm"></div>
      </section>

      <!-- ========== COMPETENCIAS ========== -->
      <!-- Lista + filtro por programa -->
      <section data-tab="competencies" class="tab-pane mt-8 hidden">
        <div class="flex items-center justify-between flex-wrap gap-4 mb-6">
          <div>
            <h2 class="text-3xl font-bold" style="color:#39a900">Competencias</h2>
            <p class="text-sm text-zinc-500">Visualice y edite las competencias cargadas</p>
          </div>
          <div class="flex items-center gap-3">
            <select id="competencyProgramFilter" class="w-[260px] border border-zinc-300 rounded-xl px-3 py-2 text-sm select-nice">
              <option value="all">Todos los programas</option>
            </select>
            <button id="btnNewCompetency" class="rounded-xl px-4 py-2 text-sm font-medium flex items-center gap-2 bg-[#00324d] text-[#fff]">
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
      <!-- Filtros encadenados + lista (JS lo maneja) -->
      <section data-tab="raes" class="tab-pane mt-8 hidden">
        <div class="flex items-center justify-between flex-wrap gap-4 mb-6">
          <div>
            <h2 class="text-3xl font-bold">Resultados de Aprendizaje Esperados (RAE)</h2>
            <p class="text-sm text-zinc-500">Visualice y edite los RAE cargados</p>
          </div>
          <button class="rounded-xl px-4 py-2 text-sm font-medium flex items-center gap-2  bg-[#00324d] text-[#fff]">
            <img src="src/assets/img/plus.svg" class="w-4 h-4"></i> Nuevo RAE
          </button>
        </div>

        <div class="flex gap-3 flex-wrap mb-5">
          <select id="raeProgramFilter" class="w-[260px] border border-zinc-300 rounded-xl px-3 py-2 text-sm select-nice">
            <option value="all">Todos los programas</option>
          </select>
          <select id="raeCompetencyFilter" class="w-[260px] border border-zinc-300 rounded-xl px-3 py-2 text-sm select-nice">
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
  <!-- Modal simple: el JS abre/cierra y envía el form -->
  <div id="modalProgramBackdrop" class="hidden fixed inset-0 z-40" style="background:rgba(0,0,0,.4)"></div>
  <section id="modalProgram" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
  <div class="w-full max-w-2xl rounded-2xl bg-white shadow-2xl" style="border:1px solid #e5e7eb">
    <div class="flex items-start justify-between p-6 pb-2">
      <div>
        <h2 id="modalProgramTitle" class="text-2xl font-bold text-zinc-900">
          Nuevo Programa
        </h2>
        <p class="text-sm text-zinc-500">Complete la información del programa de formación</p>
      </div>
      <button id="btnCloseProgram" class="p-2 rounded-lg hover:bg-zinc-100">X
      </button>
    </div>

    <!-- Campos esenciales: código y nombre; los otros son opcionales -->
    <form id="formProgramNew" class="p-6 pt-4 space-y-4">
      <div>
        <label class="block text-sm font-medium mb-1">Código *</label>
        <input id="pg_code" name="id_programa" type="text"
               placeholder="Ej: 228106"
               class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm outline-none">
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Nombre *</label>
        <input id="pg_name" type="text"
               placeholder="Ej: Análisis y Desarrollo de Software (ADSI)"
               class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm outline-none">
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Descripción</label>
        <textarea id="pg_desc" rows="3"
                  placeholder="Ej: Programa orientado al diseño y desarrollo de aplicaciones empresariales."
                  class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm outline-none"></textarea>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Duración (horas)</label>
        <input id="pg_hours" type="number" min="0"
               placeholder="Ej: 2640"
               class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm outline-none">
      </div>

      <div class="flex justify-end gap-3 pt-2">
        <button type="button" id="btnCancelProgram"
                class="rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium">
          Cancelar
        </button>
        <button type="submit" id="btnSubmitProgram"
                class="rounded-xl px-4 py-2.5 text-sm font-medium bg-[#00324d] text-[#fff]">
          Guardar
        </button>
      </div>
    </form>
  </div>
</section>


  <!-- ===== MODAL: Nueva/Editar Competencia ===== -->
  <!-- Incluye select de programa para relacionar la competencia -->
  <div id="modalCompetencyBackdrop" class="hidden fixed inset-0 z-40" style="background:rgba(0,0,0,.4)"></div>
  <section id="modalCompetency" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
  <div class="w-full max-w-2xl rounded-2xl bg-white shadow-2xl modal-card" style="border:1px solid #e5e7eb">
    <div class="flex items-start justify-between p-6 pb-2">
      <div>
        <h3 id="titleCompetency" class="text-2xl font-bold">Nueva Competencia</h3>
        <p class="text-sm text-zinc-500">Complete la información de la competencia</p>
      </div>
      <button id="btnCloseCompetency" class="p-2 rounded-lg hover:bg-zinc-100">X
      </button>
    </div>

    <!-- cp_program es obligatorio; code y name también -->
    <form id="formCompetencyNew" class="p-6 pt-4 space-y-4">
      <div>
        <label class="block text-sm font-medium mb-1">Programa *</label>
        <select id="cp_program"
                class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm bg-white select-nice">
          <option value="">Seleccione el programa de formación asociado</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Código *</label>
        <input id="cp_code" type="text"
               placeholder="Ej: 220501046 (identificador único de la competencia)"
               class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm outline-none">
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Nombre *</label>
        <input id="cp_name" type="text"
               placeholder="Ej: Desarrollar software aplicando metodologías ágiles"
               class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm outline-none">
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Descripción</label>
        <textarea id="cp_desc" rows="3"
                  placeholder="Ej: Competencia enfocada en el diseño, implementación y documentación de soluciones de software según las necesidades del cliente."
                  class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm outline-none"></textarea>
      </div>

      <div class="flex justify-end gap-3 pt-2">
        <button type="button" id="btnCancelCompetency"
                class="rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium">
          Cancelar
        </button>
        <button type="submit" id="btnSubmitCompetency"
                class="rounded-xl px-4 py-2.5 text-sm font-medium bg-[#00324d] text-[#fff]">
          Guardar
        </button>
      </div>
    </form>
  </div>
</section>


  <!-- ===== MODAL: Nuevo RAE ===== -->
  <!-- Seleccione competencia, ponga código y descripción -->
  <div id="modalRaeBackdrop" class="hidden fixed inset-0 z-40" style="background:rgba(0,0,0,.4)"></div>
<section id="modalRae" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
  <div class="w-full max-w-2xl rounded-2xl bg-white shadow-2xl" style="border:1px solid #e5e7eb">
    <div class="flex items-start justify-between p-6 pb-2">
      <div>
        <h3 class="text-2xl font-bold">Nuevo RAE</h3>
        <p class="text-sm text-zinc-500">Complete la información del Resultado de Aprendizaje Esperado</p>
      </div>
      <button id="btnCloseRae" class="p-2 rounded-lg hover:bg-zinc-100" aria-label="Cerrar modal">X
      </button>
    </div>

    <!-- rae_competency es clave para enlazar -->
    <form id="formRaeNew" class="p-6 pt-4 space-y-4">
      <div>
        <label class="block text-sm font-medium mb-1">Competencia *</label>
        <select
          id="rae_competency"
          class="select-nice w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm bg-white outline-none focus:ring-2 focus:ring-[#00324d]/20"
        >
          <option value="">Seleccione una competencia</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Código *</label>
        <input
          id="rae_code"
          type="text"
          placeholder="Ej: 220501032-01"
          class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm outline-none placeholder-zinc-400 focus:ring-2 focus:ring-[#00324d]/20"
        >
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Descripción *</label>
        <textarea
          id="rae_desc"
          rows="3"
          placeholder="Describe el resultado de aprendizaje…"
          class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm outline-none placeholder-zinc-400 focus:ring-2 focus:ring-[#00324d]/20"
        ></textarea>
      </div>

      <div class="flex justify-end gap-3 pt-2">
        <button type="button" id="btnCancelRae" class="rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium">Cancelar</button>
        <button type="submit" id="btnSubmitRae" class="rounded-xl px-4 py-2.5 text-sm font-medium bg-[#00324d] text-[#fff]">Guardar</button>
      </div>
    </form>
  </div>
</section>

  <!-- ========= SCRIPTS ========= -->
  <!-- Tabs: muestra una sección y oculta el resto -->
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
        // Redibuja iconos al cambiar
        window.lucide?.createIcons();
      }
      // Click de pestañas + pestaña inicial
      btns.forEach(b => b.addEventListener('click', () => activate(b.getAttribute('data-tab-btn'))));
      activate('upload');
    })();
  </script>

  <!-- Endpoints y flags globales que usan los JS -->
  <script>
    window.API_PROGRAMAS     = encodeURI('<?= BASE_URL ?? '' ?>src/controllers/ProgramasController.php');
    window.PROGRAMS_MANAGED_BY_API = true;
    window.API_COMPETENCIAS  = encodeURI('<?= BASE_URL ?? '' ?>src/controllers/CompetenciaController.php');
    window.API_RAES = encodeURI('<?= BASE_URL ?? '' ?>src/controllers/RaeController.php');
  </script>

  <!-- Módulos: cada uno maneja su CRUD/UX. El ?v= ayuda a romper caché -->
  <script src="<?= BASE_URL ?? '' ?>src/assets/js/gestionProgramas.js?v=3"></script>
  <script src="<?= BASE_URL ?? '' ?>src/assets/js/gestionCompetencias.js?v=2" defer></script>
  <script src="<?= BASE_URL ?? '' ?>src/assets/js/gestionRaes.js?v=1" defer></script>

  <!-- Agregar cargar programas dinámicamente -->
  <script>
document.addEventListener("DOMContentLoaded", () => {

  const selectProgram = document.getElementById("upload_program");

  fetch("<?= BASE_URL ?>src/controllers/ProgramasController.php?accion=listar")
    .then(res => res.json())
    .then(programas => {

      if (!Array.isArray(programas)) return;

      programas.forEach(p => {
        selectProgram.innerHTML += `
          <option value="${p.id_programa}">
            ${p.nombre_programa}
          </option>`;
      });
    })
    .catch(err => {
      console.error("Error cargando programas:", err);
      // Toast de error (solo toast)
      if (window.Swal) {
        Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'error',
          title: 'No se pudieron cargar los programas',
          timer: 2500,
          showConfirmButton: false,
          timerProgressBar: true
        });
      }
    });
});
</script>

  <!-- Implementar SweetAlert para la carga de Excel (SOLO TOASTS) -->
  <script>
document.addEventListener("DOMContentLoaded", () => {

  const btnProcesar = document.getElementById("btnProcesarExcel");
  const inputFile = document.getElementById("inputExcel");
  const selectProgram = document.getElementById("upload_program");

  btnProcesar.addEventListener("click", function () {

    // Validar que hay un programa seleccionado (toast)
    if (selectProgram.value === "") {
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'warning',
        title: 'Seleccione un programa de formación',
        timer: 2200,
        showConfirmButton: false,
        timerProgressBar: true
      });
      return;
    }

    // Validar que hay un archivo seleccionado (toast)
    if (inputFile.files.length === 0) {
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'error',
        title: 'Seleccione un archivo Excel (.xlsx)',
        timer: 2200,
        showConfirmButton: false,
        timerProgressBar: true
      });
      return;
    }

    let formData = new FormData();
    formData.append("archivo", inputFile.files[0]);
    formData.append("programa", selectProgram.value);

    // Toast de carga persistente (con loading). Se cierra manualmente.
    Swal.fire({
      toast: true,
      position: 'top-end',
      title: 'Procesando archivo…',
      icon: 'info',
      showConfirmButton: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    fetch("<?= BASE_URL ?>src/controllers/EtlController.php?accion=subir", {
      method: "POST",
      body: formData
    })
    .then(r => r.text())
    .then(r => {
      console.log("RESPUESTA DEL SERVIDOR:", r);

      // Cerrar el loading y mostrar éxito como toast
      Swal.close();
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: 'Archivo procesado correctamente',
        timer: 2600,
        showConfirmButton: false,
        timerProgressBar: true
      });

      // Limpiar inputs después de éxito
      inputFile.value = '';
      selectProgram.value = '';
    })
    .catch(e => {
      console.error("ERROR:", e);

      // Cerrar el loading y mostrar error como toast
      Swal.close();
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'error',
        title: 'Error al procesar el archivo',
        timer: 2600,
        showConfirmButton: false,
        timerProgressBar: true
      });
    });

  });

});
</script>

</body>
</html>