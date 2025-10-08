<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Document</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Work+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="src/assets/css/formulario_crear_trimestralizacion.css">
</head>

<body class="flex flex-col min-h-screen font-sans text-center bg-white text-gray-900">
  <!-- Barra superior -->
  <header class="flex flex-col lg:flex-row lg:items-center lg:justify-between px-4 lg:px-8 xl:px-16 py-4 border-b gap-4 lg:gap-0">
    <img src="src/assets/img/logoSena.png" alt="SENA Logo" class="h-10 lg:h-12 xl:h-14 2xl:h-16 mx-auto lg:mx-0" />
    <nav class="flex flex-col lg:flex-row items-center gap-3 lg:gap-6 text-sm lg:text-base xl:text-lg 2xl:text-xl">
      <a href="index.php?page=landing" class="hover:text-[#39A900] font-semibold transition-colors duration-200">Inicio</a>

      <!-- Botón que abre el modal -->
      <a
        href="index.php?page=formulario_crear_trimestralizacion"
        id="btnAbrirModalHeader"
        class="border border-gray-400 px-4 py-1.5 lg:px-6 lg:py-2 text-sm lg:text-base xl:text-lg rounded-md text-[#00324D] hover:bg-[#004A70] font-bold hover:text-white transition-colors duration-200">
        CREAR TRIMESTRALIZACIÓN
      </a>
    </nav>
  </header>

  <!-- ============== MODAL CREAR TRIMESTRALIZACIÓN (importado) ============== -->
  <div
    id="modalCrearHeader"
    class="fixed inset-0 z-40 hidden"
    role="dialog"
    aria-modal="true"
    aria-labelledby="tituloModalCrear"
  >
    <!-- Backdrop -->
    <div id="modalBackdrop" class="fixed inset-0 bg-black/40"></div>

    <!-- Contenedor centrado -->
    <div class="fixed inset-0 flex items-center justify-center p-4">
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
          >
            ✕
          </button>
        </div>
        <div class="border-b border-[#dcdcdc] mb-[12px]"></div>

        <!-- Formulario del modal -->
        <form action="guardar_trimestralizacion.php" method="POST" class="trimestralizacion-form space-y-3">
          <!-- Selección de zona -->
          <select name="zona" required
            class="select-chev w-full h-12 px-4 text-[13px] rounded-xl border-0 outline-none bg-white shadow placeholder-gray-400">
            <option value="">Seleccione la zona a la que pertenece la ficha</option>
            <option value="zona1">Zona 1</option>
            <option value="zona2">Zona 2</option>
            <option value="zona3">Zona 3</option>
            <option value="zona5">Zona 5</option>
            <option value="zona6">Zona 6</option>
          </select>

          <!-- Selección de nivel -->
          <select name="nivel" required
            class="select-chev w-full h-12 px-4 text-[13px] rounded-xl border-0 outline-none bg-white shadow placeholder-gray-400">
            <option value="">Seleccione el nivel de la ficha</option>
            <option value="tecnico">Técnico</option>
            <option value="tecnologo">Tecnólogo</option>
          </select>

          <!-- Dos columnas -->
          <div class="flex flex-minw-0 gap-3 max-[420px]:flex-col">
            <input type="text" name="numero_ficha" placeholder="Número de la ficha" required
              class="basis-1/2 w-full h-12 px-4 text-[13px] rounded-xl border-0 outline-none bg-white shadow placeholder-gray-400"/>
            <input type="text" name="nombre_instructor" placeholder="Nombre del instructor" required
              class="basis-1/2 w-full h-12 px-4 text-[13px] rounded-xl border-0 outline-none bg-white shadow placeholder-gray-400"/>
          </div>

          <!-- Tipo de instructor -->
          <select name="tipo_instructor" required
            class="select-chev w-full h-12 px-4 text-[13px] rounded-xl border-0 outline-none bg-white shadow placeholder-gray-400">
            <option value="">Seleccione el tipo de instructor</option>
            <option value="tecnico">Técnico</option>
            <option value="trasnversal">Transversal</option>
            <option value="clave">clave</option>
          </select>

          <!-- Día con icono calendario -->
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

          <!-- Horarios -->
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

          <!-- Color -->
          <select name="color_instructor" required
            class="select-chev w-full h-12 px-4 text-[13px] rounded-xl border-0 outline-none bg-white shadow placeholder-gray-400">
            <option value="">Seleccione el color</option>
            <option value="#001F3F">Azul marino</option>
            <option value="#DC143C">Rojo carmesí</option>
            <option value="#50C878">Verde esmeralda</option>
            <option value="#FFDB58">Amarillo mostaza</option>
            <option value="#CC5500">Naranja quemado</option>
            <option value="#8A2BE2">Violeta</option>
            <option value="#40E0D0">Turquesa</option>
            <option value="#FF00FF">Rosa fucsia</option>
            <option value="#D9DDDC">Gris perla</option>
            <option value="#7B3F00">Marrón chocolate</option>
            <option value="#F5F5DC">Beige arena</option>
            <option value="#808000">Verde oliva</option>
            <option value="#87CEEB">Azul celeste</option>
            <option value="#2E2E2E">Negro carbón</option>
            <option value="#FFFFF0">Blanco marfil</option>
            <option value="#722F37">Rojo vino</option>
            <option value="#003B46">Azul petróleo</option>
            <option value="#98FF98">Verde menta</option>
            <option value="#FFF44F">Amarillo limón</option>
            <option value="#C8A2C8">Lila</option>
            <option value="#FFD700">Dorado</option>
            <option value="#C0C0C0">Plateado</option>
            <option value="#FF7F50">Coral</option>
            <option value="#00FFFF">Cian</option>
            <option value="#FF00A8">Magenta</option>
          </select>

          <!-- Competencia -->
          <textarea name="competencia" rows="4" placeholder="Diligencie la competencia aquí" required
            class="w-full min-h-[90px] px-4 py-3 text-[13px] rounded-xl border-0 outline-none bg-white resize-none shadow placeholder-gray-400"></textarea>

          <!-- Guardar -->
          <button type="submit"
            class="w-full h-12 bg-[#0b2d5b] text-white rounded-lg text-sm font-semibold hover:bg-[#082244] transition-colors">
            GUARDAR TRIMESTRALIZACIÓN
          </button>
        </form>
      </div>
    </div>
  </div>
  <!-- ============== /MODAL ============== -->

  <script src="src/assets/js/header.js"></script>
</body>
</html>
