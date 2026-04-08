<?php

require_once __DIR__ . '/../../config/database.php';

$id_usuario = $_SESSION['usuario_id'] ?? 0;
$cargo = $_SESSION['usuario_cargo'] ?? '';

$tieneRolEncargado = false;

if ($cargo === 'INSTRUCTOR' && $id_usuario) {
    require_once __DIR__ . '/../models/Usuario.php';
    require_once __DIR__ . '/../../config/database.php';

    $usuarioModel = new Usuario($conn);
    $roles = $usuarioModel->listarRolesFuncionalesPorUsuario($id_usuario);

    foreach ($roles as $r) {
        if (strtoupper($r['nombre_rol']) === 'ENCARGADO_TRIMESTRALIZACION') {
            $tieneRolEncargado = true;
            break;
        }
    }
}

$puedeCrearTrimestralizacion =
    $cargo === 'COORDINADOR' ||
    ($cargo === 'INSTRUCTOR' && $tieneRolEncargado);

$areas        = [];
$zonas        = [];
$instructores = [];
$trimestres   = [];
$programas    = [];
$competencias = [];
$grupos       = [];
$isAuthenticated = isset($_SESSION['usuario_id']);

if (isset($conn)) {
  try {
    $s = $conn->prepare("SELECT id_area, nombre_area FROM areas WHERE estado = 1 ORDER BY nombre_area ASC");
    $s->execute();
    $areas = $s->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    try {
      $s = $conn->prepare("SELECT id_area, nombre_area FROM area WHERE estado = 1 ORDER BY nombre_area ASC");
      $s->execute();
      $areas = $s->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e2) {
      $areas = [];
    }
  }

  try {
    $s = $conn->prepare("SELECT id_zona, id_area FROM zonas WHERE estado = 1 ORDER BY id_zona ASC");
    $s->execute();
    $zonas = $s->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    $zonas = [];
  }

  $instructores = [];
  try {
    $s = $conn->prepare("SELECT id_instructor, nombre_instructor, tipo_instructor FROM instructores WHERE estado = 1 ORDER BY nombre_instructor ASC");
    $s->execute();
    $instructores = $s->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    $instructores = [];
  }
  // Si la tabla instructores existe pero está vacía, antes no se listaba nadie; los datos suelen estar en usuarios.
  if (empty($instructores)) {
    try {
      $s = $conn->prepare("SELECT id_usuario AS id_instructor, nombre_completo AS nombre_instructor, tipo_instructor FROM usuarios WHERE UPPER(TRIM(cargo)) = 'INSTRUCTOR' AND estado = 1 AND COALESCE(es_sistema, 0) = 0 ORDER BY nombre_completo ASC");
      $s->execute();
      $instructores = $s->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e2) {
      $instructores = [];
    }
  }

  try {
    $s = $conn->prepare("SELECT numero_trimestre FROM trimestre WHERE estado = 1 ORDER BY numero_trimestre ASC");
    $s->execute();
    $trimestres = $s->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    try {
      $s = $conn->prepare("SELECT numero_trimestre FROM trimestres WHERE estado = 1 ORDER BY numero_trimestre ASC");
      $s->execute();
      $trimestres = $s->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e2) {
      $trimestres = [];
    }
  }

  try {
    $s = $conn->prepare("SELECT id_programa, nombre_programa FROM programas WHERE estado = 1 ORDER BY nombre_programa ASC");
    $s->execute();
    $programas = $s->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    $programas = [];
  }
  if (empty($programas)) {
    try {
      $s = $conn->prepare("SELECT id_programa, nombre_programa FROM programas ORDER BY nombre_programa ASC");
      $s->execute();
      $programas = $s->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      $programas = [];
    }
  }

  try {
    $s = $conn->prepare("SELECT id_competencia, nombre_competencia, id_programa FROM competencias WHERE estado = 1 ORDER BY nombre_competencia ASC");
    $s->execute();
    $competencias = $s->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    $competencias = [];
  }
  if (empty($competencias)) {
    try {
      $s = $conn->prepare("SELECT id_competencia, nombre_competencia, id_programa FROM competencias ORDER BY nombre_competencia ASC");
      $s->execute();
      $competencias = $s->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      $competencias = [];
    }
  }

  try {
    $s = $conn->prepare("SELECT DISTINCT numero_ficha FROM fichas WHERE numero_ficha IS NOT NULL AND numero_ficha <> '' ORDER BY numero_ficha ASC");
    $s->execute();
    $grupos = $s->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    $grupos = [];
  }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Proyecto Z - Visualización de registro de tablas</title>

  <link rel="stylesheet" href="<?= BASE_URL ?>public/css/output.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>public/css/fonts.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>src/assets/css/formulario_crear_trimestralizacion.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>src/assets/css/register_tables.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>src/assets/css/components/select-styled.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>src/assets/css/components/combobox.css">
  <script src="<?= BASE_URL ?>src/assets/js/sweetalert2.all.min.js"></script>

</head>

<body class="text-center font-sans min-h-screen flex flex-col bg-gray-50 overflow-x-hidden">

<header class="mt-10 text-center w-full max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-10" id="cabecera-trimestralizacion">
  <h1 class="inline-block text-2xl sm:text-3xl font-bold tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600">
    VISUALIZACIÓN DE REGISTRO TRIMESTRALIZACIÓN - ZONA 
    <?php echo isset($_GET['id_zona']) ? htmlspecialchars($_GET['id_zona']) : '—'; ?>
  </h1>
  <h2 class="text-lg sm:text-xl font-semibold text-gray-700 tracking-wide mb-6">
    Sistema de gestión de trimestralización <br> SENA
  </h2>

  <!-- Contenedor principal de selects y botón -->
  <div class="flex flex-col md:flex-row justify-between items-center md:items-end w-full my-6 gap-4 md:gap-6">
    
    <div class="flex flex-col sm:flex-row gap-4 sm:gap-8 w-full md:w-auto">


    <!-- Selector de Modalidad -->
      <div class="w-full sm:w-60">
        <label for="selectModalidad" class="block mb-2 text-sm sm:text-base font-semibold text-[#00324D] tracking-wide text-left">Modalidad</label>
        <select id="selectModalidad" name="id_modalidad"
          class="select-styled w-full" autocomplete="off">
          <option value="" selected hidden>Seleccione la modalidad</option>
          <option value="presencial">Presencial</option>
          <option value="virtual">Virtual</option>
          <option value="mixto">Mixta</option>
        </select>
      </div>

      <!-- Selector de Área (Combobox) -->
      <div id="contenedorAreaFiltro" class="w-full sm:w-60">
        <label for="inputAreaTexto" class="block mb-2 text-sm sm:text-base font-semibold text-[#00324D] tracking-wide text-left">Área</label>
        <div class="custom-combobox w-full">
          <input type="text" id="inputAreaTexto" autocomplete="off" placeholder="Seleccione o escriba el área" class="filter-search-input w-full">
          <div id="panelAreaFiltro" class="custom-combobox-panel hidden">
            <div class="custom-combobox-list"></div>
          </div>
        </div>
        <select id="selectArea" name="id_area" class="hidden" tabindex="-1" aria-hidden="true">
          <option value="">Seleccione el área</option>
        </select>
      </div>

      <!-- Selector de Zona (Combobox) -->
      <div id="contenedorZonaFiltro" class="w-full sm:w-60">
        <label for="inputZonaTexto" class="block mb-2 text-sm sm:text-base font-semibold text-[#00324D] tracking-wide text-left">Zona</label>
        <div class="custom-combobox w-full">
          <input type="text" id="inputZonaTexto" autocomplete="off" placeholder="Seleccione o escriba la zona" class="filter-search-input w-full">
          <div id="panelZonaFiltro" class="custom-combobox-panel hidden">
            <div class="custom-combobox-list"></div>
          </div>
        </div>
        <select id="selectZona" name="id_zona" class="hidden" tabindex="-1" aria-hidden="true">
          <option value="">Seleccione la zona</option>
        </select>
      </div>

      <!-- Input para filtrar por grupo -->
      <div id="contenedorGrupoFiltro" class="w-full sm:w-60 hidden">
        <label for="inputGrupoTexto" class="block mb-2 text-sm sm:text-base font-semibold text-[#00324D] tracking-wide text-left">Filtrar por grupo</label>
        <div class="custom-combobox w-full">
          <input type="text" id="inputGrupoTexto" autocomplete="off" placeholder="Buscar número de grupo" class="filter-search-input w-full">
          <div id="panelGrupoFiltro" class="custom-combobox-panel hidden">
            <div class="custom-combobox-list"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Botón de crear nueva trimestralización (abre el mismo modal que en la landing) -->
    <button id="btnAbrirModal" 
      class="group flex items-center justify-center gap-2
         w-full sm:w-60 lg:w-[230px] xl:w-[230px] 2xl:w-[230px]
         h-11 px-4
         rounded-xl
         bg-[#00324d]
         text-white font-medium tracking-wider
         text-sm sm:text-sm leading-none whitespace-nowrap
         shadow-md hover:shadow-lg
         hover:bg-[#00304D]
         active:scale-[0.98]
         transition-all duration-200
            <?= ($isAuthenticated && $puedeCrearTrimestralizacion) ? '' : 'hidden' ?>
         focus:outline-none focus:ring-2 focus:ring-[#00324d]/20">
        <img class="w-4 h-4 shrink-0" src="<?= BASE_URL ?>src/assets/img/plus.svg" alt="" aria-hidden="true" />
        <span class="leading-none">Nueva trimestralización</span>
    </button>
  </div>
</header>

  <!-- Contenido principal -->
  <main class="flex flex-col items-center flex-grow w-full max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-10 pb-6">
    <section id="tabla-horarios"
  class="hidden w-full shadow-xl rounded-2xl border border-gray-200 p-0 overflow-hidden">
      <table class="w-full text-xs min-w-[900px] sm:text-sm border-separate border-spacing-0">
        <colgroup>
          <col style="width: 130px;">
          <col style="width: calc((100% - 130px)/6);">
          <col style="width: calc((100% - 130px)/6);">
          <col style="width: calc((100% - 130px)/6);">
          <col style="width: calc((100% - 130px)/6);">
          <col style="width: calc((100% - 130px)/6);">
          <col style="width: calc((100% - 130px)/6);">
        </colgroup>
        <thead class="sticky top-0 bg-gray-300 text-gray-700 z-10">
          <tr>
            <th class="border border-gray-400 p-3 sm:p-4 font-semibold text-sm">Hora</th>
            <th class="border border-gray-400 p-3 font-semibold text-sm">Lunes</th>
            <th class="border border-gray-400 p-3 font-semibold text-sm">Martes</th>
            <th class="border border-gray-400 p-3 font-semibold text-sm">Miércoles</th>
            <th class="border border-gray-400 p-3 font-semibold text-sm">Jueves</th>
            <th class="border border-gray-400 p-3 font-semibold text-sm">Viernes</th>
            <th class="border border-gray-400 p-3 font-semibold text-sm">Sábado</th>
          </tr>
        </thead>
        <tbody id="tbody-horarios">
          <tr><td colspan="7" class="p-4 text-gray-500 text-center">Cargando datos…</td></tr>
        </tbody>
      </table>
    </section>

    <div id="empty-state" class="w-full py-16">
      <div class="mx-auto flex max-w-3xl flex-col items-center justify-center text-center">
        <svg
          width="120"
          height="120"
          viewBox="0 0 24 24"
          fill="none"
          class="mb-4 block text-[#00324D] mx-auto"
          xmlns="http://www.w3.org/2000/svg">
          <rect x="3" y="4" width="18" height="17" rx="2" ry="2" stroke="currentColor" stroke-width="1.5"/>
          <line x1="16" y1="2.5" x2="16" y2="5.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
          <line x1="8" y1="2.5" x2="8" y2="5.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
          <line x1="3" y1="9" x2="21" y2="9" stroke="currentColor" stroke-width="1.5"/>
          <path d="M9 14l2 2 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>

        <h3 id="empty-state-title" class="text-lg sm:text-xl font-semibold text-gray-700 mb-2">
          Seleccione un horario
        </h3>
        <p id="empty-state-desc" class="text-sm sm:text-base text-gray-500 max-w-md">
          Elige la modalidad y completa los filtros
          correspondientes para ver el horario.
        </p>
      </div>
    </div> 

    <!-- Botones de acciones -->
    <div id="botones-principales" 
  class="hidden mt-6 mb-6 flex flex-col sm:flex-row flex-wrap justify-end items-stretch gap-3 sm:gap-6 w-full px-2">

      <button onclick="descargarPDF()" 
        class="border border-black bg-white text-black px-6 py-2 rounded-lg transition flex items-center justify-center gap-2 w-full sm:w-auto hover:bg-gray-100 hover:border-gray-800"   style="border: 2px solid #333333c5 !important;">
        <img src="<?= BASE_URL ?>src/assets/img/descargar.png" class="w-5 h-5" alt="descargar" style="filter: brightness(0);">
        Descargar PDF
      </button>

      <?php if ($isAuthenticated): ?>
      <button id="btn-actualizar" class="bg-[#39a900] text-white px-6 py-2 rounded-lg hover:bg-[#4ebe15] transition flex items-center justify-center w-full sm:w-auto">
        Gestionar horas
      </button>

      <button onclick="mostrarModalEliminar()" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition flex items-center justify-center w-full sm:w-auto">
        Limpiar Trimestralización
      </button>

      <button onclick="enviarHorario()" class="hidden bg-[#0a3a57] text-white px-6 py-2 rounded-lg hover:bg-[#00304D] transition flex items-center justify-center w-full sm:w-auto">
        Enviar horario
      </button>
      <?php endif; ?> 
    </div>
  </main>

  <!-- Modal Eliminar (mismo patrón visual que #modalCerrarSesión en header-private) -->
  <div id="modalEliminar" class="modal-perfil fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/40" role="dialog" aria-modal="true" aria-labelledby="tituloModalEliminar">
    <div class="relative z-10 bg-white rounded-2xl shadow-xl max-w-sm w-full max-h-[calc(100vh-2rem)] overflow-y-auto p-6 flex flex-col items-center text-center">
      <img src="<?= BASE_URL ?>src/assets/img/triangle-alert.svg" alt="" class="w-14 h-14 mb-4 shrink-0" aria-hidden="true" />
      <h2 id="tituloModalEliminar" class="modal-perfil-titulo text-gray-900 mb-2">Eliminar trimestralización</h2>
      <p class="text-sm text-gray-600 mb-6">Esta acción eliminará permanentemente el horario actual. No podrás recuperarlo después.</p>
      <div class="flex gap-3 w-full">
        <button type="button" data-close="modalEliminar" class="flex-1 px-4 py-2.5 rounded-lg border border-gray-300 text-gray-700 text-sm font-medium hover:bg-gray-50 transition">Cancelar</button>
        <button type="button" id="btnConfirmarEliminarTrimestral" class="flex-1 px-4 py-2.5 rounded-lg bg-red-600 text-white text-sm font-medium hover:bg-red-700 transition">Aceptar</button>
      </div>
    </div>
  </div>

  <!-- Modal editar horario (mismo patrón que modal-perfil + formulario trimestralización) -->
  <div id="modalEditarHorario" class="modal-perfil fixed inset-0 z-50 hidden items-center justify-center bg-black/40" style="padding: 6rem 1rem;" role="dialog" aria-modal="true" aria-labelledby="tituloModalEditarHorario">
    <div class="relative z-10 mx-auto w-full bg-white rounded-2xl shadow-xl overflow-hidden flex flex-col border border-gray-100" style="max-width:32em;max-height:min(30rem,calc(100vh - 13rem));min-height:0;flex-shrink:0;">
      <div class="modal-perfil-header flex-shrink-0 px-5 pt-4 pb-3 border-b border-gray-200">
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0 pr-2">
            <h2 id="tituloModalEditarHorario" class="modal-perfil-titulo text-gray-900">Editar horario</h2>
            <p id="subtituloModalEditarHorario" class="text-sm text-gray-500 mt-1 truncate"></p>
          </div>
          <button type="button" data-close="modalEditarHorario" class="shrink-0 p-2 rounded-lg text-gray-500 hover:text-gray-800 hover:bg-gray-100 transition" aria-label="Cerrar">✕</button>
        </div>
      </div>
      <div class="px-5 py-3 overflow-y-auto overflow-x-hidden flex-1 min-h-0 text-left overscroll-contain">
        <p id="editHorarioValidation" class="hidden mb-3 text-sm text-red-600 rounded-lg bg-red-50 border border-red-100 px-3 py-2" role="alert"></p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div class="field">
            <label for="editDia" class="block text-xs font-semibold text-gray-800 mb-1">Día</label>
            <select id="editDia" class="js-edit-horario-native select-styled w-full form-field" autocomplete="off"></select>
          </div>
          <div class="field">
            <label for="editFicha" class="block text-xs font-semibold text-gray-800 mb-1">Grupo</label>
            <div class="cell-edit-wrap text-left">
              <select id="editFicha" class="js-edit-horario-combo select-grupo input-enterprise w-full py-2.5 text-sm" autocomplete="off">
                <option value="">Seleccione un grupo</option>
              </select>
            </div>
          </div>
          <div class="field">
            <label for="editHoraInicio" class="block text-xs font-semibold text-gray-800 mb-1">Hora inicio</label>
            <select id="editHoraInicio" class="js-edit-horario-native select-styled w-full form-field" autocomplete="off">
              <option value="">Seleccionar hora</option>
            </select>
          </div>
          <div class="field">
            <label for="editHoraFin" class="block text-xs font-semibold text-gray-800 mb-1">Hora fin</label>
            <select id="editHoraFin" class="js-edit-horario-native select-styled w-full form-field" autocomplete="off">
              <option value="">Seleccionar hora</option>
            </select>
          </div>
          <div class="field">
            <label for="editInstructor" class="block text-xs font-semibold text-gray-800 mb-1">Instructor</label>
            <div class="cell-edit-wrap text-left">
              <select id="editInstructor" class="js-edit-horario-combo select-grupo input-enterprise w-full py-2.5 text-sm" autocomplete="off">
                <option value="">Seleccione un instructor</option>
              </select>
            </div>
          </div>
          <div class="field">
            <label for="editCompetencia" class="block text-xs font-semibold text-gray-800 mb-1">Competencia</label>
            <div class="cell-edit-wrap text-left">
              <select id="editCompetencia" class="js-edit-horario-combo select-grupo input-enterprise w-full py-2.5 text-sm" autocomplete="off">
                <option value="">Seleccione una competencia</option>
              </select>
            </div>
          </div>
          <div class="field sm:col-span-2">
            <span class="block text-xs font-semibold text-gray-800 mb-1">RAEs</span>
            <div id="editRAEs" class="overflow-y-auto border border-gray-200 rounded-xl p-3 text-sm bg-gray-50 text-gray-700" style="max-height:9rem;overscroll-behavior:contain;"></div>
          </div>
          <div class="field sm:col-span-2">
            <label for="editDescripcion" class="block text-xs font-semibold text-gray-800 mb-1">Descripción <span class="font-normal text-gray-500">(opcional)</span></label>
            <textarea id="editDescripcion" rows="3" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:outline-none focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/20" placeholder="Notas adicionales del horario…"></textarea>
          </div>
        </div>
      </div>
      <div class="flex-shrink-0 px-5 py-3 border-t border-gray-200 flex flex-col-reverse sm:flex-row gap-3 sm:justify-end bg-gray-50/90">
        <button type="button" data-close="modalEditarHorario" class="w-full sm:w-auto px-4 py-2.5 rounded-lg border border-gray-300 text-gray-700 text-sm font-medium hover:bg-white transition">Cancelar</button>
        <button type="button" id="btnGuardarEditarHorario" class="w-full sm:w-auto px-4 py-2.5 rounded-lg bg-[#00324d] text-white text-sm font-medium hover:bg-[#00263b] transition">Guardar cambios</button>
      </div>
    </div>
  </div>

  <div id="modalGestionHoras" class="hidden fixed inset-0 z-[70] items-center justify-center px-4 py-6">
    <div class="gh-backdrop absolute inset-0"></div>
    <div class="gh-card relative z-10 w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6">

      <!-- Header -->
      <div class="gh-header">
        <h2 class="gh-title">Gestión de horas</h2>
        <button type="button" id="btnCerrarGestionHoras" class="gh-close" aria-label="Cerrar modal">×</button>
      </div>

      <!-- Tabs -->
      <div class="gh-tabs">
        <button type="button" id="tabGestionHorasInstructores" class="gh-tab is-active">Instructores</button>
        <button type="button" id="tabGestionHorasGrupos" class="gh-tab">Grupos</button>
      </div>

      <!-- Resumen (texto simple) -->
      <div id="gestionHorasResumen" class="gh-resumen"></div>

      <!-- Filtros -->
      <div id="gestionHorasFiltros" class="gh-filtros"></div>

      <!-- Tabla -->
      <div class="gh-table-wrapper">
        <table class="gh-table">
          <thead id="gestionHorasHead"></thead>
          <tbody id="gestionHorasBody"></tbody>
        </table>
      </div>

      <!-- Footer -->
      <div class="gh-footer">
        <button type="button" id="btnIrGestionInstructores" class="rounded-lg border border-gray-400 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">Gestionar usuarios</button>
        <button type="button" id="btnAceptarGestionHoras" class="rounded-lg bg-[#00324d] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#00263b] transition">Aceptar</button>
      </div>
    </div>
  </div>

  <script>
    window.BASE_URL = window.BASE_URL || "<?= BASE_URL ?>";
    window.IS_AUTHENTICATED = <?= $isAuthenticated ? 'true' : 'false' ?>;
  </script>

  <!-- Combobox global (select estilizado) para modalidad en cabecera -->
  <script src="<?= BASE_URL ?>src/assets/js/components/combobox.js"></script>
  <!-- RegisterTables: orden fijo (namespace window.RegisterTables); cargar una sola vez la cadena -->
  <script src="<?= BASE_URL ?>src/assets/js/registerTables/config.js"></script>
  <script src="<?= BASE_URL ?>src/assets/js/registerTables/utils.js"></script>
  <script src="<?= BASE_URL ?>src/assets/js/registerTables/templates.js"></script>
  <script src="<?= BASE_URL ?>src/assets/js/registerTables/gestionHoras.js"></script>
  <script src="<?= BASE_URL ?>src/assets/js/registerTables/areasFiltros.js"></script>
  <script src="<?= BASE_URL ?>src/assets/js/registerTables/grid.js"></script>
  <script src="<?= BASE_URL ?>src/assets/js/registerTables/trimestralizacionData.js"></script>
  <script src="<?= BASE_URL ?>src/assets/js/registerTables/editHorario.js"></script>
  <script src="<?= BASE_URL ?>src/assets/js/registerTables/modalsPdf.js"></script>
  <script src="<?= BASE_URL ?>src/assets/js/registerTables/init.js"></script>
  <script src="<?= BASE_URL ?>src/assets/js/html2canvas.min.js"></script>
  <script src="<?= BASE_URL ?>src/assets/js/jspdf.umd.min.js"></script>

  <!-- ============== MODAL CREAR TRIMESTRALIZACIÓN (Mismo que en la landing) ============== -->
  <div id="modalCrearLanding" class="fixed inset-0 z-40 hidden" role="dialog" aria-modal="true" aria-labelledby="tituloModalCrear">
      <!-- Backdrop -->
      <div id="modalBackdrop" class="fixed inset-0 bg-black/40"></div>

      <!-- Contenedor centrado -->
      <div class="fixed inset-0 flex items-center justify-center p-4 z-50">
        <div
      id="modalCard"
      class="bg-white !w-[480px] rounded-xl shadow-lg border border-gray-300 px-5 py-4 max-h-[90vh] overflow-y-auto" style="width: 480px !important; max-width: 480px !important;">
          <!-- Cabecera con botón cerrar -->
          <div class="flex items-start justify-between mb-2">
            <h2 id="tituloModalCrear" class="text-center w-full text-lg mb-0 text-black font-semibold">
              Crear trimestralización
            </h2>
            <button id="btnCerrarModal" class="ml-3 -mt-2 text-gray-500 hover:text-gray-700" aria-label="Cerrar modal" title="Cerrar" type="button" data-close="true">
              ✕
            </button>
          </div>
          <div class="border-b border-gray-300 mb-3"></div>

          <!-- Formulario -->
          <form id="formTrimestralizacion" action="<?= BASE_URL ?>src/controllers/TrimestralizacionController.php?accion=crear" method="POST" autocomplete="off" class="trimestralizacion-form space-y-0 text-xs">
            
            <!-- GRID -->
            <div class="form-grid">

            <!-- PROGRAMA (combobox + select oculto para filtro de competencias / lógica JS) -->
              <div class="field">
                <label for="id_programa_combo" class="block text-xs font-semibold text-gray-800 mb-1">Programa de formación</label>
                <div class="custom-combobox w-full">
                  <input
                    type="text"
                    id="id_programa_combo"
                    autocomplete="off"
                    placeholder="Seleccione el programa"
                    class="filter-search-input w-full" />
                  <div id="panelProgramaCrear" class="custom-combobox-panel hidden">
                    <div class="custom-combobox-list"></div>
                  </div>
                </div>
                <select id="id_programa_select" class="hidden" tabindex="-1" aria-hidden="true">
                  <option value="">Ingrese el programa de formación</option>
                  <?php if (empty($programas)): ?>
                    <option disabled>Sin datos disponibles</option>
                  <?php else: ?>
                    <?php foreach ($programas as $prog): ?>
                      <option value="<?= htmlspecialchars($prog['id_programa']) ?>">
                        <?= htmlspecialchars($prog['nombre_programa']) ?>
                      </option>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </select>
              </div>

              <!-- INSTRUCTOR -->
               <div class="field">
                <label for="id_instructor_combo" class="block text-xs font-semibold text-gray-800 mb-1">Instructor</label>
                <div class="custom-combobox w-full">
                  <input
                    type="text"
                    id="id_instructor_combo"
                    autocomplete="off"
                    placeholder="Seleccione el instructor"
                    class="filter-search-input w-full" />
                  <div id="panelInstructorCrear" class="custom-combobox-panel hidden">
                    <div class="custom-combobox-list"></div>
                  </div>
                </div>
                <select name="nombre_instructor" id="nombre_instructor" class="hidden" tabindex="-1" aria-hidden="true">
                  <option value="">Seleccione el instructor</option>
                  <?php foreach ($instructores as $ins): ?>
                    <option value="<?= htmlspecialchars($ins['id_instructor'] ?? '') ?>" data-tipo="<?= htmlspecialchars($ins['tipo_instructor'] ?? '') ?>">
                      <?= htmlspecialchars($ins['nombre_instructor']) ?> <?= isset($ins['tipo_instructor']) ? "— " . htmlspecialchars($ins['tipo_instructor']) : "" ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <!-- NUMERO DE FICHA -->
               <div class="field">
                <label for="numero_ficha" class="block text-xs font-semibold text-gray-800 mb-1">Número de grupo</label>
                <div class="custom-combobox w-full">
                  <input
                    type="text"
                    name="numero_ficha"
                    id="numero_ficha"
                    autocomplete="off"
                    placeholder="Seleccione el número de grupo"
                    class="filter-search-input w-full"/>
                  <div id="panelGrupoCrear" class="custom-combobox-panel hidden">
                    <div class="custom-combobox-list"></div>
                  </div>
                </div>
                <datalist id="listaGruposData">
                  <?php foreach ($grupos as $g): ?>
                    <?php if (!empty($g['numero_ficha'])): ?>
                      <option value="<?= htmlspecialchars($g['numero_ficha']) ?>"></option>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </datalist>
              </div>

              <!-- TRIMESTRE -->
              <div class="field">
                <label for="numero_trimestre" class="block text-xs font-semibold text-gray-800 mb-1">Trimestre de grupo</label>
                <select name="numero_trimestre" id="numero_trimestre"
                  class="select-styled w-full form-field" autocomplete="off">
                  <option value="">Seleccione el trimestre que cursa el grupo</option>
                  <?php foreach ($trimestres as $t): ?>
                    <option value="<?= htmlspecialchars($t['numero_trimestre']) ?>">
                      <?= "Trimestre " . htmlspecialchars($t['numero_trimestre']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <input type="hidden" name="modalidad" id="modalidad" value="presencial">

              <!-- AREA + ZONA-->
              <div class="field">
                <div class="flex flex-minw-0 gap-2">
                  <div class="flex-1">
                    <label for="id_area_combo" class="block text-xs font-semibold text-gray-800 mb-1">Área</label>
                    <div class="custom-combobox w-full">
                      <input
                        type="text"
                        id="id_area_combo"
                        autocomplete="off"
                        placeholder="Seleccione el área"
                        class="filter-search-input w-full" />
                      <div id="panelAreaCrear" class="custom-combobox-panel hidden">
                        <div class="custom-combobox-list"></div>
                      </div>
                    </div>
                    <datalist id="listaAreasCombo">
                      <?php foreach ($areas as $a): ?>
                        <option
                          value="<?= htmlspecialchars($a['nombre_area']) ?>"
                          data-id="<?= htmlspecialchars($a['id_area']) ?>"></option>
                      <?php endforeach; ?>
                    </datalist>

                    <select name="area" id="id_area" class="hidden" tabindex="-1" aria-hidden="true">
                      <option value="">seleccione el área</option>
                      <?php foreach ($areas as $a): ?>
                        <option value="<?= htmlspecialchars($a['id_area']) ?>"><?= htmlspecialchars($a['nombre_area']) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <div class="flex-1">
                    <label for="id_zona_combo" class="block text-xs font-semibold text-gray-800 mb-1">Zona</label>
                    <div class="custom-combobox w-full">
                      <input
                        type="text"
                        id="id_zona_combo"
                        autocomplete="off"
                        placeholder="Seleccione la zona"
                        class="filter-search-input w-full"
                        disabled />
                      <div id="panelZonaCrear" class="custom-combobox-panel hidden">
                        <div class="custom-combobox-list"></div>
                      </div>
                    </div>
                    <datalist id="listaZonasCombo"></datalist>

                    <select name="zona" id="id_zona" class="hidden" tabindex="-1" aria-hidden="true">
                      <option value="">seleccione la zona</option>
                      <?php foreach ($zonas as $z): ?>
                        <?php $label = isset($z['id_zona']) ? "Zona " . $z['id_zona'] : "Zona"; ?>
                        <option value="<?= htmlspecialchars($z['id_zona']) ?>" data-area="<?= htmlspecialchars($z['id_area'] ?? '') ?>">
                          <?= htmlspecialchars($label) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
              </div>

              <!-- DÍA SEMANA -->
              <div class="field">
                <label for="dia" class="block text-xs font-semibold text-gray-800 mb-1">Día</label>
                <select name="dia_semana" id="dia" 
                  class="select-styled w-full form-field" autocomplete="off">
                  <option value="">Seleccione fecha de inicio</option>
                  <option value="lunes">Lunes</option>
                  <option value="martes">Martes</option>
                  <option value="miercoles">Miércoles</option>
                  <option value="jueves">Jueves</option>
                  <option value="viernes">Viernes</option>
                  <option value="sabado">Sábado</option>
                </select>
              </div>

              <!-- HORAS -->
              <div class="field-full">
                <div class="flex flex-minw-0 gap-3">
                  <div class="flex-1">
                    <label for="hora_inicio" class="block text-xs font-semibold text-gray-800 mb-1">Hora de inicio</label>
                    <select name="hora_inicio" id="hora_inicio" 
                      class="select-styled w-full form-field" autocomplete="off">
                      <option value="">Seleccione hora de inicio</option>
                    <?php for ($i = 6; $i <= 22; $i++): ?>
                      <option value="<?= $i ?>:00"><?= $i ?>:00</option>
                    <?php endfor; ?>
                  </select>
                  </div>
                  <div class="flex-1">
                    <label for="hora_fin" class="block text-xs font-semibold text-gray-800 mb-1">Hora de fin</label>
                    <select name="hora_fin" id="hora_fin" 
                      class="select-styled w-full form-field" autocomplete="off">
                      <option value="">Seleccione hora de fin</option>
                    <?php for ($i = 7; $i <= 22; $i++): ?>
                      <option value="<?= $i ?>:00"><?= $i ?>:00</option>
                    <?php endfor; ?>
                  </select>
                  </div>
                </div>
              </div>

              <!-- COMPETENCIA -->
              <div class="field-full">
                <label for="id_competencia_combo" class="block text-xs font-semibold text-gray-800 mb-1">Competencia</label>
                <!-- Combobox visible -->
                <div class="custom-combobox w-full">
                  <input
                    type="text"
                    id="id_competencia_combo"
                    autocomplete="off"
                    placeholder="Seleccione la competencia"
                    class="filter-search-input w-full"
                  />
                  <div id="panelCompetenciaCrear" class="custom-combobox-panel custom-combobox-panel-top hidden">
                    <div class="custom-combobox-list"></div>
                  </div>
                </div>
                <!-- Hidden select: retains value/options for all existing JS logic -->
                <select id="id_competencia" name="id_competencia" class="hidden" tabindex="-1" aria-hidden="true">
                  <option value="">Seleccione competencia</option>
                  <?php if (empty($competencias)): ?>
                    <option disabled>Sin datos disponibles</option>
                  <?php else: ?>
                    <?php foreach ($competencias as $comp): ?>
                      <?php $nombreCompFull = (string)($comp['nombre_competencia'] ?? ''); ?>
                      <option
                        value="<?= htmlspecialchars($comp['id_competencia']) ?>"
                        data-programa="<?= htmlspecialchars($comp['id_programa']) ?>">
                        <?= htmlspecialchars($nombreCompFull) ?>
                      </option>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </select>
              </div>

              <!-- Descripción de la competencia (solo informativa, no se guarda) -->
              <div class="field-full">
                <label for="descripcion_competencia" class="block text-xs font-semibold text-gray-800 mb-1">Descripción de la competencia</label>
                <textarea id="descripcion_competencia" name="descripcion_competencia" rows="2" class="w-full min-h-[3.75rem] max-h-[4.5rem] px-3 py-2 text-sm rounded-lg border border-gray-300 outline-none bg-white text-gray-700 resize-none overflow-auto focus:ring-2 focus:ring-[#39A900]/20 focus:border-[#39A900]"></textarea>
              </div>

              <!-- BOTÓN RAEs + CONTADOR -->
              <div class="field-full">
                <div class="flex items-center justify-between gap-3">
                  
                  <!-- Botón a la izquierda -->
                  <button type="button" id="btnSeleccionarRaes" class="flex-1 h-10 bg-white border border-gray-300 rounded-lg text-xs font-medium text-[#00324D] hover:bg-[#f4f4f5] transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    Seleccionar RAEs de la competencia
                  </button>

                  <!-- Contador a la derecha -->
                  <small id="textoResumenRaes" class="text-[15px] text-gray-500 whitespace-nowrap px-3 py-2 bg-gray-50 rounded-md border border-gray-200 text-center">
                    0 seleccionados
                  </small>
                </div>
              </div>

            </div><!-- /form-grid -->

            <!-- Campos ocultos -->
            <input type="hidden" name="id_rae" id="id_rae_field" value="">
            <input type="hidden" name="id_programa" id="id_programa_field" value="">

            <button type="submit" class="w-full h-10 bg-[#0b2d5b] text-white rounded-lg text-sm font-medium
              hover:bg-[#082244] transition-all duration-200 mt-4">
              Guardar trimestralización
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- ============== MODAL RAEs POR COMPETENCIA ============== -->
    <div id="modalRaes" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true" aria-labelledby="tituloModalRaes">
      <!-- Backdrop RAEs -->
      <div id="modalRaesBackdrop" class="fixed inset-0 bg-black/40"></div>

      <!-- Contenedor centrado RAEs -->
      <div class="fixed inset-0 flex items-center justify-center p-4">
        <div id="modalRaesCard" class="bg-white w-full max-w-[420px] sm:max-w-[520px] md:max-w-[560px] rounded-2xl shadow-md border border-[#d8d8d8] px-4 sm:px-6 pt-12 pb-6 mx-3 max-h-[90vh] overflow-y-auto">
          <div class="flex items-start justify-between mb-2 mt-4">
            <div class="text-left">
              <h3 id="tituloModalRaes" class="text-xl text-[#0c2443] font-semibold">
                RAEs asociadas a la competencia
              </h3>
              <p id="subtituloModalRaes" class="text-base text-gray-500 mt-1"></p>
            </div>
            <button type="button" id="btnCerrarModalRaes" class="ml-3 -mt-1 text-gray-500 hover:text-gray-700" aria-label="Cerrar"> ✕ </button>
          </div>

          <div class="border-b border-[#dcdcdc] mb-3"></div>

          <!-- Select all -->
          <div class="flex items-center justify-between mb-2">
            <label class="flex items-center gap-2 text-xs sm:text-sm text-gray-700">
              <input type="checkbox" id="chkRaesTodos" class="rounded border-gray-300 mb-4">
              <span class="text-sm">Seleccionar todas las RAEs</span>
            </label>
            <span id="contadorRaesSeleccionadas" class="text-sm text-gray-500"></span>
          </div>

          <!-- Contenedor de lista de RAEs -->
          <div id="listaRaesModal"
               class="mt-2 max-h-64 overflow-y-auto rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-left text-sm sm:text-base">
            <p class="text-gray-500 text-xs">Cargando RAEs...</p>
          </div>

          <!-- Acciones -->
          <div class="mt-4 flex flex-col sm:flex-row justify-end gap-2">
            <button
              type="button" id="btnCancelarRaes" class="px-3 py-2 text-xs sm:text-sm rounded-lg border border-gray-300 font-semibold hover:bg-gray-50 w-full sm:w-auto">
              Cancelar
            </button>
            <button type="button" id="btnGuardarRaes" class="px-3 py-2 text-xs sm:text-sm rounded-lg bg-[#0b2d5b] text-white font-medium hover:bg-[#082244] w-full sm:w-auto">
              Guardar
            </button>
          </div>
        </div>
      </div>
    </div>
    <!-- ============== /MODAL RAEs ============== -->

    <!-- ============== MODAL DUPLICAR HORARIO ============== -->
    <div id="modalDuplicarHorario" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true" aria-labelledby="tituloModalDuplicar">
      <div id="modalDuplicarBackdrop" class="fixed inset-0 bg-black/40"></div>

      <!-- Contenedor centrado -->
      <div class="fixed inset-0 flex items-center justify-center p-4">
        <div
          class="bg-white w-full max-w-[420px] sm:max-w-[520px] md:max-w-[560px] rounded-2xl shadow-md border border-[#dd8d8] px-4 sm:px-6 pt-10 pb-6 mx-3 max-h-[90vh] overflow-y-auto"
        >
          <!-- Header -->
          <div class="flex items-start justify-between mb-2 mt-4">
            <div class="text-left">
              <h3 id="tituloModalDuplicar" class="text-[1rem] text-[#0c2443] font-semibold">
                ¿Aplicar este horario a otros días?
              </h3>
              <p class="text-xs text-gray-500 mt-1">
                Marca los días en los que también quieres usar este mismo horario.
              </p>
            </div>
            <button type="button" id="btnCerrarModalDuplicar" class="ml-3 -mt-1 text-gray-500 hover:text-gray-700" aria-label="Cerrar"> ✕ </button>
            </div>
            
            <div class="border-b border-[#dcdcdc] mb-3"></div>
          
          <!-- Checklist + select oculto -->
          <div class="mb-4 text-left">
            <label class="block text-xs sm:text-sm text-gray-700 mb-1">
              Selecciona el/los día(s) al que deseas aplicar también este horario:
            </label>

            <!-- 📝 Checklist visible -->
            <div id="checklistDias" class="mt-1 grid grid-cols-2 gap-2 text-xs sm:text-sm">
              <label class="flex items-center gap-2">
                <input type="checkbox" class="chk-dia-duplicar rounded border-gray-300" value="lunes">
                <span>Lunes</span>
              </label>
              <label class="flex items-center gap-2">
                <input type="checkbox" class="chk-dia-duplicar rounded border-gray-300" value="martes">
                <span>Martes</span>
              </label>
              <label class="flex items-center gap-2">
                <input type="checkbox" class="chk-dia-duplicar rounded border-gray-300" value="miercoles">
                <span>Miércoles</span>
              </label>
              <label class="flex items-center gap-2">
                <input type="checkbox" class="chk-dia-duplicar rounded border-gray-300" value="jueves">
                <span>Jueves</span>
              </label>
              <label class="flex items-center gap-2">
                <input type="checkbox" class="chk-dia-duplicar rounded border-gray-300" value="viernes">
                <span>Viernes</span>
              </label>
              <label class="flex items-center gap-2">
                <input type="checkbox" class="chk-dia-duplicar rounded border-gray-300" value="sabado">
                <span>Sábado</span>
              </label>
            </div>

            <!-- Select oculto para que lo use formulario_trimestralizacion.js -->
            <select id="selectDiaDuplicar" multiple class="hidden">
              <option value="lunes">lunes</option>
              <option value="martes">martes</option>
              <option value="miercoles">miercoles</option>
              <option value="jueves">jueves</option>
              <option value="viernes">viernes</option>
              <option value="sabado">sabado</option>
            </select>

            <small id="mensajeErrorDuplicar" class="mt-1 block text-[11px] text-red-500 hidden">
              Debes seleccionar al menos un día diferente al original.
            </small>
          </div>

          <!-- Acciones -->
          <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:justify-end">
            <button type="button" id="btnSoloEsteDia" class="w-full sm:w-auto px-3 py-2 text-xs sm:text-sm rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
              No, solo este día
            </button>
            <button type="button" id="btnDuplicarDia" class="w-full sm:w-auto px-3 py-2 text-xs sm:text-sm rounded-lg bg-[#0b2d5b] text-white font-medium hover:bg-[#082244]">
              Sí, duplicar horario
            </button>
          </div>
        </div>
      </div>
    </div>
    <!-- ============== /MODAL DUPLICAR HORARIO ============== -->

    <script>
      const BASE_URL = "<?= BASE_URL ?>";
    </script>

    <!-- Scripts compartidos: apertura de modal, validaciones, flujo duplicar, etc -->
    <script src="<?= BASE_URL ?>src/assets/js/landing.js"></script>
    <script src="<?= BASE_URL ?>src/assets/js/formulario_trimestralizacion.js"></script>

    <script src="<?= BASE_URL ?>src/assets/js/registerTablesModal.js"></script>

</body>
</html>