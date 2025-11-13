<?php
// Cargar datos desde la base de datos para los selects
require_once __DIR__ . '/../../config/database.php';

$areas = [];
$zonas = [];
$instructores = [];
$trimestres = [];
$competencias = [];
$programas = []; // <- NUEVO: arreglo para los programas

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

        // Trimestres
$s = $conn->prepare("SELECT numero_trimestre, estado FROM trimestre ORDER BY numero_trimestre ASC");
$s->execute();
$trimestres = $s->fetchAll(PDO::FETCH_ASSOC);

// 🔹 Programas de formación
$s = $conn->prepare("
    SELECT id_programa, nombre_programa
    FROM programas
    WHERE estado = 1
    ORDER BY nombre_programa ASC
");
$s->execute();
$programas = $s->fetchAll(PDO::FETCH_ASSOC);

// Competencias
$s = $conn->prepare("SELECT id_competencia, nombre_competencia, id_programa FROM competencias WHERE estado = 1 ORDER BY nombre_competencia ASC");
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

            <!-- 🔹 NUEVO: SELECT DE PROGRAMAS DE FORMACIÓN -->
            <select
              id="id_programa_select"
              name="id_programa_select"
              class="select-chev form-field w-full h-12 px-4 text-[13px] rounded-xl border-0 outline-none bg-white shadow placeholder-gray-400 sm:px-4 lg:px-6 sm:text-sm mt-1">
              <option value="">Seleccione el programa de formación</option>
              <?php if (empty($programas)): ?>
                <option disabled>No se encontraron programas activos</option>
                <!-- programas: <?= htmlspecialchars(json_encode($programas)) ?> -->
              <?php else: ?>
                <?php foreach ($programas as $prog): ?>
                  <option value="<?= htmlspecialchars($prog['id_programa']) ?>">
                    <?= htmlspecialchars($prog['nombre_programa']) ?>
                  </option>
                <?php endforeach; ?>
              <?php endif; ?>
            </select>

            <!-- Select para vincular competencia existente -->
            <div class="relative">
              <select
                id="id_competencia"
                name="id_competencia"
                class="select-chev form-field w-full h-12 px-4 text-[13px] rounded-xl border-0 outline-none bg-white shadow placeholder-gray-400 sm:px-4 lg:px-6 sm:text-sm mt-1">
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

            <!-- BOTÓN PARA ABRIR MODAL DE RAEs ASOCIADAS -->
            <button
              type="button"
              id="btnSeleccionarRaes"
              class="w-full h-10 bg-white border border-gray-300 rounded-lg text-xs sm:text-sm font-medium text-[#00324D] hover:bg-[#f4f4f5] transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
              Seleccionar RAEs de la competencia
            </button>
            <small id="textoResumenRaes" class="block mt-1 text-[11px] text-gray-500 text-left"></small>

            <!-- Campos ocultos -->
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

    <!-- ============== MODAL RAEs POR COMPETENCIA ============== -->
    <div
      id="modalRaes"
      class="fixed inset-0 z-50 hidden"
      role="dialog"
      aria-modal="true"
      aria-labelledby="tituloModalRaes"
    >
      <!-- Backdrop RAEs -->
      <div id="modalRaesBackdrop" class="fixed inset-0 bg-black/40"></div>

      <!-- Contenedor centrado RAEs -->
      <div class="fixed inset-0 flex items-center justify-center p-4">
        <div
          id="modalRaesCard"
          class="bg-white w-full max-w-[420px] sm:max-w-[520px] md:max-w-[560px] rounded-2xl shadow-md border border-[#d8d8d8] px-4 sm:px-6 pt-5 pb-6 mx-3"
        >
          <div class="flex items-start justify-between mb-2">
            <div class="text-left">
              <h3 id="tituloModalRaes" class="text-[1rem] text-[#0c2443] font-semibold">
                RAEs asociadas a la competencia
              </h3>
              <p id="subtituloModalRaes" class="text-xs text-gray-500 mt-1"></p>
            </div>
            <button
              type="button"
              id="btnCerrarModalRaes"
              class="ml-3 -mt-1 text-gray-500 hover:text-gray-700"
              aria-label="Cerrar"
            >
              ✕
            </button>
          </div>

          <div class="border-b border-[#dcdcdc] mb-3"></div>

          <!-- Select all -->
          <div class="flex items-center justify-between mb-2">
            <label class="flex items-center gap-2 text-xs sm:text-sm text-gray-700">
              <input type="checkbox" id="chkRaesTodos" class="rounded border-gray-300">
              <span>Seleccionar todas las RAEs</span>
            </label>
            <span id="contadorRaesSeleccionadas" class="text-[11px] text-gray-500"></span>
          </div>

          <!-- Contenedor de lista de RAEs -->
          <div id="listaRaesModal"
               class="mt-2 max-h-64 overflow-y-auto rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-left text-xs sm:text-sm">
            <!-- Se llena dinámicamente desde JS -->
            <p class="text-gray-500 text-xs">Cargando RAEs...</p>
          </div>

          <!-- Acciones -->
          <div class="mt-4 flex justify-end gap-2">
            <button
              type="button"
              id="btnCancelarRaes"
              class="px-3 py-2 text-xs sm:text-sm rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50"
            >
              Cancelar
            </button>
            <button
              type="button"
              id="btnGuardarRaes"
              class="px-3 py-2 text-xs sm:text-sm rounded-lg bg-[#0b2d5b] text-white font-medium hover:bg-[#082244]"
            >
              Guardar selección
            </button>
          </div>
        </div>
      </div>
    </div>
    <!-- ============== /MODAL RAEs ============== -->

    <script>
      window.BASE_URL = window.BASE_URL || "<?= BASE_URL ?>";
    </script>

    <!-- tipo_instructor se determina en el servidor, no hay select correspondiente -->
    <script src="<?= BASE_URL ?>src/assets/js/landing.js"></script>
    <script src="<?= BASE_URL ?>src/assets/js/formulario_trimestralizacion.js"></script>

    <script>
  document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('#formTrimestralizacion');
    if (!form) return;

    const selComp = form.querySelector('[name="id_competencia"]');
    const raeField = form.querySelector('#id_rae_field');
    const progField = form.querySelector('#id_programa_field');
    const selProg = document.getElementById('id_programa_select');

    function syncCompData() {
      const opt = selComp && selComp.selectedOptions && selComp.selectedOptions[0];
      if (!opt) return;

      // Si no hay programa seleccionado en el select de programas,
      // tomamos el data-programa de la competencia seleccionada
      if (progField && (!selProg || !selProg.value)) {
        progField.value = opt.dataset.programa || '';
      }

      // ❌ LÍNEA ELIMINADA (era la que dañaba id_rae)
      // raeField.value = opt.dataset.rae;

      // ✔ AHORA id_rae_field SOLO LO MANEJA EL MODAL (correcto)
    }

    // Cuando cambie la competencia, sincronizamos programa
    if (selComp) {
      selComp.addEventListener('change', syncCompData);
      syncCompData();
    }

    // Cuando cambie el programa manualmente, lo copiamos al hidden
    if (selProg && progField) {
      selProg.addEventListener('change', function () {
        progField.value = this.value || '';
      });
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

    <!-- 🔹 LÓGICA PARA FILTRAR COMPETENCIAS SEGÚN PROGRAMA -->
    <script>
      (function () {
        const selProg = document.getElementById('id_programa_select');
        const selComp = document.getElementById('id_competencia');
        if (!selProg || !selComp) return;

        function filtrarCompetenciasPorPrograma() {
          const progVal = selProg.value;
          let tieneVisible = false;

          for (const opt of selComp.options) {
            if (opt.value === "") {
              opt.hidden = false;
              opt.disabled = false;
              continue;
            }
            const optProg = opt.dataset.programa ?? "";
            // Si hay programa seleccionado, solo mostramos competencias de ese programa
            const show = progVal !== "" ? (String(optProg) === String(progVal)) : true;
            opt.hidden = !show;
            opt.disabled = !show;
            if (show) tieneVisible = true;
          }

          // Si la competencia seleccionada ya no pertenece al programa filtrado, limpiamos el select
          const selectedOpt = selComp.selectedOptions[0];
          if (selectedOpt && selectedOpt.hidden) {
            selComp.value = "";
          }
        }

        selProg.addEventListener('change', filtrarCompetenciasPorPrograma);
        // Ejecutamos una vez al cargar, por si viene algo seteado
        document.addEventListener('DOMContentLoaded', filtrarCompetenciasPorPrograma);
      })();
    </script>

    <!-- LÓGICA DEL MODAL DE RAEs POR COMPETENCIA -->
   <!-- LÓGICA DEL MODAL DE RAEs POR COMPETENCIA -->
<script>
(function () {
    const BASE_URL = window.BASE_URL || '';
    const API_RAES = (BASE_URL + 'src/controllers/RaeController.php?accion=listar').replace(/\/+$/, '');
    
    const form = document.getElementById('formTrimestralizacion');
    if (!form) return;

    const selComp = document.getElementById('id_competencia');
    const hiddenRaes = document.getElementById('id_rae_field');
    const resumenRaes = document.getElementById('textoResumenRaes');
    const btnRaes = document.getElementById('btnSeleccionarRaes');

    const modalRaes = document.getElementById('modalRaes');
    const backdropRaes = document.getElementById('modalRaesBackdrop');
    const btnCerrarModalRaes = document.getElementById('btnCerrarModalRaes');
    const btnCancelarRaes = document.getElementById('btnCancelarRaes');
    const btnGuardarRaes = document.getElementById('btnGuardarRaes');
    const listaRaesModal = document.getElementById('listaRaesModal');
    const chkRaesTodos = document.getElementById('chkRaesTodos');
    const contadorRaesSeleccionadas = document.getElementById('contadorRaesSeleccionadas');
    const subtituloModalRaes = document.getElementById('subtituloModalRaes');

    if (!selComp || !btnRaes || !modalRaes) return;

    // Toast helper usando SweetAlert
    function toast(msg, type = 'info') {
      if (window.Swal) {
        Swal.fire({
          toast: true,
          position: 'top-end',
          icon: type,
          title: msg,
          showConfirmButton: false,
          timer: 2200,
          timerProgressBar: true
        });
      } else {
        alert(msg);
      }
    }

    function actualizarResumen() {
      const valor = (hiddenRaes.value || '').trim();
      if (!valor) {
        resumenRaes.textContent = 'No hay RAEs seleccionadas.';
        return;
      }
      const partes = valor.split(',').map(v => v.trim()).filter(Boolean);
      if (!partes.length) {
        resumenRaes.textContent = 'No hay RAEs seleccionadas.';
        return;
      }
      resumenRaes.textContent = partes.length === 1
        ? '1 RAE seleccionada.'
        : partes.length + ' RAEs seleccionadas.';
    }

    function abrirModalRaes() {
      modalRaes.classList.remove('hidden');
    }

    function cerrarModalRaes() {
      modalRaes.classList.add('hidden');
    }

    function contarSeleccionadas() {
      const checks = listaRaesModal.querySelectorAll('.chk-rae-modal:checked');
      const cantidad = checks.length;

      contadorRaesSeleccionadas.textContent =
        cantidad === 0 ? '' :
        cantidad === 1 ? '1 RAE seleccionada' :
        cantidad + ' RAEs seleccionadas';
    }

    // 🔥 AUTO-GUARDADO — actualiza hidden en tiempo real
    function actualizarHiddenAuto() {
      const checks = listaRaesModal.querySelectorAll('.chk-rae-modal:checked');
      const ids = Array.from(checks).map(ch => ch.value);
      hiddenRaes.value = ids.join(',');
      actualizarResumen();
    }

    // Seleccionar todas
    function aplicarSeleccionTodos() {
      const checks = listaRaesModal.querySelectorAll('.chk-rae-modal');
      const checked = chkRaesTodos.checked;
      checks.forEach(ch => { ch.checked = checked; });
      contarSeleccionadas();
      actualizarHiddenAuto(); // 🔥 auto-guardado
    }

    async function cargarRaesPorCompetencia(idComp) {
      listaRaesModal.innerHTML = '<p class="text-gray-500 text-xs">Cargando RAEs...</p>';
      chkRaesTodos.checked = false;
      contadorRaesSeleccionadas.textContent = '';

      try {
        const resp = await fetch(API_RAES + '&id_competencia=' + encodeURIComponent(idComp));
        const data = await resp.json();

        const lista = Array.isArray(data) ? data : (data.data || []);
        if (!lista.length) {
          listaRaesModal.innerHTML = '<p class="text-gray-500 text-xs">No hay RAEs asociadas a esta competencia.</p>';
          hiddenRaes.value = "";   // 🔥 limpiar hidden si no hay RAEs
          actualizarResumen();
          return;
        }

        const seleccionadasPrevias = (hiddenRaes.value || '').split(',')
          .map(v => v.trim())
          .filter(Boolean);

        const frag = document.createDocumentFragment();

        lista.forEach((r) => {
          const id = r.id_rae || r.id || r.ID_RAE;
          const codigo = r.codigo_rae || r.codigo || r.codigoRAE || '';
          const desc = r.descripcion || r.descripcion_rae || r.nombre_rae || r.nombre || '';

          if (!id) return;

          const label = document.createElement('label');
          label.className = 'flex items-start gap-2 py-1 border-b border-gray-100 last:border-b-0 cursor-pointer text-[11px] sm:text-xs text-gray-800';

          const input = document.createElement('input');
          input.type = 'checkbox';
          input.value = id;
          input.className = 'mt-[3px] chk-rae-modal rounded border-gray-300';

          if (seleccionadasPrevias.includes(String(id))) {
            input.checked = true;
          }

          const span = document.createElement('span');
          span.innerHTML = (codigo ? ('<strong>' + codigo + '</strong> — ') : '') +
                           (desc || '(sin descripción)');

          label.appendChild(input);
          label.appendChild(span);
          frag.appendChild(label);
        });

        listaRaesModal.innerHTML = '';
        listaRaesModal.appendChild(frag);

        contarSeleccionadas();
        actualizarHiddenAuto(); // 🔥 también al cargar se sincroniza
      } catch (err) {
        console.error(err);
        listaRaesModal.innerHTML = '<p class="text-red-500 text-xs">Error al cargar las RAEs.</p>';
      }
    }

    // ===================================================
    //            EVENTOS PRINCIPALES
    // ===================================================
    function toggleBotonRaes() {
      btnRaes.disabled = !selComp.value;
    }

    toggleBotonRaes();
    actualizarResumen();

    selComp.addEventListener('change', () => {
      toggleBotonRaes();
      hiddenRaes.value = ""; // limpiar RAEs si cambias competencia
      actualizarResumen();
    });

    btnRaes.addEventListener('click', async () => {
      const idComp = selComp.value;
      if (!idComp) {
        toast('Primero selecciona una competencia.', 'warning');
        return;
      }

      const opt = selComp.selectedOptions[0];
      const nombreComp = opt ? (opt.textContent || '').trim() : '';
      subtituloModalRaes.textContent = nombreComp;

      await cargarRaesPorCompetencia(idComp);
      abrirModalRaes();
    });

    // Cerrar modal
    [btnCerrarModalRaes, btnCancelarRaes].forEach(btn => {
      if (btn) btn.addEventListener('click', cerrarModalRaes);
    });

    if (backdropRaes) backdropRaes.addEventListener('click', cerrarModalRaes);

    // Select all
    chkRaesTodos.addEventListener('change', aplicarSeleccionTodos);

    // Checkbox individual — 🔥 auto-guardado
    listaRaesModal.addEventListener('change', (e) => {
      if (e.target.classList.contains('chk-rae-modal')) {
        contarSeleccionadas();
        actualizarHiddenAuto();  // 🔥 cada check actualiza el hidden
      }
    });

    // Botón "Guardar selección" (opcional) — ahora solo cierra
    btnGuardarRaes.addEventListener('click', () => {
      cerrarModalRaes();
    });
})();
</script>


  </body>
</html>
