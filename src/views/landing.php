<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Proyecto 0</title>
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Fuente Work Sans (el modal la usa) -->
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="<?= BASE_URL ?>src/assets/css/formulario_crear_trimestralizacion.css">

 
  </head>
  <body class="flex flex-col min-h-screen font-sans text-center bg-white text-gray-900">
    <!-- Contenido principal -->
    <main class="flex flex-col items-center mt-20 flex-1 px-4 lg:px-8 xl:px-16 2xl:px-32">
      <h1 class="text-4xl sm:text-5xl lg:text-6xl xl:text-7xl 2xl:text-8xl font-bold text-[#39A900] mb-2">PROYECTO 0</h1>
      <p class="text-sm sm:text-base lg:text-lg xl:text-xl 2xl:text-2xl mb-8">Crea y ajusta horarios en segundos</p>

      <div class="flex flex-col gap-3 lg:gap-4 items-center">
        <!-- Botón de crear (abrirá el modal) -->
        <button type="button" id="btnAbrirModal"
          class="w-60 lg:w-72 xl:w-80 2xl:w-96 px-6 py-2 lg:px-8 lg:py-3 border border-gray-400 text-sm lg:text-base xl:text-lg rounded-md text-[#00324D] font-bold bg-white hover:bg-[#004A70] transition-colors duration-200 outline-none cursor-pointer hover:text-white">
          CREAR TRIMESTRALIZACIÓN
        </button>

        <!-- Menú desplegable -->
         <div class="relative inline-block">
          <select id="zona" name="zona" required
            class="appearance-none w-60 lg:w-72 xl:w-80 2xl:w-96 
              px-6 py-2 lg:px-8 lg:py-3 
              border border-gray-400 text-sm lg:text-base xl:text-lg 
              rounded-md font-bold 
              hover:bg-gray-100 
              transition-colors duration-200 outline-none cursor-pointer pr-10"
            style="color:#00324d;"
          >
            <option value="" hidden !impor>VISUALIZAR ZONA</option>
            <option value="zona1">Zona 1</option>
            <option value="zona2">Zona 2</option>
            <option value="zona3">Zona 3</option>
            <option value="zona5">Zona 5</option>
            <option value="zona6">Zona 6</option>
          </select>

          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 h-4 w-4 lg:h-5 lg:w-5 text-[#00324D]"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </div>

        <div class="relative inline-block text-left">

          <!-- Contenido desplegable -->
          <div
            id="dropdownMenu"
            class="hidden absolute left-0 mt-2 w-60 lg:w-72 xl:w-80 2xl:w-96 bg-white border border-gray-200 rounded-md shadow-lg z-10"
            role="menu"
            aria-labelledby="dropdownButton"
          >
            <?php foreach ($zonas as $zona): ?>
              <a href="?zona=<?php echo urlencode($zona); ?>"
                class="block px-4 py-2 text-sm lg:text-base text-gray-700 hover:bg-gray-100" role="menuitem">
                <?php echo htmlspecialchars($zona); ?>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </main>

    <!-- ============== MODAL CREAR TRIMESTRALIZACIÓN (tu modal importado) ============== -->
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
          class="bg-white w-[420px] max-w-[92vw] rounded-2xl shadow-md border border-[#d8d8d8] px-[26px] pt-[28px] pb-[36px]"
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
          <form action="guardar_trimestralizacion.php" method="POST" class="trimestralizacion-form space-y-3">
            <select name="zona" required
              class="select-chev w-full h-12 px-4 text-[13px] rounded-xl border-0 outline-none bg-white shadow placeholder-gray-400">
              <option value="">Seleccione la zona a la que pertenece la ficha</option>
              <option value="zona1">Zona 1</option>
              <option value="zona2">Zona 2</option>
              <option value="zona3">Zona 3</option>
              <option value="zona5">Zona 5</option>
              <option value="zona6">Zona 6</option>
            </select>

            <select name="nivel" required
              class="select-chev w-full h-12 px-4 text-[13px] rounded-xl border-0 outline-none bg-white shadow placeholder-gray-400">
              <option value="">Seleccione el nivel de la ficha</option>
              <option value="tecnico">Técnico</option>
              <option value="tecnologo">Tecnólogo</option>
            </select>

            <div class="flex flex-minw-0 gap-3 max-[420px]:flex-col">
              <input type="text" name="numero_ficha" placeholder="Número de la ficha" required
                class="basis-1/2 w-full h-12 px-4 text-[13px] rounded-xl border-0 outline-none bg-white shadow placeholder-gray-400"/>
              <input type="text" name="nombre_instructor" placeholder="Nombre del instructor" required
                class="basis-1/2 w-full h-12 px-4 text-[13px] rounded-xl border-0 outline-none bg-white shadow placeholder-gray-400"/>
            </div>

            <select name="tipo_instructor" required
              class="select-chev w-full h-12 px-4 text-[13px] rounded-xl border-0 outline-none bg-white shadow placeholder-gray-400">
              <option value="">Seleccione el tipo de instructor</option>
              <option value="tecnico">Técnico</option>
              <option value="trasnversal">Transversal</option>
              <option value="clave">clave</option>
            </select>

            <select name="dia_semana" required
              class="select-chev select-cal w-full h-12 px-4 text-[13px] rounded-xl border-0 outline-none bg-white shadow placeholder-gray-400">
              <option value="">Seleccione el día</option>
              <option value="lunes">Lunes</option>
              <option value="martes">Martes</option>
              <option value="miercoles">Miércoles</option>
              <option value="jueves">Jueves</option>
              <option value="viernes">Viernes</option>
              <option value="sabado">Sábado</option>
              <option value="domingo">Domingo</option>
            </select>

            <div class="flex flex-minw-0 gap-3 max-[420px]:flex-col">
              <select name="hora_inicio" required
                class="select-chev basis-1/2 w-full h-12 px-4 text-[13px] rounded-xl border-0 outline-none bg-white shadow placeholder-gray-400">
                <option value="">Hora de inicio</option>
                <?php for ($i = 6; $i <= 22; $i++): ?>
                  <option value="<?= $i ?>:00"><?= $i ?>:00</option>
                <?php endfor; ?>
              </select>

              <select name="hora_fin" required
                class="select-chev basis-1/2 w-full h-12 px-4 text-[13px] rounded-xl border-0 outline-none bg-white shadow placeholder-gray-400">
                <option value="">Hora de fin</option>
                <?php for ($i = 7; $i <= 22; $i++): ?>
                  <option value="<?= $i ?>:00"><?= $i ?>:00</option>
                <?php endfor; ?>
              </select>
            </div>

            <textarea name="competencia" rows="4" placeholder="Diligencie la competencia aquí" required
              class="w-full min-h-[90px] px-4 py-3 text-[13px] rounded-xl border-0 outline-none bg-white resize-none shadow placeholder-gray-400"></textarea>

            <button type="submit"
              class="w-full h-12 bg-[#0b2d5b] text-white rounded-lg text-sm font-semibold hover:bg-[#082244] transition-colors">
              GUARDAR TRIMESTRALIZACIÓN
            </button>
          </form>
        </div>
      </div>
    </div>
    <!-- ============== /MODAL ============== -->

    <!-- Script del menú desplegable + modal -->
    <script src="<?= BASE_URL ?>src/assets/js/landing.js"></script>

  </body>
</html>
