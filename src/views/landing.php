<?php
// Cargar datos desde la base de datos para los selects
require_once __DIR__ . '/../../config/database.php';

$areas = [];
$zonas = [];
$instructores = [];
$trimestres = [];
$competencias = [];

try {
    if (isset($conn)) {
        // Áreas
        $s = $conn->prepare("SELECT id_area, nombre_area FROM areas WHERE estado = 1 ORDER BY nombre_area ASC");
        $s->execute();
        $areas = $s->fetchAll(PDO::FETCH_ASSOC);

        // Zonas
        $s = $conn->prepare("SELECT id_zona, id_area FROM zonas WHERE estado = 1 ORDER BY id_zona ASC");
        $s->execute();
        $zonas = $s->fetchAll(PDO::FETCH_ASSOC);

        // Instructores
        $s = $conn->prepare("SELECT nombre_instructor, tipo_instructor FROM instructores WHERE estado = 1 ORDER BY nombre_instructor ASC");
        $s->execute();
        $instructores = $s->fetchAll(PDO::FETCH_ASSOC);

        // Trimestres
        $s = $conn->prepare("SELECT numero_trimestre, estado FROM trimestre ORDER BY numero_trimestre ASC");
        $s->execute();
        $trimestres = $s->fetchAll(PDO::FETCH_ASSOC);

  // Competencias
  $s = $conn->prepare("SELECT id_competencia, nombre_competencia, descripcion, id_programa FROM competencias WHERE estado = 1 ORDER BY nombre_competencia ASC");
  $s->execute();
  $competencias = $s->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    // No interrumpo la vista si falla la carga, se muestran los selects vacíos
}
?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Proyecto 0</title>

    <!-- Fuente Work Sans (el modal la usa) -->
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/fonts.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>src/assets/css/formulario_crear_trimestralizacion.css">

    <!-- SweetAlert2 -->
    <script src="<?= BASE_URL ?>src/assets/js/sweetalert2.all.min.js"></script>
  </head>
  <body class="flex flex-col min-h-screen font-sans text-center bg-white text-gray-900">
    <!-- Contenido principal -->
    <main class="flex flex-col items-center mt-20 flex-1 px-4 lg:px-8 xl:px-16 2xl:px-32">
      <h1 class="text-4xl sm:text-5xl lg:text-6xl xl:text-7xl 2xl:text-8xl font-bold text-[#39A900] mb-2">PROYECTO 0</h1>
      <p class="text-sm sm:text-base lg:text-lg xl:text-xl 2xl:text-2xl mb-8">Crea y ajusta horarios en segundos</p>

  <div class="flex flex-col gap-3 lg:gap-4 items-center">
        <!-- Botón de crear -->
        <button type="button" id="btnAbrirModal"
          class="w-60 lg:w-72 xl:w-80 2xl:w-96 px-6 py-2 lg:px-8 lg:py-3 border border-gray-400 text-sm lg:text-base xl:text-lg rounded-md text-[#00324D] font-bold bg-white hover:bg-[#004A70] transition-colors duration-200 outline-none cursor-pointer hover:text-white">
          CREAR TRIMESTRALIZACIÓN
        </button>

        <a href="<?= BASE_URL ?>index.php?page=src/views/register_tables"
          class="block text-center w-60 lg:w-72 xl:w-80 2xl:w-96 px-6 py-2 lg:px-8 lg:py-3 border border-gray-400 text-sm lg:text-base xl:text-lg rounded-md text-[#00324D] font-bold bg-white hover:bg-[#004A70] transition-colors duration-200 outline-none cursor-pointer hover:text-white">
          VISUALIZAR HORARIO
        </a>

      </div>
    </main>

    <!-- ============== MODAL CREAR TRIMESTRALIZACIÓN  ============== -->
    <div
      id="modalCrearLanding"
      class="fixed inset-0 z-40 hidden"
      role="dialog"
      aria-modal="true"
      aria-labelledby="tituloModalCrear"
    >
      <!-- Backdrop -->
      <div id="modalBackdrop" class="fixed inset-0 bg-black/40"></div>

      <!-- Contenedor centrado -->
      <div class="fixed inset-0 flex items-center justify-center p-4 z-50">
        <div
          id="modalCard"
          class="bg-white w-full max-w-[420px] sm:max-w-[520px] md:max-w-[640px] lg:max-w-[720px] xl:max-w-[860px] rounded-2xl shadow-md border border-[#d8d8d8] px-4 sm:px-6 md:px-8 lg:px-10 pt-6 sm:pt-8 pb-8 sm:pb-10 mx-3 lg:mx-0"
        >
          <!-- Cabecera con botón cerrar -->
          <div class="flex items-start justify-between">
            <h2 id="tituloModalCrear" class="text-center w-full text-[1.1rem] mb-[6px] text-[#0c2443] font-semibold">
              CREAR TRIMESTRALIZACIÓN
            </h2>
            <button
              id="btnCerrarModal"
              class="ml-3 -mt-2 text-gray-500 hover:text-gray-700"
              aria-label="Cerrar modal"
              title="Cerrar"
              type="button"
              data-close="true" 
            >
              ✕
            </button>
          </div>
          <div class="border-b border-[#dcdcdc] mb-[12px]"></div>

          <!-- Formulario -->
          <form id="formTrimestralizacion" action="<?= BASE_URL ?>src/controllers/TrimestralizacionController.php?accion=crear" method="POST" class="trimestralizacion-form space-y-3 text-sm lg:text-base">
            <!-- AREA -->
            <select name="area" id="id_area" 
              class="select-chev form-field w-full h-12 px-4 text-[13px] rounded-xl border-0 outline-none bg-white shadow placeholder-gray-400 sm:px-4 lg:px-6 sm:text-sm">
              <option value="">Seleccione el area a la que pertenece la ficha</option>
              <?php foreach ($areas as $a): ?>
                <option value="<?= htmlspecialchars($a['id_area']) ?>"><?= htmlspecialchars($a['nombre_area']) ?></option>
              <?php endforeach; ?>
            </select>

            <!-- ZONA -->
            <select name="zona" id="id_zona" 
              class="select-chev form-field w-full h-12 px-4 text-[13px] rounded-xl border-0 outline-none bg-white shadow placeholder-gray-400 sm:px-4 lg:px-6 sm:text-sm">
              <option value="">Seleccione la zona a la que pertenece la ficha</option>
              <?php foreach ($zonas as $z): ?>
                <?php $label = isset($z['id_zona']) ? "Zona " . $z['id_zona'] : "Zona"; ?>
                <option value="<?= htmlspecialchars($z['id_zona']) ?>" data-area="<?= htmlspecialchars($z['id_area'] ?? '') ?>">
                  <?= htmlspecialchars($label) ?>
                </option>
              <?php endforeach; ?>
            </select>

            <select name="nivel_ficha" 
              class="select-chev form-field w-full h-12 px-4 text-[13px] rounded-xl border-0 outline-none bg-white shadow placeholder-gray-400 sm:px-4 lg:px-6 sm:text-sm">
              <option value="">Seleccione el nivel de la ficha</option>
              <option value="tecnico">Tecnico</option>
              <option value="tecnologo">Tecnologo</option>
            </select>

            <!-- TRIMESTRE -->
            <select name="numero_trimestre" 
              class="select-chev form-field w-full h-12 px-4 text-[13px] rounded-xl border-0 outline-none bg-white shadow placeholder-gray-400 sm:px-4 lg:px-6 sm:text-sm">
              <option value="">Seleccione el trimestre que cursa la ficha</option>
              <?php foreach ($trimestres as $t): ?>
                <option value="<?= htmlspecialchars($t['numero_trimestre']) ?>" <?= ($t['estado']==1) ? '' : '' ?>>
                  <?= "Trimestre " . htmlspecialchars($t['numero_trimestre']) . (($t['estado']==1) ? " (activo)" : "") ?>
                </option>
              <?php endforeach; ?>
            </select>

            <div class="flex flex-minw-0 gap-3 flex-col sm:flex-row lg:flex-row">
              <input type="text" name="numero_ficha" id="numero_ficha" placeholder="Número de la ficha" 
                class="form-field basis-1/2 w-full h-12 px-4 pr-12 text-[13px] rounded-xl border-0 outline-none bg-white shadow placeholder-gray-400 sm:px-4 lg:px-6 sm:text-sm"/>
                
              <!-- INSTRUCTOR -->
              <select name="nombre_instructor" id="nombre_instructor"
                class="select-chev form-field basis-1/2 w-full h-12 px-4 text-[13px] rounded-xl border-0 outline-none bg-white shadow placeholder-gray-400 sm:px-4 lg:px-6 sm:text-sm">
                <option value="">Seleccione el instructor</option>
                <?php foreach ($instructores as $ins): ?>
                  <option value="<?= htmlspecialchars($ins['nombre_instructor']) ?>" data-tipo="<?= htmlspecialchars($ins['tipo_instructor']) ?>">
                    <?= htmlspecialchars($ins['nombre_instructor']) ?> <?= isset($ins['tipo_instructor']) ? "— " . htmlspecialchars($ins['tipo_instructor']) : "" ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <select name="dia_semana" id="dia" 
              class="select-chev select-cal form-field w-full h-12 px-4 text-[13px] rounded-xl border-0 outline-none bg-white shadow placeholder-gray-400 sm:px-4 lg:px-6 sm:text-sm">
              <option value="">Seleccione el día</option>
              <option value="lunes">Lunes</option>
              <option value="martes">Martes</option>
              <option value="miercoles">Miércoles</option>
              <option value="jueves">Jueves</option>
              <option value="viernes">Viernes</option>
              <option value="sabado">Sábado</option>
            </select>

            <div class="flex flex-minw-0 gap-3 flex-col sm:flex-row lg:flex-row">
              <select name="hora_inicio" id="hora_inicio" 
                class="select-chev form-field basis-1/2 w-full h-12 px-4 text-[13px] rounded-xl border-0 outline-none bg-white shadow placeholder-gray-400 sm:px-4 lg:px-6 sm:text-sm">
                <option value="">Hora de inicio</option>
                <?php for ($i = 6; $i <= 22; $i++): ?>
                  <option value="<?= $i ?>:00"><?= $i ?>:00</option>
                <?php endfor; ?>
              </select>

              <select name="hora_fin" id="hora_fin" 
                class="select-chev form-field basis-1/2 w-full h-12 px-4 text-[13px] rounded-xl border-0 outline-none bg-white shadow placeholder-gray-400 sm:px-4 lg:px-6 sm:text-sm">
                <option value="">Hora de fin</option>
                <?php for ($i = 7; $i <= 22; $i++): ?>
                  <option value="<?= $i ?>:00"><?= $i ?>:00</option>
                <?php endfor; ?>
              </select>
            </div>
            <!-- Select para vincular competencia existente -->
            <div class="relative">
              <select
                id="id_competencia"
                name="id_competencia"
                class="select-chev form-field w-full h-12 px-4 text-[13px] rounded-xl border-0 outline-none bg-white shadow placeholder-gray-400 sm:px-4 lg:px-6 sm:text-sm">
                <option value="">Seleccione la competencia (opcional)</option>
                <?php if (empty($competencias)): ?>
                  <option disabled>No se encontraron competencias activas</option>
                  <!-- competencias: <?= htmlspecialchars(json_encode($competencias)) ?> -->
                <?php else: ?>
                  <?php foreach ($competencias as $comp): ?>
                    <?php $valueComp = htmlspecialchars($comp['id_competencia']); ?>
                    <option value="<?= $valueComp ?>"
                            data-desc="<?= htmlspecialchars($comp['descripcion'] ?? '') ?>"
                            data-programa="<?= htmlspecialchars($comp['id_programa'] ?? '') ?>">
                      <?= htmlspecialchars($comp['nombre_competencia'] ?? $comp['descripcion'] ?? ('Competencia ' . ($comp['id_competencia'] ?? ''))) ?>
                    </option>
                  <?php endforeach; ?>
                <?php endif; ?>
              </select>
            </div>
            <input type="hidden" name="id_rae" id="id_rae_field" value="">
            <input type="hidden" name="id_programa" id="id_programa_field" value="">
            <button type="submit"
              class="w-full h-12 bg-[#0b2d5b] text-white rounded-lg text-sm lg:text-base font-semibold hover:bg-[#082244] transition-colors">
              GUARDAR TRIMESTRALIZACIÓN
            </button>
          </form>
        </div>
      </div>
    </div>
    <!-- ============== /MODAL ============== -->
  <script>
    window.BASE_URL = window.BASE_URL || "<?= BASE_URL ?>";
  </script>

    <!-- tipo_instructor se determina en el servidor, no hay select correspondiente -->
    <script src="<?= BASE_URL ?>src/assets/js/landing.js"></script>
    <script src="<?= BASE_URL ?>src/assets/js/formulario_trimestralizacion.js"></script>
    <script>
      // Copiar atributos data-rae/data-programa desde la opción seleccionada al formulario
      document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('#formTrimestralizacion');
        if (!form) return;
        const sel = form.querySelector('[name="id_competencia"]');
        const raeField = form.querySelector('#id_rae_field');
        const progField = form.querySelector('#id_programa_field');

        function syncCompData() {
          const opt = sel && sel.selectedOptions && sel.selectedOptions[0];
          if (!opt) return;
          if (raeField) raeField.value = opt.dataset.rae || '';
          if (progField) progField.value = opt.dataset.programa || '';
        }

        if (sel) {
          sel.addEventListener('change', syncCompData);
          // Sincronizar al cargar
          syncCompData();
        }
      });
    </script>
    <script>
      (function(){
        const selArea = document.getElementById('id_area');
        const selZona = document.getElementById('id_zona');
        if (!selArea || !selZona) return;

        function filterZonas() {
          const areaVal = selArea.value;
          let hasVisible = false;

          for (const opt of selZona.options) {
            if (opt.value === "") {
              opt.hidden = false;
              opt.disabled = false;
              continue;
            }
            const optArea = opt.dataset.area ?? "";
            // Si no hay área seleccionada, mostrar todas las zonas
            const show = areaVal !== "" ? (String(optArea) === String(areaVal)) : true;
            opt.hidden = !show;
            opt.disabled = !show;
            if (show) hasVisible = true;
          }

          // Si la zona actualmente seleccionada queda oculta
          const selectedOpt = selZona.selectedOptions[0];
          if (selectedOpt && selectedOpt.hidden) selZona.value = "";
        }

        selArea.addEventListener('change', filterZonas);
        // Ejecutar una vez al cargar para sincronizar
        document.addEventListener('DOMContentLoaded', filterZonas);
      })();
    </script>
  </body>
</html>
