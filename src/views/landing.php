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

          <!-- Boton Visualizar horario (green, pill) -->
          <a href="<?= BASE_URL ?>index.php?page=src/views/register_tables"
            class="inline-flex items-center justify-center min-w-[180px] sm:min-w-[200px] px-8 py-3 border-0 text-sm rounded-full text-white font-semibold bg-[#39A900] hover:bg-[#2d8a00] transition-colors duration-200 cursor-pointer shadow-lg shadow-green-300/40">
            Visualizar horario
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
