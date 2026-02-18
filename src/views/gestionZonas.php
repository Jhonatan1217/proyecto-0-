<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Gestión de Zonas</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <!-- Tailwind y SweetAlert deben estar cargados en tu layout principal -->
  <style>
    /* Scrollbar bonito (opcional) */
    #wrapTablaZonas::-webkit-scrollbar { width: 8px; }
    #wrapTablaZonas::-webkit-scrollbar-thumb { background-color: rgba(0,0,0,0.15); border-radius: 10px; }
    #wrapTablaZonas:hover::-webkit-scrollbar-thumb { background-color: rgba(0,0,0,0.25); }

    /* ============================
       SOPORTE PARA LÁPIZ / ACCIONES
       (por si hay botón .btn-editar en las filas)
       ============================ */
    .btn-editar {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    /* Contenedor de acciones en la última columna:
       mantiene lápiz + switch (o lo que haya) en una sola línea */
    #tablaInstructores td:last-child > div {
      display: flex;
      justify-content: flex-end;
      align-items: center;
      gap: 0.5rem;
      flex-wrap: nowrap;
    }

    /* ============================
       RESPONSIVE EXTRA (SIN CAMBIAR DISEÑO)
       ============================ */
    @media (max-width: 640px) {
      /* Header de la card: título + botón se apilan */
      .bg-white.shadow.rounded-2xl.border.border-gray-200 > .flex {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
      }

      /* Botón "Nueva Zona" ocupa ancho completo en móvil */
      #btnAbrirModalZonas {
        width: 100%;
        justify-content: center;
      }

      /* Ajustar paddings de la tabla para pantallas pequeñas */
      #tablaInstructores th,
      #tablaInstructores td {
        padding-left: 0.75rem;
        padding-right: 0.75rem;
      }

      /* Dar más espacio a la columna Acciones para que no se rompa */
      #tablaInstructores th:last-child,
      #tablaInstructores td:last-child {
        width: 120px;        /* si queda justo, puedes subir a 140px */
        white-space: nowrap; /* que no baje el contenido a otra línea */
      }

      /* Altura máxima del wrapper y scroll vertical interno en móvil */
      #wrapTablaZonas {
        max-height: 320px;
        overflow-y: auto;
      }
    }
  </style>
</head>
<body class="bg-white text-gray-900 font-sans">

  <div class="max-w-6xl mx-auto px-4 py-10">
    <!-- Encabezado -->
    <h1 class="text-4xl font-extrabold tracking-tight mb-2 text-[#39A900]">Gestión de Zonas</h1>
    <p class="text-gray-500 mb-6">Administra las Zonas</p>

    <!-- Card -->
    <div class="bg-white shadow rounded-2xl border border-gray-200">
      <!-- Header card -->
      <div class="flex items-center justify-between p-6 border-b">
        <div>
          <h2 class="text-xl font-semibold">Zonas</h2>
          <p class="text-sm text-gray-500">Administra a las zonas</p>
        </div>

        <button 
          id="btnAbrirModalZonas"
          class="bg-[#0a3a57] text-white px-4 py-2 rounded-xl flex items-center gap-2 hover:bg-[#00304D] active:scale-[0.99] transition"
          type="button"
        >
          <img class="w-5 h-5" src="<?= BASE_URL ?>src/assets/img/plus.svg" alt="+" />
          <span class="text-sm font-medium">Nueva Zona</span>
        </button>
      </div>

      <!-- 🟩 Contenedor con scroll interno -->
      <div id="wrapTablaZonas" class="overflow-hidden transition-all duration-300">
        <div class="overflow-x-auto">
          <table class="w-full text-left" id="tablaInstructores">
            <thead class="bg-gray-50">
              <tr class="text-gray-600 text-sm border-b">
                <th class="px-6 py-3 font-medium">N° Zona</th>
                <th class="px-6 py-3 font-medium text-center">Área</th>
                <th class="px-6 py-3 font-medium text-right">Acciones</th>
              </tr>
            </thead>
            <tbody class="text-sm">
              <!-- Las filas se renderizan por JS -->
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- ========== MODAL: Nueva Zona ========== -->
  <div id="modalZonas" class="fixed inset-0 z-50 hidden">
    <div id="modalBackdrop" class="absolute inset-0 bg-black/60 backdrop-blur-[1px] opacity-0 transition-opacity duration-200"></div>

    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div id="modalPanel"
        class="w-full max-w-[720px] bg-white rounded-2xl shadow-2xl border border-gray-100 p-6 md:p-8 lg:p-10 relative
               opacity-0 scale-95 translate-y-2 transition-all duration-200 ease-out">
        
        <button id="btnCerrarModalZonas"
          class="absolute right-4 top-4 p-2 rounded-full hover:bg-gray-100 transition"
          type="button">✕</button>

        <div class="space-y-6">
          <div>
            <h3 class="text-2xl font-semibold">Nueva Zona</h3>
            <p class="text-gray-400 mt-1">Ingresa el número y el área de la nueva zona</p>
          </div>

          <form id="formNuevaZona" class="space-y-6">
            <!-- N° Zona -->
            <div class="space-y-2">
              <label for="id_zona" class="block text-sm font-semibold">Número de la Zona</label>
              <input id="id_zona" name="id_zona" type="number" placeholder="Ej: 1"
                class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm placeholder:text-gray-400
                       focus:ring-0 focus:outline-none focus:border-gray-300" />
            </div>

            <!-- Área -->
            <div class="space-y-2">
              <label for="id_area" class="block text-sm font-semibold">Área perteneciente</label>
              <div class="relative">
                <select id="id_area" name="id_area"
                  class="w-full appearance-none rounded-xl border border-gray-200 bg-white px-4 py-3 pr-10 shadow-sm
                         focus:ring-0 focus:outline-none focus:border-gray-300">
                  <option disabled selected value="">Cargando áreas...</option>
                </select>
                <img 
                  src="<?= BASE_URL ?>src/assets/img/chevron-down.svg" 
                  alt="arrow" 
                  class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 opacity-70"
                />
              </div>
            </div>

            <!-- Acciones -->
            <div class="pt-2 flex items-center justify-end gap-4">
              <button type="button" id="btnCancelarModalZonas"
                class="px-5 py-2.5 rounded-xl border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 transition">
                Cancelar
              </button>
              <button type="submit"
                class="px-6 py-2.5 rounded-xl bg-[#0a3a57] text-white hover:bg-[#00304D] transition">
                Crear Zona
              </button>
            </div>
          </form>

        </div>
      </div>
    </div>
  </div>

  <!-- Inyecta la URL del controlador para el JS -->
  <script>window.API_URL = "<?= BASE_URL ?>src/controllers/ZonaController.php";</script>

  <!-- Tu script -->
  <script src="<?= BASE_URL ?>src/assets/js/gestionZonas.js?v=3" defer></script>
</body>
</html>
