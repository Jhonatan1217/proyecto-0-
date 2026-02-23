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
    <title>Proyecto Z</title>

    <!-- Fuente Work Sans (el modal la usa) -->
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/fonts.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>src/assets/css/formulario_crear_trimestralizacion.css">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- SweetAlert2 -->
    <script src="<?= BASE_URL ?>src/assets/js/sweetalert2.all.min.js"></script>

    <style>
      #modalCard .form-grid { display: block; }

      @media (min-width: 768px) and (max-width: 1600px) {
        #modalCard .form-grid {
          display: grid;
          grid-template-columns: repeat(2, minmax(0, 1fr));
          column-gap: 0.75rem;
          row-gap: 0.75rem;
        }
        #modalCard .form-grid .field-full { grid-column: span 2; }
      }

      @media (min-width: 1601px) {
        #modalCard .form-grid { display: block; }
        #modalCard .form-grid .field-full { grid-column: auto; }
      }

      @media (max-width: 640px) {
        #modalCard .form-grid .field,
        #modalCard .form-grid .field-full { margin-bottom: 12px; }

        #modalCard select,
        #modalCard input {
          white-space: normal;
          line-height: 1.3;
          font-size: 14px;
          padding-right: 2.5rem;
        }

        #formTrimestralizacion {
          margin-bottom: 20px;
          padding-bottom: 12px;
        }

        #modalWrapperCrear {
          align-items: flex-start;
          padding-top: 1.7rem;
          padding-bottom: 1.7rem;
        }

        #modalCard {
          margin-left: 1rem;
          margin-right: 1rem;
          max-height: calc(100vh - 3rem);
          overflow-y: auto;
          padding-bottom: 25px !important;
        }
      }
    </style>

  </head>
  <body class="flex flex-col min-h-screen font-sans bg-white text-gray-900 relative overflow-x-hidden">

    <!-- Contenido principal -->
    <main class="relative z-10 flex-1 flex flex-col lg:flex-row items-center lg:items-start justify-between gap-10 lg:gap-16 px-6 sm:px-10 lg:px-16 xl:px-24 2xl:px-32 pt-20 sm:pt-24 lg:pt-32 pb-16 max-w-[1440px] mx-auto w-full">

      <!-- Columna izquierda: texto y botones -->
      <div class="flex flex-col items-start max-w-xl lg:max-w-lg xl:max-w-xl w-full">
        <h1 class="text-4xl sm:text-5xl lg:text-6xl xl:text-7xl font-extrabold text-[#39A900] mb-4 leading-tight tracking-tight">
          PROYECTO Z
        </h1>
        <h2 class="text-lg sm:text-xl lg:text-2xl xl:text-3xl font-bold text-[#1a1a2e] mb-8 leading-snug">
          Crea y ajusta horarios en segundos
        </h2>

        <p class="text-sm sm:text-base text-gray-400 leading-relaxed mb-12 max-w-md">
           Gestiona fácilmente las trimestralizaciones, asigna competencias, instructores y horarios de manera rápida y organizada.
          Optimiza la planificación académica centralizando toda la información en un solo lugar, reduce errores manuales y mejora la coordinación entre equipos.
          Visualiza, edita y ajusta la programación en tiempo real para garantizar una distribución eficiente de recursos y un seguimiento claro del avance formativo.
        </p>

        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center">
          <!-- Boton Iniciar Sesion (outline, pill) -->
          <a href="<?= BASE_URL ?>index.php?page=src/views/login"
            class="inline-flex items-center justify-center min-w-[180px] sm:min-w-[200px] px-8 py-3 border border-gray-300 text-sm rounded-full text-[#1a1a2e] font-semibold bg-white hover:bg-gray-50 transition-colors duration-200 cursor-pointer no-underline">
            Iniciar Sesion
          </a>

          <!-- Boton Ver horario (green, pill) -->
          <a href="<?= BASE_URL ?>index.php?page=src/views/register_tables"
            class="inline-flex items-center justify-center min-w-[180px] sm:min-w-[200px] px-8 py-3 border-0 text-sm rounded-full text-white font-semibold bg-[#39A900] hover:bg-[#2d8a00] transition-colors duration-200 cursor-pointer shadow-lg shadow-green-300/40">
            Ver horario
          </a>
        </div>
      </div>

      <!-- Columna derecha: tarjeta Horario Semanal -->
      <div class="w-full lg:w-auto flex-shrink-0 flex justify-center lg:justify-end mt-2 lg:mt-4">
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 px-5 sm:px-7 py-6 sm:py-7 w-full max-w-[560px] lg:max-w-[600px] xl:max-w-[660px]">

          <!-- Cabecera -->
          <div class="flex items-center justify-between mb-5">
            <h3 class="text-sm sm:text-base font-bold text-[#1a1a2e]">Horario Semanal</h3>
            <span class="text-xs text-gray-400 font-medium">Enero 2026</span>
          </div>

          <!-- Tabla de horario -->
          <div class="overflow-x-auto">
            <table class="w-full border-collapse" style="min-width:460px">
              <thead>
                <tr>
                  <th class="pb-3 px-1 text-xs sm:text-sm font-semibold text-[#1a1a2e] text-center w-1/5">Lun</th>
                  <th class="pb-3 px-1 text-xs sm:text-sm font-semibold text-[#1a1a2e] text-center w-1/5">Mar</th>
                  <th class="pb-3 px-1 text-xs sm:text-sm font-semibold text-[#1a1a2e] text-center w-1/5">Mie</th>
                  <th class="pb-3 px-1 text-xs sm:text-sm font-semibold text-[#1a1a2e] text-center w-1/5">Jue</th>
                  <th class="pb-3 px-1 text-xs sm:text-sm font-semibold text-[#1a1a2e] text-center w-1/5">Vie</th>
                </tr>
              </thead>
              <tbody>
                <!-- Fila 1 -->
                <tr>
                  <td class="py-1.5 px-1 text-center">
                    <span class="inline-block w-full px-2 py-2 rounded-lg text-[11px] sm:text-xs font-medium bg-green-50 text-green-700 border border-green-200">Investigacion</span>
                  </td>
                  <td class="py-1.5 px-1 text-center">
                    <span class="inline-block w-full px-2 py-2 rounded-lg text-[11px] sm:text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">Derechos</span>
                  </td>
                  <td class="py-1.5 px-1 text-center">
                    <span class="inline-block w-full px-2 py-2 rounded-lg text-[11px] sm:text-xs font-medium bg-green-50 text-green-700 border border-green-200">Calidad</span>
                  </td>
                  <td class="py-1.5 px-1 text-center">
                    <span class="inline-block w-full px-2 py-2 rounded-lg text-[11px] sm:text-xs font-medium bg-gray-50 text-gray-600 border border-gray-200">Comunicacion</span>
                  </td>
                  <td class="py-1.5 px-1 text-center">
                    <span class="inline-block w-full px-2 py-2 rounded-lg text-[11px] sm:text-xs font-medium bg-gray-50 text-gray-600 border border-gray-200">Finanzas</span>
                  </td>
                </tr>
                <!-- Fila 2 -->
                <tr>
                  <td class="py-1.5 px-1 text-center">
                    <span class="inline-block w-full px-2 py-2 rounded-lg text-[11px] sm:text-xs font-medium bg-green-50 text-green-700 border border-green-200">Ingles</span>
                  </td>
                  <td class="py-1.5 px-1 text-center">
                    <span class="inline-block w-full px-2 py-2 rounded-lg text-[11px] sm:text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">Python</span>
                  </td>
                  <td class="py-1.5 px-1 text-center">
                    <span class="inline-block w-full px-2 py-2 rounded-lg text-[11px] sm:text-xs font-medium bg-gray-50 text-gray-600 border border-gray-200">Negocios</span>
                  </td>
                  <td class="py-1.5 px-1 text-center">
                    <span class="inline-block w-full px-2 py-2 rounded-lg text-[11px] sm:text-xs font-medium bg-green-50 text-green-700 border border-green-200">Analisis</span>
                  </td>
                  <td class="py-1.5 px-1 text-center">
                    <span class="inline-block w-full px-2 py-2 rounded-lg text-[11px] sm:text-xs font-medium bg-gray-50 text-gray-600 border border-gray-200">Gestion</span>
                  </td>
                </tr>
                <!-- Fila 3 -->
                <tr>
                  <td class="py-1.5 px-1 text-center">
                    <span class="inline-block w-full px-2 py-2 rounded-lg text-[11px] sm:text-xs font-medium bg-gray-50 text-gray-500 border border-gray-100">Ingles</span>
                  </td>
                  <td class="py-1.5 px-1 text-center">
                    <span class="inline-block w-full px-2 py-2 rounded-lg text-[11px] sm:text-xs font-medium bg-green-50 text-green-700 border border-green-200">Deporte</span>
                  </td>
                  <td class="py-1.5 px-1 text-center">
                    <span class="inline-block w-full px-2 py-2 rounded-lg text-[11px] sm:text-xs font-medium bg-gray-50 text-gray-600 border border-gray-200">Negocios</span>
                  </td>
                  <td class="py-1.5 px-1 text-center">
                    <span class="inline-block w-full px-2 py-2 rounded-lg text-[11px] sm:text-xs font-medium bg-gray-50 text-gray-600 border border-gray-200">Analisis</span>
                  </td>
                  <td class="py-1.5 px-1 text-center">
                    <span class="inline-block w-full px-2 py-2 rounded-lg text-[11px] sm:text-xs font-medium bg-gray-50 text-gray-600 border border-gray-200">Gestion</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </main>
  </body>
</html>
