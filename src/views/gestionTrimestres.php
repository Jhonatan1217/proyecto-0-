<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Gestión de Trimestres</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-white text-gray-900 font-sans">

  <div class="max-w-6xl mx-auto px-4 py-10">
    <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight mb-1 text-[#2a7f00]">Gestión de Trimestres</h1>
    <p class="text-gray-600 mb-8">Administra los trimestres</p>

    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm">
      <div class="flex items-center justify-between p-6 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800">Trimestres</h2>
        <button 
          id="btnAbrirModalTrimestre"
          class="bg-[#00324D] text-white px-4 py-2 rounded-xl flex items-center gap-2 hover:bg-[#00273A] transition"
          type="button"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path d="M12 5v14M5 12h14" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <span>Nuevo Trimestre</span>
        </button>
      </div>

      <!-- Tabla -->
      <div class="overflow-x-auto">
        <table class="w-full text-left text-gray-800" id="tablaTrimestres">
          <thead>
            <tr class="border-b bg-gray-50 text-sm text-gray-700">
              <th class="px-6 py-3 font-semibold">N° Trimestre</th>
              <th class="px-6 py-3 font-semibold text-right">Acciones</th>
            </tr>
          </thead>
          <tbody id="tbodyTrimestres" class="text-sm">
            <!-- Se llena dinámicamente -->
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Modal: Nuevo Trimestre -->
  <div id="modalTrimestre" class="fixed inset-0 z-50 hidden">
    <div id="backdrop" class="absolute inset-0 bg-black/70 opacity-0 transition-opacity duration-200"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div id="modalPanel" class="w-full max-w-md bg-white rounded-2xl shadow-2xl p-6 opacity-0 scale-95 transition-all duration-200 relative">
        <button id="btnCerrarModalTrimestre" class="absolute right-4 top-4 p-2 rounded-full hover:bg-gray-100 transition" type="button">✕</button>

        <h3 class="text-xl font-semibold mb-1">Nuevo Trimestre</h3>
        <p class="text-sm text-gray-500 mb-5">Ingresa el número de trimestre</p>

        <form id="formNuevoTrimestre" class="space-y-5">
          <div>
            <label class="block text-sm font-semibold mb-1">Número de trimestre</label>
            <input id="inputNumeroTrimestre" type="number" placeholder="Ej: 1"
              class="w-full rounded-xl border border-gray-300 px-4 py-2 focus:border-[#2a7f00] focus:ring-0 outline-none" required />
          </div>

          <div class="flex justify-end gap-4 pt-2">
            <button type="button" id="btnCancelarModalTrimestre"
              class="px-5 py-2 rounded-xl border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 transition">
              Cancelar
            </button>
            <button type="submit"
              class="px-6 py-2 rounded-xl bg-[#00324D] text-white hover:bg-[#00273A] transition">
              Crear Trimestre
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- JS -->
   <script>
    window.API_URL = "<?= BASE_URL ?>src/controllers/TrimestreController.php";
  </script>

  <script src="<?= BASE_URL ?>src/assets/js/gestionTrimestre.js"></script>


</body>
</html>