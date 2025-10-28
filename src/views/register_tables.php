<?php
// Cargar datos desde la base de datos para los selects
require_once __DIR__ . '/../../config/database.php';

$areas = [];
$zonas = [];
$instructores = [];
$trimestres = [];

try {
    if (isset($conn)) {
        // Áreas
        $s = $conn->prepare("SELECT id_area, nombre_area FROM areas WHERE estado = 1 ORDER BY nombre_area ASC");
        $s->execute();
        $areas = $s->fetchAll(PDO::FETCH_ASSOC);

        // Zonas (si no hay nombre, muestro "Zona X")
        $s = $conn->prepare("SELECT id_zona, id_area FROM zonas WHERE estado = 1 ORDER BY id_zona ASC");
        $s->execute();
        $zonas = $s->fetchAll(PDO::FETCH_ASSOC);

        // Instructores (nombre + tipo)
        $s = $conn->prepare("SELECT nombre_instructor, tipo_instructor FROM instructores WHERE estado = 1 ORDER BY nombre_instructor ASC");
        $s->execute();
        $instructores = $s->fetchAll(PDO::FETCH_ASSOC);

        // Trimestres (listado)
        $s = $conn->prepare("SELECT numero_trimestre, estado FROM trimestre ORDER BY numero_trimestre ASC");
        $s->execute();
        $trimestres = $s->fetchAll(PDO::FETCH_ASSOC);
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
  <link rel="stylesheet" href="<?= BASE_URL ?>src/assets/css/register_tables.css">
  <script src="https://cdn.tailwindcss.com"></script>
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
      <svg xmlns="http://www.w3.org/2000/svg"
        class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 h-4 w-4 lg:h-5 lg:w-5 text-[#00324D]"
        fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
    </div>

    <!-- Selector de Zona -->
    <div class="relative">
      <select id="selectZona" name="id_zona"
        class="appearance-none w-60 lg:w-72 xl:w-80 2xl:w-96 px-6 py-2 border border-gray-400 text-sm lg:text-base xl:text-lg rounded-md text-[#00324D] font-bold bg-white hover:bg-gray-100 transition-colors duration-200 outline-none cursor-pointer pr-10">
        <option value="" class="text-[#00324D]" selected hidden>SELECCIONE LA ZONA</option>
      </select>
      <svg xmlns="http://www.w3.org/2000/svg"
        class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 h-4 w-4 lg:h-5 lg:w-5 text-[#00324D]"
        fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
    </div>
  </div>

  <!-- Botón de crear nueva trimestralización -->
  <button id="btnAbrirModal" 
    class="flex items-center justify-center gap-2 w-60 lg:w-72 xl:w-80 2xl:w-96 px-6 py-2 text-white font-semibold text-sm lg:text-base rounded-md bg-[#00324D] hover:bg-[#004a70] transition-colors duration-200 shadow-md">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
      stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M12 5v14M5 12h14" />
    </svg>
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
      <button onclick="mostrarModalEliminar()" class="bg-[#00324D] text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
        Eliminar Trimestralización
      </button>

      <button id="btn-actualizar" class="bg-[#00324D] text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
        Actualizar Trimestralización
      </button>

      <button onclick="descargarPDF()" class="bg-[#00324D] text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition flex items-center justify-center">
        Descargar PDF
        <img src="<?= BASE_URL ?>src/assets/img/descargar.png" class="ml-2 w-5 h-5" alt="descargar">
      </button>
    </div>
  </main>

  <!-- Modal Eliminar -->
  <div id="modalEliminar" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-3xl shadow-2xl p-8 max-w-2xl w-11/12 border-4 border-red-600">
      <div class="flex justify-center mb-4">
        <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" class="w-16 h-16">
          <path d="M50 10 L90 80 L10 80 Z" fill="none" stroke="#dc2626" stroke-width="6" stroke-linejoin="round"/>
          <circle cx="50" cy="65" r="3" fill="#dc2626"/>
          <line x1="50" y1="35" x2="50" y2="55" stroke="#dc2626" stroke-width="6" stroke-linecap="round"/>
        </svg>
      </div>
      <h2 class="text-2xl font-bold text-center mb-8 text-gray-900">
        ¿Estás seguro de querer eliminar la trimestralización?
      </h2>
      <div class="flex gap-6 justify-center">
        <button onclick="confirmarEliminar()" class="bg-green-600 hover:bg-green-700 text-white font-bold text-xl px-10 py-3 rounded-xl transition shadow-lg">
          Aceptar
        </button>
        <button onclick="cerrarModal()" class="bg-red-600 hover:bg-red-700 text-white font-bold text-xl px-10 py-3 rounded-xl transition shadow-lg">
          Cancelar
        </button>
      </div>
    </div>
  </div>

  <script>const BASE_URL = "<?= BASE_URL ?>";</script>
  <script src="<?= BASE_URL ?>src/assets/js/registerTables.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

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
            <!-- AREA (desde DB) -->
            <select name="area" id="id_area" 
              class="select-chev form-field w-full h-12 px-4 text-[13px] rounded-xl border-0 outline-none bg-white shadow placeholder-gray-400 sm:px-4 lg:px-6 sm:text-sm">
              <option value="">Seleccione el area a la que pertenece la ficha</option>
              <?php foreach ($areas as $a): ?>
                <option value="<?= htmlspecialchars($a['id_area']) ?>"><?= htmlspecialchars($a['nombre_area']) ?></option>
              <?php endforeach; ?>
            </select>

            <!-- ZONA (desde DB) -->
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

            <!-- TRIMESTRE (desde DB) -->
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
                
              <!-- INSTRUCTOR (desde DB) -->
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

            <textarea name="descripcion" id="descripcion" rows="4" placeholder="Diligencie la competencia aquí" 
              class="form-field form-field--textarea w-full min-h-[90px] px-4 pr-12 text-[13px] py-3 rounded-xl border-0 outline-none bg-white resize-none shadow placeholder-gray-400 sm:px-4 lg:px-6 sm:text-sm"></textarea>

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
        const BASE_URL = "<?= BASE_URL ?>";
    </script>

    <!-- tipo_instructor se determina en el servidor, no hay select correspondiente -->
    <script src="<?= BASE_URL ?>src/assets/js/landing.js"></script>
    <script src="<?= BASE_URL ?>src/assets/js/formulario_trimestralizacion.js"></script>
    <script>
      (function(){
        const selArea = document.getElementById('id_area');
        const selZona = document.getElementById('id_zona');
        if (!selArea || !selZona) return;

        function filterZonas() {
          const areaVal = selArea.value;
          let hasVisible = false;

          for (const opt of selZona.options) {
            if (opt.value === "") { // always keep placeholder visible
              opt.hidden = false;
              opt.disabled = false;
              continue;
            }
            const optArea = opt.dataset.area ?? "";
            // Si hay un área seleccionada, mostrar sólo zonas con esa área.
            // Si no hay área seleccionada, mostrar todas las zonas.
            const show = areaVal !== "" ? (String(optArea) === String(areaVal)) : true;
            opt.hidden = !show;
            opt.disabled = !show;
            if (show) hasVisible = true;
          }

          // Si la zona actualmente seleccionada queda oculta, limpiarla
          const selectedOpt = selZona.selectedOptions[0];
          if (selectedOpt && selectedOpt.hidden) selZona.value = "";
        }

        selArea.addEventListener('change', filterZonas);
        // Ejecutar una vez al cargar para sincronizar (útil si el formulario se reutiliza)
        document.addEventListener('DOMContentLoaded', filterZonas);
      })();
    </script>
</body>
</html>
