<?php
// Cargar datos desde la base de datos para los selects de áreas, zonas, instructores, trimestres, programas y competencias
require_once __DIR__ . '/../../config/database.php';

$areas        = [];
$zonas        = [];
$instructores = [];
$trimestres   = [];
$programas    = [];
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

        // Trimestres (solo activos)
        $s = $conn->prepare("SELECT numero_trimestre, estado FROM trimestre WHERE estado = 1 ORDER BY numero_trimestre ASC");
        $s->execute();
        $trimestres = $s->fetchAll(PDO::FETCH_ASSOC);

        // Programas de formación
        $s = $conn->prepare("
            SELECT id_programa, nombre_programa
            FROM programas
            WHERE estado = 1
            ORDER BY nombre_programa ASC
        ");
        $s->execute();
        $programas = $s->fetchAll(PDO::FETCH_ASSOC);

        // Competencias (SIN descripcion, solo lo que existe en la tabla)
        $s = $conn->prepare("
            SELECT id_competencia, nombre_competencia, id_programa
            FROM competencias
            WHERE estado = 1
            ORDER BY nombre_competencia ASC
        ");
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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Proyecto 0 - Visualización de registro de tablas</title>

  <!-- Fuente Work Sans + estilos del formulario (igual que landing) -->
  <link rel="stylesheet" href="<?= BASE_URL ?>public/css/fonts.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>src/assets/css/formulario_crear_trimestralizacion.css">

  <!-- Estilos propios de la vista de tablas -->
  <link rel="stylesheet" href="<?= BASE_URL ?>src/assets/css/register_tables.css">

  <!-- SweetAlert2 (usado por el formulario y por otros flujos) -->
  <script src="<?= BASE_URL ?>src/assets/js/sweetalert2.all.min.js"></script>

  <!-- Layout del formulario del modal: 1 col (móvil), 2 cols en portátiles, apilado en monitores grandes -->
  <style>
    #modalCard .form-grid {
      display: block;
    }

    @media (min-width: 768px) and (max-width: 1600px) {
      #modalCard .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        column-gap: 0.75rem;
        row-gap: 0.75rem;
      }
      #modalCard .form-grid .field-full {
        grid-column: span 2;
      }
    }

    @media (min-width: 1601px) {
      #modalCard .form-grid {
        display: block;
      }
      #modalCard .form-grid .field-full {
        grid-column: auto;
      }
    }
  </style>
</head>

<body class="text-center font-sans min-h-screen flex flex-col bg-gray-50">

<header class="mt-10 text-center" id="cabecera-trimestralizacion">
  <h1 class="text-3xl font-bold text-gray-900">
    VISUALIZACIÓN DE REGISTRO TRIMESTRALIZACIÓN - ZONA 
    <?php echo isset($_GET['id_zona']) ? htmlspecialchars($_GET['id_zona']) : '—'; ?>
  </h1>
  <h2 class="text-xl text-gray-700 mb-6">
    Sistema de gestión de trimestralización <br> SENA
  </h2>

  <!-- Contenedor principal de selects y botón -->
  <div class="flex justify-between items-center w-full px-16 my-6">
    
    <div class="flex gap-8">
      <!-- Selector de Área -->
      <div class="relative">
        <select id="selectArea" name="id_area"
          class="appearance-none w-60 lg:w-72 xl:w-80 2xl:w-96 px-6 py-2 border border-gray-400 text-sm lg:text-base xl:text-lg rounded-md text-[#00324D] font-bold bg-white hover:bg-gray-100 transition-colors duration-200 outline-none cursor-pointer pr-10">
          <option value="" class="text-[#00324D]" selected hidden>SELECCIONE EL ÁREA</option>
        </select>
        <img 
          src="<?= BASE_URL ?>src/assets/img/chevron-down.svg" 
          alt="arrow" 
          class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 opacity-70"
        />
      </div>

      <!-- Selector de Zona -->
      <div class="relative">
        <select id="selectZona" name="id_zona"
          class="appearance-none w-60 lg:w-72 xl:w-80 2xl:w-96 px-6 py-2 border border-gray-400 text-sm lg:text-base xl:text-lg rounded-md text-[#00324D] font-bold bg-white hover:bg-gray-100 transition-colors duration-200 outline-none cursor-pointer pr-10">
          <option value="" class="text-[#00324D]" selected hidden>SELECCIONE LA ZONA</option>
        </select>
        <img 
          src="<?= BASE_URL ?>src/assets/img/chevron-down.svg" 
          alt="arrow" 
          class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 opacity-70"
        />
      </div>
    </div>

    <!-- Botón de crear nueva trimestralización (abre el mismo modal que en la landing) -->
    <button id="btnAbrirModal" 
      class="flex items-center justify-center gap-2 w-60 lg:w-72 xl:w-80 2xl:w-96 px-6 py-2 text-white font-semibold text-sm lg:text-base rounded-md bg-[#0a3a57] hover:bg-[#00304D] transition-colors duration-200 shadow-md">
        <img class="w-5 h-5" src="<?= BASE_URL ?>src/assets/img/plus.svg" />
      Nueva trimestralización
    </button>
  </div>
