<?php /* views/gestionSolicitudes.php */ ?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<main class="px-6 py-8 min-h-[calc(100vh-80px)]">

  <div class="max-w-6xl mx-auto">

    <!-- Título -->
    <h1 class="text-4xl font-extrabold text-[#39A900]">
      Solicitudes
    </h1>

    <p class="text-gray-600 mt-2 mb-8">
      Administra las solicitudes de horario
    </p>

    <!-- Card principal -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">

    <!-- Filtros -->
    <div class="flex gap-3 flex-wrap items-center mb-6">
        
        <!-- Botón Todas -->
        <button class="filter-btn px-6 py-2 text-sm font-medium rounded-md shadow-sm boton-todos"
            data-estado="Todas"
            style="background-color: #e0e0e0; color: #5a5a5a;">
            Todas
        </button>
        
        <!-- Botón Pendiente -->
        <button class="filter-btn px-6 py-2 text-sm font-medium rounded-md shadow-sm boton-pendiente"
            data-estado="Pendiente"
            style="background-color: #ffe8c0; color: #7C4D03;">
            Pendientes
        </button>
        
        <!-- Botón Aprobado -->
        <button class="filter-btn px-6 py-2 text-sm font-medium rounded-md shadow-sm boton-aprobado"
            data-estado="Aprobada"
            style="background-color: #C5E7B5; color: #1E5E3C;">
            Aprobadas
        </button>

        <!-- Botón Devuelto -->
        <button class="filter-btn px-6 py-2 text-sm font-medium rounded-md shadow-sm boton-devuelto"
            data-estado="Devuelto"
            style="background-color: #ffc1bc; color: #9B2C2C;">
            Devueltas
        </button>

    </div>

    <style>
        /* Estilos hover existentes */
        .boton-todos:hover {
            background-color: #9E9E9E !important;
            color: #FFFFFF !important;
        }
        .boton-pendiente:hover {
            background-color: #E0A43C !important;
            color: #FFFFFF !important;
        }
        .boton-aprobado:hover {
            background-color: #77a863 !important;
            color: #FFFFFF !important;
        }
        .boton-devuelto:hover {
            background-color: rgb(194, 114, 109) !important;
            color: #FFFFFF !important;
        }

        /* Estilos para botón activo - borde notorio y sombra */
        .boton-activo {
            border: 1.5px solid #2C3E50 !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3), 0 4px 6px -2px rgba(0, 0, 0, 0.2) !important;
            transform: scale(1.02) !important;
            font-weight: 700 !important;
        }

        /* También puedes tener variantes de borde por color de botón si prefieres */
        .boton-todos.boton-activo {
            border-color: #2C3E50 !important;
        }
        .boton-pendiente.boton-activo {
            border-color: #7C4D03 !important;
        }
        .boton-aprobado.boton-activo {
            border-color: #1E5E3C !important;
        }
        .boton-devuelto.boton-activo {
            border-color: #9B2C2C !important;
        }
    </style>

      <!-- Buscador -->
      <div class="mb-6">
        <input 
          type="text"
          id="searchInput"
          placeholder="Buscar por nombre, programa o ID"
          class="w-80 rounded-lg border border-gray-300 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#F6BB54] focus:border-transparent"
        />
      </div>

      <!-- Cuadro más pequeño para la tabla -->
      <div class="border border-gray-300 rounded-lg shadow-sm overflow-hidden max-w-5xl mx-auto">
        
        <!-- Tabla -->
        <div class="overflow-x-auto">
          <table class="w-full text-sm">

            <thead class="bg-gray-100 border-b border-gray-300 text-gray-700">
              <tr>
                <th class="px-4 py-3 font-semibold text-left">ID</th>
                <th class="px-4 py-3 font-semibold text-left">Solicitante</th>
                <th class="px-4 py-3 font-semibold text-left">Solicitud</th>
                <th class="px-4 py-3 font-semibold text-left">Tipo</th>
                <th class="px-4 py-3 font-semibold text-left">Fecha</th>
                <th class="px-4 py-3 font-semibold text-center">Visualizar</th>
              </tr>
            </thead>

            <tbody id="tablaSolicitudes" class="divide-y divide-gray-200 bg-white border-t border-gray-300 bg-gray-50">
            <!-- Filas de ejemplo para probar (borrar cuando conectes backend) -->
            </tbody>

          </table>
        </div>

      </div>
      <!-- Paginación -->
        <div class="px-6 py-10 flex justify-center text-sm text-gray-600 gap-6 ">
          <button class="hover:text-[#F6BB54] font-medium">← Previous</button>
          <span class="text-gray-400">1 …</span>
          <button class="hover:text-[#F6BB54] font-medium">Next →</button>
        </div>

    </div> <!-- Fin del card principal -->

  </div>

</main>

<script>
  window.API_URL = "<?= BASE_URL ?>src/controllers/SolicitudController.php";
</script>

<script src="<?= BASE_URL ?>src/assets/js/gestionarSolicitudes.js?v=2" defer></script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>