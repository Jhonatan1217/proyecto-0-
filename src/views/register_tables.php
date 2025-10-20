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

  <!-- Selector de Zona -->
<!-- 🔹 Selectores de Área y Zona -->
<div class="flex justify-center mt-4 gap-6">
  
  <!-- Selector de Área -->
  <div class="relative inline-block">
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
  <div class="relative inline-block">
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

    <!-- Botones -->
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
</body>
</html>