</header>

  <!-- Contenido principal -->
  <main class="flex flex-col items-center flex-grow">
    <section id="tabla-horarios" class="w-11/12 max-h-[500px] overflow-y-auto shadow-lg bg-white rounded-xl">
      <table class="border border-gray-700 border-collapse w-full text-sm">
        <thead class="sticky top-0 bg-green-600 text-white z-10">
          <tr>
            <th class="border border-gray-700 p-3">Hora</th>
            <th class="border border-gray-700 p-2">Lunes</th>
            <th class="border border-gray-700 p-2">Martes</th>
            <th class="border border-gray-700 p-2">Miércoles</th>
            <th class="border border-gray-700 p-2">Jueves</th>
            <th class="border border-gray-700 p-2">Viernes</th>
            <th class="border border-gray-700 p-2">Sábado</th>
          </tr>
        </thead>
        <tbody id="tbody-horarios">
          <tr><td colspan="7" class="p-4 text-gray-500">Cargando datos...</td></tr>
        </tbody>
      </table>
    </section>

    <!-- Botones de acciones -->
    <div id="botones-principales" class="mt-6 mb-6 flex justify-center gap-6">
      <button onclick="mostrarModalEliminar()" class="bg-[#0a3a57] text-white px-6 py-2 rounded-lg hover:bg-[#00304D] transition">
        Limpiar Trimestralización
      </button>

      <button id="btn-actualizar" class="bg-[#0a3a57] text-white px-6 py-2 rounded-lg hover:bg-[#00304D] transition">
        Actualizar Trimestralización
      </button>

      <button onclick="descargarPDF()" class="bg-[#0a3a57] text-white px-6 py-2 rounded-lg hover:bg-[#00304D] transition flex items-center justify-center">
        Descargar PDF
        <img src="<?= BASE_URL ?>src/assets/img/descargar.png" class="ml-2 w-5 h-5" alt="descargar">
      </button>
    </div>
  </main>

  <!-- Modal Eliminar -->
  <div id="modalEliminar" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-3xl shadow-2xl p-8 max-w-2xl w-11/12 border-4 border-red-600">
      <div class="flex justify-center mb-4">
        <img class="w-16 h-16" src="<?= BASE_URL ?>src/assets/img/triangle-alert.svg" />
      </div>
      <h2 class="text-2xl font-bold text-center mb-8 text-gray-900">
        ¿Estás seguro de querer limpiar la trimestralización?
      </h2>
      <div class="flex gap-6 justify-center">
        <button onclick="confirmarEliminar()" class="bg-[#4ebe15] hover:bg-[#39A900] text-white font-bold text-xl px-10 py-3 rounded-xl transition shadow-lg">
          Aceptar
        </button>
        <button onclick="cerrarModal()" class="bg-red-600 hover:bg-red-700 text-white font-bold text-xl px-10 py-3 rounded-xl transition shadow-lg">
          Cancelar
        </button>
      </div>
    </div>
  </div>

  <script>
    window.BASE_URL = window.BASE_URL || "<?= BASE_URL ?>";
  </script>

  <!-- Scripts de la vista de tablas (SOLO una vez registerTables.js para evitar el error de urlParams) -->
  <script src="<?= BASE_URL ?>src/assets/js/registerTables.js"></script>
  <script src="<?= BASE_URL ?>src/assets/js/html2canvas.min.js"></script>
  <script src="<?= BASE_URL ?>src/assets/js/jspdf.umd.min.js"></script>

  <!-- ============== MODAL CREAR TRIMESTRALIZACIÓN (Mismo que en la landing) ============== -->
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
            
            <!-- GRID -->
            <div class="form-grid">

              <!-- AREA -->
              <div class="field">
                <select name="area" id="id_area" 
                  class="select-chev form-field w-full h-12 px-4 text-[13px] rounded-xl border-0 outline-none bg-white shadow placeholder-gray-400 sm:px-4 lg:px-6 sm:text-sm">
                  <option value="">Seleccione el area a la que pertenece la ficha</option>
                  <?php foreach ($areas as $a): ?>
                    <option value="<?= htmlspecialchars($a['id_area']) ?>"><?= htmlspecialchars($a['nombre_area']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <!-- ZONA -->
              <div class="field">
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
              </div>

              <!-- NIVEL FICHA -->
              <div class="field">
                <select name="nivel_ficha" 
                  class="select-chev form-field w-full h-12 px-4 text-[13px] rounded-xl border-0 outline-none bg-white shadow placeholder-gray-400 sm:px-4 lg:px-6 sm:text-sm">
                  <option value="">Seleccione el nivel de la ficha</option>
                  <option value="tecnico">Tecnico</option>
                  <option value="tecnologo">Tecnologo</option>
                </select>
              </div>

              <!-- TRIMESTRE -->
              <div class="field">
                <select name="numero_trimestre" 
                  class="select-chev form-field w-full h-12 px-4 text-[13px] rounded-xl border-0 outline-none bg-white shadow placeholder-gray-400 sm:px-4 lg:px-6 sm:text-sm">
                  <option value="">Seleccione el trimestre que cursa la ficha</option>
                  <?php foreach ($trimestres as $t): ?>
                    <option value="<?= htmlspecialchars($t['numero_trimestre']) ?>">
                      <?= "Trimestre " . htmlspecialchars($t['numero_trimestre']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <!-- NÚMERO FICHA + INSTRUCTOR -->
              <div class="field-full">
                <div class="flex flex-minw-0 gap-3 flex-col sm:flex-row lg:flex-row">
                  <input type="text" name="numero_ficha" id="numero_ficha" placeholder="Número de la ficha" 
                    class="form-field basis-1/2 w-full h-12 px-4 pr-12 text-[13px] rounded-xl border-0 outline-none bg-white shadow placeholder-gray-400 sm:px-4 lg:px-6 sm:text-sm"/>
                  
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
              </div>

              <!-- DÍA SEMANA -->
              <div class="field">
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
              </div>

              <!-- PROGRAMA -->
              <div class="field">
                <select
                  id="id_programa_select"
                  name="id_programa_select"
                  class="select-chev form-field w-full h-12 px-4 text-[13px] rounded-xl border-0 outline-none bg-white shadow placeholder-gray-400 sm:px-4 lg:px-6 sm:text-sm mt-1">
                  <option value="">Seleccione el programa de formación</option>
                  <?php if (empty($programas)): ?>
                    <option disabled>No se encontraron programas activos</option>
                  <?php else: ?>
                    <?php foreach ($programas as $prog): ?>
                      <option value="<?= htmlspecialchars($prog['id_programa']) ?>">
                        <?= htmlspecialchars($prog['nombre_programa']) ?>
                      </option>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </select>
              </div>

              <!-- HORAS -->
              <div class="field-full">
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
              </div>

              <!-- COMPETENCIA -->
              <div class="field-full">
                <div class="relative">
                  <select
                    id="id_competencia"
                    name="id_competencia"
                    class="select-chev form-field w-full h-12 px-4 text-[13px] rounded-xl border-0 outline-none bg-white shadow placeholder-gray-400 sm:px-4 lg:px-6 sm:text-sm mt-1">
                    <option value="">Seleccione la competencia</option>
                    <?php if (empty($competencias)): ?>
                      <option disabled>No se encontraron competencias activas</option>
                    <?php else: ?>
                      <?php foreach ($competencias as $comp): ?>
                        <option
                          value="<?= htmlspecialchars($comp['id_competencia']) ?>"
                          data-programa="<?= htmlspecialchars($comp['id_programa']) ?>">
                          <?= htmlspecialchars($comp['nombre_competencia']) ?>
                        </option>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </select>
                </div>
              </div>

              <!-- BOTÓN RAEs + CONTADOR -->
              <div class="field-full">
                <div class="flex items-center justify-between gap-3">
                  <button
                    type="button"
                    id="btnSeleccionarRaes"
                    class="flex-1 h-10 bg-white border border-gray-300 rounded-lg text-xs sm:text-sm font-medium text-[#00324D] hover:bg-[#f4f4f5] transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    Seleccionar RAEs de la competencia
                  </button>
                  <small id="textoResumenRaes" class="text-[11px] text-gray-500 whitespace-nowrap px-3 py-2 bg-gray-50 rounded-lg border border-gray-200"></small>
                </div>
              </div>

            </div><!-- /form-grid -->

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
    <!-- ============== /MODAL CREAR ============== -->

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
          class="bg-white w-full max-w-[420px] sm:max-w-[520px] md:max-w-[560px] rounded-2xl shadow-md border border-[#d8d8d8] px-4 sm:px-6 pt-12 pb-6 mx-3"
        >
          <div class="flex items-start justify_between mb-2 mt-4">
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

    <!-- ============== MODAL DUPLICAR HORARIO ============== -->
    <div
      id="modalDuplicarHorario"
      class="fixed inset-0 z-50 hidden"
      role="dialog"
      aria-modal="true"
      aria-labelledby="tituloModalDuplicar"
    >
      <!-- Backdrop -->
      <div id="modalDuplicarBackdrop" class="fixed inset-0 bg-black/40"></div>

      <!-- Contenedor centrado -->
      <div class="fixed inset-0 flex items-center justify-center p-4">
        <div
          class="bg-white w-full max-w-[420px] sm:max-w-[520px] md:max-w-[560px] rounded-2xl shadow-md border border-[#d8d8d8] px-4 sm:px-6 pt-10 pb-6 mx-3"
        >
          <!-- Header -->
          <div class="flex items-start justify-between mb-2 mt-4">
            <div class="text-left">
              <h3 id="tituloModalDuplicar" class="text-[1rem] text-[#0c2443] font-semibold">
                ¿Aplicar este horario a otro día?
              </h3>
              <p class="text-xs text-gray-500 mt-1">
                Puedes duplicar la misma información en un día diferente.
              </p>
            </div>
            <button
              type="button"
              id="btnCerrarModalDuplicar"
              class="ml-3 -mt-1 text-gray-500 hover:text-gray-700"
              aria-label="Cerrar"
            >
              ✕
            </button>
          </div>

          <div class="border-b border-[#dcdcdc] mb-3"></div>

          <!-- Select de día destino -->
          <div class="mb-4 text-left">
            <label for="selectDiaDuplicar" class="block text-xs sm:text-sm text-gray-700 mb-1">
              Selecciona el día al que deseas aplicar también este horario:
            </label>
            <select
              id="selectDiaDuplicar"
              class="select-chev form-field w-full h-10 px-3 text-[13px] rounded-xl border border-gray-200 outline-none bg-white placeholder-gray-400 sm:text-sm"
            >
              <option value="">Seleccione un día</option>
              <option value="lunes">Lunes</option>
              <option value="martes">Martes</option>
              <option value="miercoles">Miércoles</option>
              <option value="jueves">Jueves</option>
              <option value="viernes">Viernes</option>
              <option value="sabado">Sábado</option>
            </select>
            <small id="mensajeErrorDuplicar" class="mt-1 block text-[11px] text-red-500 hidden">
              Debes seleccionar un día diferente al original.
            </small>
          </div>

          <!-- Acciones -->
          <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:justify-end">
            <button
              type="button"
              id="btnSoloEsteDia"
              class="w-full sm:w-auto px-3 py-2 text-xs sm:text-sm rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50"
            >
              No, solo este día
            </button>
            <button
              type="button"
              id="btnDuplicarDia"
              class="w-full sm:w-auto px-3 py-2 text-xs sm:text-sm rounded-lg bg-[#0b2d5b] text-white font-medium hover:bg-[#082244]"
            >
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

    <!-- Filtrar zonas por área -->
    <script>
      (function(){
        const selArea = document.getElementById('id_area');
        const selZona = document.getElementById('id_zona');
        if (!selArea || !selZona) return;

        function filterZonas() {
          const areaVal = selArea.value;

          for (const opt of selZona.options) {
            if (opt.value === "") {
              opt.hidden = false;
              opt.disabled = false;
              continue;
            }
            const optArea = opt.dataset.area ?? "";
            const show = areaVal !== "" ? (String(optArea) === String(areaVal)) : true;
            opt.hidden = !show;
            opt.disabled = !show;
          }

          const selectedOpt = selZona.selectedOptions[0];
          if (selectedOpt && selectedOpt.hidden) selZona.value = "";
        }

        selArea.addEventListener('change', filterZonas);
        document.addEventListener('DOMContentLoaded', filterZonas);
      })();
    </script>

    <!-- Filtrar competencias según programa (igual lógica que la landing, sin descripcion) -->
    <script>
      (function () {
        const selProg = document.getElementById('id_programa_select');
        const selComp = document.getElementById('id_competencia');
        if (!selProg || !selComp) return;

        function filtrarCompetenciasPorPrograma() {
          const progVal = selProg.value;
          let hayCoincidencias = false;

          console.log('📋 Programa seleccionado:', progVal);

          for (const opt of selComp.options) {
            if (opt.value === "") {
              // Placeholder siempre visible
              opt.hidden = false;
              opt.disabled = false;
              continue;
            }

            const optProg = opt.getAttribute('data-programa') ?? "";
            const show = progVal !== "" && (String(optProg) === String(progVal));

            if (show) hayCoincidencias = true;

            console.log(
              '  Competencia:',
              (opt.textContent || '').trim(),
              '| data-programa =', optProg,
              '| coincide:', show
            );

            opt.hidden = !show;
            opt.disabled = !show;
          }

          if (progVal !== "" && !hayCoincidencias) {
            console.warn(
              '[Trimestralización] Ninguna competencia tiene data-programa =',
              progVal,
              '. Revisa la columna id_programa de la tabla competencias.'
            );
          }

          const selectedOpt = selComp.selectedOptions[0];
          if (selectedOpt && selectedOpt.hidden) {
            selComp.value = "";
          }
        }

        selProg.addEventListener('change', filtrarCompetenciasPorPrograma);
        document.addEventListener('DOMContentLoaded', filtrarCompetenciasPorPrograma);
      })();
    </script>

    <!-- Lógica del modal de RAEs por competencia -->
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

        function actualizarHiddenAuto() {
          const checks = listaRaesModal.querySelectorAll('.chk-rae-modal:checked');
          const ids = Array.from(checks).map(ch => ch.value);
          hiddenRaes.value = ids.join(',');
          actualizarResumen();
        }

        function aplicarSeleccionTodos() {
          const checks = listaRaesModal.querySelectorAll('.chk-rae-modal');
          const checked = chkRaesTodos.checked;
          checks.forEach(ch => { ch.checked = checked; });
          contarSeleccionadas();
          actualizarHiddenAuto();
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
              hiddenRaes.value = "";
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
            actualizarHiddenAuto();
          } catch (err) {
            console.error(err);
            listaRaesModal.innerHTML = '<p class="text-red-500 text-xs">Error al cargar las RAEs.</p>';
          }
        }

        function toggleBotonRaes() {
          btnRaes.disabled = !selComp.value;
        }

        toggleBotonRaes();
        actualizarResumen();

        selComp.addEventListener('change', () => {
          toggleBotonRaes();
          hiddenRaes.value = "";
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

        [btnCerrarModalRaes, btnCancelarRaes].forEach(btn => {
          if (btn) btn.addEventListener('click', cerrarModalRaes);
        });

        if (backdropRaes) backdropRaes.addEventListener('click', cerrarModalRaes);

        chkRaesTodos.addEventListener('change', aplicarSeleccionTodos);

        listaRaesModal.addEventListener('change', (e) => {
          if (e.target.classList.contains('chk-rae-modal')) {
            contarSeleccionadas();
            actualizarHiddenAuto();
          }
        });

        btnGuardarRaes.addEventListener('click', () => {
          cerrarModalRaes();
        });
    })();
    </script>

</body>
</html>
