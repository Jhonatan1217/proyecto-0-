<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Proyecto Z</title>

    <!-- Fuente Work Sans (el modal la usa) -->
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/fonts.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>src/assets/css/formulario_crear_trimestralizacion.css">

    <!-- SweetAlert2 -->
    <script src="<?= BASE_URL ?>src/assets/js/sweetalert2.all.min.js"></script>

  </head>
  <body class="flex flex-col min-h-screen font-sans text-center bg-white text-gray-900">
    <!-- Contenido principal -->
    <main class="flex flex-col items-center mt-20 flex-1 px-4 lg:px-8 xl:px-16 2xl:px-32">
      <h1 class="text-4xl sm:text-5xl lg:text-6xl xl:text-7xl 2xl:text-8xl font-bold text-[#39A900] mb-2">PROYECTO Z</h1>
      <p class="text-sm sm:text-base lg:text-lg xl:text-xl 2xl:text-2xl mb-8">Crea y ajusta horarios en segundos</p>

      <div class="flex flex-col gap-3 lg:gap-4 items-center">
        <!-- Botón de crear -->
        <button type="button" id="btnAbrirModal"
          class="w-60 lg:w-72 xl:w-80 2xl:w-96 px-6 py-2 lg:px-8 lg:py-3 border border-gray-400 text-sm lg:text-base xl:text-lg rounded-md text-[#00324D] font-bold bg-white hover:bg-[#004A70] transition-colors duration-200 outline-none cursor-pointer hover:text-white">
          CREAR TRIMESTRALIZACIÓN
        </button>

        <a href="<?= BASE_URL ?>index.php?page=src/views/register_tables"
          class="block text-center w-60 lg:w-72 xl:w-80 2xl:w-96 px-6 py-2 lg:px-8 lg:py-3 border border-gray-400 text-sm lg:text-base xl:text-lg rounded-md text-[#00324D] font-bold bg-white hover:bg-[#004A70] transition-colors duración-200 outline-none cursor-pointer hover:text-white">
          VISUALIZAR HORARIO
        </a>
      </div>
    </main>
  </body>
</html>
