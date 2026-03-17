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

    <div class="bg-white rounded-2xl shadow-2xl relative" style="width:100%;max-width:520px;">

        <!-- BOTÓN CERRAR -->
        <button id="cerrarModalDetalle"
            onclick="(function(){var m=document.getElementById('modalDetalle');m.classList.add('hidden');m.classList.remove('flex');})()"
            class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 text-xl font-bold z-10 leading-none"
            style="line-height:1;padding:4px 8px;">
            ✕
        </button>

        <!-- HEADER: avatar + nombre + estado + tipo badge -->
        <div class="px-6 pt-6 pb-4">

            <!-- Fila superior: icono + nombre + badges -->
            <div style="display:flex;align-items:flex-start;gap:14px;">

                <div style="width:40px;height:40px;border-radius:50%;border:2px solid #e5e7eb;display:flex;align-items:center;justify-content:center;background:#f9fafb;flex-shrink:0;">
                    <i data-lucide="user" style="width:18px;height:18px;color:#9ca3af;"></i>
                </div>

                <div style="flex:1;min-width:0;">
                    <!-- Nombre + estado + tipo -->
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                        <h2 id="modalSolicitante" style="font-size:15px;font-weight:700;color:#1f2937;margin:0;"></h2>
                        <span id="modalEstado" style="font-size:11px;font-weight:700;padding:2px 10px;border-radius:999px;background:#fef3c7;color:#92400e;"></span>
                        <span id="modalTipoBadge" style="font-size:11px;font-weight:500;padding:2px 10px;border-radius:999px;background:#f3f4f6;color:#6b7280;border:1px solid #e5e7eb;"></span>
                    </div>
                    <!-- Código -->
                    <p id="modalCodigo" style="font-size:13px;color:#3b82f6;font-weight:500;margin:3px 0 0 0;"></p>
                </div>

            </div>

            <!-- Programa: alineado con el borde izquierdo del icono -->
            <div style="margin-top:14px;">
                <p style="font-size:11px;color:#9ca3af;text-transform:uppercase;letter-spacing:0.05em;margin:0 0 3px 0;">Programa</p>
                <p id="modalPrograma" style="font-size:13px;font-weight:600;color:#1f2937;margin:0;"></p>
            </div>

        </div>

        <!-- SEPARADOR -->
        <div class="border-t border-gray-100 mx-6"></div>

        <!-- CONTENIDO DINÁMICO (bloque horario o datos) -->
        <div id="modalContenido" class="px-6 py-4"></div>

        <!-- MOTIVO DEVOLUCIÓN -->
        <div id="motivoDevolucion" class="px-6 pb-3 hidden">
            <div class="bg-rose-50 border border-rose-200 rounded-xl p-3 text-sm">
                <p class="font-semibold text-rose-700 mb-1">Motivo de devolución:</p>
                <p id="textoMotivoDevolucion" class="text-rose-600"></p>
            </div>
        </div>

        <!-- FECHA -->
        <div class="px-6 pb-4 flex items-center gap-2 text-gray-500">
            <i data-lucide="calendar" class="w-4 h-4 shrink-0 text-gray-400"></i>
            <div>
                <p class="text-xs text-gray-400">Solicitado el</p>
                <p id="modalFecha" class="text-sm font-semibold text-gray-800"></p>
            </div>
        </div>

        <!-- BOTONES (solo PENDIENTE) -->
        <div id="botonesAccion" class="px-6 pb-6 flex justify-center gap-4 hidden">
            <button id="btnAprobar"
                class="bg-[#39A900] hover:bg-[#2d8a00] active:bg-[#257500] text-white px-10 py-2.5 rounded-lg font-semibold transition-colors shadow-sm">
                Aceptar
            </button>
            <button id="btnDevolver"
                class="bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white px-10 py-2.5 rounded-lg font-semibold transition-colors shadow-sm">
                Devolver
            </button>
        </div>

    </div>
</div>
<!-- ================= MODAL CONFIRMAR APROBACIÓN ================= -->
<div id="modalConfirmarAprobacion" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50">

  <div class="bg-white rounded-3xl relative shadow-2xl border border-gray-200" style="width:100%;max-width:540px;padding:3rem;">

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
  <div class="bg-white rounded-2xl p-6 relative shadow-xl" style="width:100%;max-width:448px;">
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