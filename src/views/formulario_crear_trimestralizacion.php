<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Crear Trimestralización</title>

  <!-- Importamos TailwindCSS desde el CDN para usar utilidades rápidas de estilos -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Fuente Work Sans desde Google Fonts para todo el formulario -->
  <link href="https://fonts.googleapis.com/css2?family=Work+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet"/>

  <style>
    /* Configuración base: box-sizing uniforme y tipografía principal */
    * { box-sizing: border-box; }
    body { font-family: "Work Sans", sans-serif; }

    /* Estilo uniforme para los placeholders (gris claro, no transparente) */
    input::placeholder,
    textarea::placeholder,
    select::placeholder { color:#9ca3af; opacity:1; }

    /* Personalización del select: quitamos la flecha nativa y usamos un ícono SVG */
    .select-chev {
      appearance: none;             /* oculta la flecha nativa */
      -webkit-appearance: none;
      -moz-appearance: none;
      background-image:
        url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%238a8f98' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 16px center; /* alejamos la flecha del borde */
      background-size: 16px 16px;
      padding-right: 2.75rem; /* espacio reservado para la flecha */
    }

    /* Estilo especial SOLO para el select del día: reemplaza la flecha por un ícono de calendario */
    .select-cal {
    background-image: url("../assets/img/calendar-1.svg") !important; /* ícono personalizado */
    background-position: right 14px center !important;
    background-size: 18px 18px !important;
    padding-right: 2.85rem !important; /* un poco más de espacio por el ícono */
    }

    /* Compatibilidad: ocultar flecha en IE/Edge antiguo */
    select::-ms-expand { display:none; }

    /* Color del texto dentro de los <select>:
       - gris por defecto
       - negro cuando ya hay una opción seleccionada */
    select { color:#9ca3af !important; } 
    select:has(option:checked:not([value=""])) { color:#111827 !important; }

    /* Ajuste para inputs dentro de flex: evita cortes de texto en pantallas pequeñas */
    .flex-minw-0 > * { min-width: 0; }
  </style>
</head>

<body class="min-h-screen bg-[#f4f7fb] flex items-center justify-center">

  <!-- Contenedor principal con estilo de "card" -->
  <div class="bg-white w-[420px] max-w-[92vw] rounded-2xl shadow-md border border-[#d8d8d8] px-[26px] pt-[28px] pb-[36px]">

    <!-- Título del formulario -->
    <h2 class="text-center text-[1.1rem] mb-[18px] text-[#0c2443] font-semibold border-b border-[#dcdcdc] pb-[6px]">
      CREAR TRIMESTRALIZACIÓN
    </h2>

    <!-- Formulario principal -->
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

      <!-- Selección de nivel (técnico o tecnólogo) -->
      <select name="nivel" required
        class="select-chev w-full h-12 px-4 text-[13px] rounded-xl border-0 outline-none bg-white shadow placeholder-gray-400">
        <option value="">Seleccione el nivel de la ficha</option>
        <option value="tecnico">Técnico</option>
        <option value="tecnologo">Tecnólogo</option>
      </select>

      <!-- Inputs en dos columnas (número de ficha e instructor) -->
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

      <!-- Día de la semana (con ícono de calendario en lugar de flecha) -->
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

      <!-- Horarios: hora de inicio y hora de fin (50/50) -->
      <div class="flex flex-minw-0 gap-3 max-[420px]:flex-col">
        <!-- Hora inicio: generado dinámicamente con PHP -->
        <select name="hora_inicio" required
          class="select-chev basis-1/2 w-full h-12 px-4 text-[13px] rounded-xl border-0 outline-none bg-white shadow placeholder-gray-400">
          <option value="">Hora de inicio</option>
          <?php for ($i = 6; $i <= 22; $i++): ?>
            <option value="<?= $i ?>:00"><?= $i ?>:00</option>
          <?php endfor; ?>
        </select>

        <!-- Hora fin: también generado dinámicamente con PHP -->
        <select name="hora_fin" required
        class="select-chev basis-1/2 w-full h-12 px-4 text-[13px] rounded-xl border-0 outline-none bg-white shadow placeholder-gray-400">
        <option value="">Hora de fin</option>
        <?php for ($i = 7; $i <= 22; $i++): ?>
            <option value="<?= $i ?>:00"><?= $i ?>:00</option>
        <?php endfor; ?>
        </select>
    </div>

    <!-- Selector de color para identificar instructor/horario -->
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

      <!-- Campo de texto largo para escribir la competencia -->
      <textarea name="competencia" rows="4" placeholder="Diligencie la competencia aquí" required
        class="w-full min-h-[90px] px-4 py-3 text-[13px] rounded-xl border-0 outline-none bg-white resize-none shadow placeholder-gray-400"></textarea>

      <!-- Botón de envío del formulario -->
      <button type="submit"
        class="w-full h-12 bg-[#0b2d5b] text-white rounded-lg text-sm font-semibold hover:bg-[#082244] transition-colors">
        GUARDAR TRIMESTRALIZACIÓN
      </button>
    </form>
  </div>
</body>
</html>

//borrar despues del push