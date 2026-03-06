<?php /* views/gestionSolicitudes.php */ ?>
<?php require_once __DIR__ . '/../includes/header-private.php'; ?>

<!-- Agrega esto en el <head> de tu HTML -->
<script src="https://unpkg.com/lucide@latest"></script>

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
<!-- ================= MODAL DETALLE ================= -->
<div id="modalDetalle" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50">
  <div class="bg-white px-60 max-w-2xl rounded-2xl relative shadow-xl border border-gray-300">

   <!-- Cerrar -->
    

    <!-- CONTENIDO -->
    <div class="p-8" style="padding: 3rem !important;">
      
      <!-- Header estilo imagen -->
      <div class="flex items-center justify-between mb-6">

        <div class="flex items-center gap-4">
          <div class="w-60 h-60 rounded-full border flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" 
                width="60" height="60" 
                viewBox="0 0 24 24" 
                fill="none" 
                stroke="currentColor" 
                stroke-width="2" 
                stroke-linecap="round" 
                stroke-linejoin="round" 
                class="text-gray-500">
                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
            </svg>
        </div>

          <div>
            <div class="flex items-center gap-3">
              <h3 id="modalSolicitante" class="text-lg font-semibold"></h3>

              <button onclick="cerrarModal()" class="text-2xl px-30 text-gray-400 hover:text-black">
              &times;
            </button>
            </div>

            <div class="flex items-center gap-3 mb-6">
              <span id="modalCodigo" class="text-gray-500"></span>
              <span id="modalEstado" class=" rounded-full text-sm font-medium"></span>
            </div>
          </div>
        </div>
      </div>

      <!-- Programa -->
      <div class="mb-6">
        <p class="text-gray-500 text-sm">Programa</p>
        <p id="modalPrograma" class="font-medium"></p>
      </div>

      <!-- CONTENIDO DINÁMICO - agregamos padding aquí también por si acaso -->
      <div id="modalContenido" style="padding: 0 !important;"></div>

      <!-- MOTIVO DE DEVOLUCIÓN (solo para estado DEVUELTO) -->
      <div id="motivoDevolucion" class="mt-6 hidden">
        <p class="text-gray-500">Motivo de devolución</p>
        <div id="textoMotivoDevolucion" class="mt-2 p-4 bg-rose-50 border border-rose-200 rounded-lg text-rose-700"></div>
      </div>

      <div class="mt-6">
        <p class="text-gray-500">Fecha solicitud</p>
        <p id="modalFecha" class="font-medium"></p>
      </div>

      <!-- BOTONES DE ACCIÓN (solo para estado PENDIENTE) -->
      <div id="botonesAccion" class="mt-10 flex justify-center gap-10 hidden">
        <button id="btnAprobar" class="px-8 py-3 bg-[#39A900] text-white rounded-xl shadow-md hover:bg-[#2d8a00] transition">
        Aceptar
      </button>

      <button id="btnDevolver" 
              style="background-color: #ce3030; color: white; padding: 0.75rem 2rem; border-radius: 0.75rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); transition: all 0.3s; border: none; cursor: pointer;"
              onmouseover="this.style.backgroundColor='#790604'"
              onmouseout="this.style.backgroundColor='#ce3030'">
          Devolver
      </button>
      </div>
    </div>
  </div>
</div>

<!-- ================= MODAL CONFIRMAR APROBACIÓN ================= -->
<div id="modalConfirmarAprobacion" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50">

  <div class="bg-white w-25 rounded-3xl p-8 relative shadow-2xl border border-gray-200" style="padding: 3rem !important;">

    <!-- Cerrar -->
    <button onclick="cerrarModalConfirmar()" 
            class="absolute top-5 right-6 text-gray-400 hover:text-black text-xl">
      &times;
    </button>

    <!-- Header -->
    <div class="flex items-center gap-4 mb-6">

      <!-- Icono verde -->
      <div class="w-14 h-14 rounded-full border-2 border-green-500 flex items-center justify-center">
        <i data-lucide="check" class="w-8 h-8 text-green-600"></i>
      </div>

      <div>
        <h3 class="text-2xl font-bold text-gray-800">
          Confirmar aprobación
        </h3>
        <p class="text-gray-400 text-sm">
          Solicitud ID: <span id="modalCodigoConfirmacion">---</span>
        </p>
      </div>

    </div>

    <!-- Solicitante -->
    <div class="mb-6">
      <p class="text-gray-400 text-sm">Solicitante</p>
      <p id="modalSolicitanteConfirmacion" class="font-semibold text-gray-800">
        ---
      </p>
    </div>

    <!-- Cambio de horario -->
    <div id="contenidoConfirmacion" style="padding: 0 !important;"></div>

    <!-- Mensaje verde -->
    <div class="bg-green-100 text-green-800 rounded-lg px-4 py-3 text-sm mb-8">
      Al aprobar esta solicitud, se moverá a Aprobados y los cambios solicitados serán aplicados.
    </div>

    <!-- Botones -->
    <div class="flex justify-between gap-6">

      <button onclick="cerrarModalConfirmar()" 
              class="w-80 py-3 border border-black rounded-xl font-medium hover:bg-gray-100 transition">
        Cancelar
      </button>

      <button id="btnConfirmarAprobar" 
              class="w-80 py-3 bg-[#39A900] text-white rounded-xl font-semibold hover:bg-[#2d8a00] transition shadow-md">
        Confirmar Aprobación
      </button>

    </div>

  </div>
</div>

<!-- ================= MODAL DEVOLVER CON MOTIVO ================= -->
<div id="modalDevolverMotivo" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50">
  <div class="bg-white w-full max-w-md rounded-2xl p-6 relative shadow-xl">
    <h3 class="text-xl font-bold mb-4">Devolver solicitud</h3>
    <p class="text-gray-600 mb-3">Indica el motivo de la devolución:</p>
    <textarea id="motivoDevolucionInput" rows="4" class="w-full border border-gray-300 rounded-lg p-3 mb-4 focus:outline-none focus:ring-2 focus:ring-[#39A900]"></textarea>
    <div class="flex justify-end gap-3">
      <button onclick="cerrarModalDevolver()" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
        Cancelar
      </button>
      <button id="btnConfirmarDevolver" class="px-4 py-2 bg-rose-600 text-white rounded-lg hover:bg-rose-700">
        Devolver
      </button>
    </div>
  </div>
</div>
<script>
  window.API_URL = "<?= BASE_URL ?>src/controllers/SolicitudController.php";
</script>

<script src="<?= BASE_URL ?>src/assets/js/gestionarSolicitudes.js?v=2" defer></script>

