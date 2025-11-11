<?php /* views/instructores.php */ ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestión de Instructores</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="../assets/css/register_tables.css">
  <style>
    /* Scroll igual que en zonas */
    #wrapTabla::-webkit-scrollbar {
      width: 8px;
    }
    #wrapTabla::-webkit-scrollbar-thumb {
      background-color: rgba(0, 0, 0, 0.15);
      border-radius: 10px;
    }
    #wrapTabla:hover::-webkit-scrollbar-thumb {
      background-color: rgba(0, 0, 0, 0.25);
    }
  </style>
</head>
<body class="bg-white text-gray-900 font-sans">

  <div class="max-w-6xl mx-auto px-4 py-10">
    <h1 class="text-4xl font-extrabold tracking-tight mb-2 text-[#39A900]">Gestión de Instructores</h1>
    <p class="text-gray-500 mb-6">Administra los instructores y sus tipos</p>

    <div class="bg-white shadow rounded-2xl border border-gray-200">
      <div class="flex items-center justify-between p-6 border-b">
        <div>
          <h2 class="text-xl font-semibold">Instructores</h2>
          <p class="text-sm text-gray-500">Lista de todos los instructores registrados</p>
        </div>
        <button id="btnAbrirModalInstructor"
          class="bg-[#00324D] text-white px-4 py-2 rounded-xl flex items-center gap-2 hover:bg-[#00273A] active:scale-[0.99] transition"
          type="button">
          <img class="w-5 h-5" src="<?= BASE_URL ?>src/assets/img/plus.svg" />
          <span>Nuevo Instructor</span>
        </button>
      </div>

      <!-- Scroll interno igual que zonas -->
      <div id="wrapTabla" class="overflow-hidden transition-all duration-300">
        <div class="overflow-x-auto">
          <table class="w-full text-left" id="tablaInstructores">
            <thead class="bg-gray-50">
              <tr class="text-gray-600 text-sm border-b">
                <th class="px-6 py-3 font-medium">Nombre</th>
                <th class="px-6 py-3 font-medium text-center">Tipo</th>
                <th class="px-6 py-3 font-medium text-right">Acciones</th>
              </tr>
            </thead>
            <tbody id="tbodyInstructores" class="text-sm"></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal -->
  <div id="modalInstructor" class="fixed inset-0 z-50 hidden">
    <div id="modalBackdrop" class="absolute inset-0 bg-black/60 backdrop-blur-[1px] opacity-0 transition-opacity duration-200"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div id="modalPanel"
        class="w-full max-w-[720px] bg-white rounded-2xl shadow-2xl border border-gray-100 p-6 md:p-8 lg:p-10 relative
               opacity-0 scale-95 translate-y-2 transition-all duration-200 ease-out">
        <button id="btnCerrarModalInstructor"
          class="absolute right-4 top-4 p-2 rounded-full hover:bg-gray-100 transition"
          type="button">✕</button>

        <div class="space-y-6">
          <div>
            <h3 class="text-2xl font-semibold">Nuevo Instructor</h3>
            <p class="text-gray-400 mt-1">Ingresa los datos del nuevo instructor</p>
          </div>

          <form id="formNuevoInstructor" class="space-y-6">
            <div class="space-y-2">
              <label class="block text-sm font-semibold">Nombre del Instructor</label>
              <input type="text" name="nombre_instructor" placeholder="Ej: Juan Pérez"
                class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm placeholder:text-gray-400
                       focus:ring-0 focus:outline-none focus:border-gray-300" />
            </div>

            <div class="space-y-2">
              <label class="block text-sm font-semibold">Tipo de Instructor</label>
              <div class="relative">
                <select name="tipo_instructor"
                  class="w-full appearance-none rounded-xl border border-gray-200 bg-white px-4 py-3 pr-10 shadow-sm
                         focus:ring-0 focus:outline-none focus:border-gray-300">
                  <option disabled selected>Seleccione un tipo</option>
                  <option value="TECNICO">Técnico</option>
                  <option value="TRANSVERSAL">Transversal</option>
                  <option value="MIXTO">Mixto</option>
                </select>
                <img 
                  src="<?= BASE_URL ?>src/assets/img/chevron-down.svg" 
                  alt="arrow" 
                  class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 opacity-70"
                />
              </div>
            </div>

            <div class="pt-2 flex items-center justify-end gap-4">
              <button type="button" id="btnCancelarModalInstructor"
                class="px-5 py-2.5 rounded-xl border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 transition">
                Cancelar
              </button>
              <button type="submit"
                class="px-6 py-2.5 rounded-xl bg-[#00324D] text-white hover:bg-[#00273A] transition">
                Crear Instructor
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script src="<?= BASE_URL ?>src/assets/js/sweetalert2.all.min.js"></script>

  <script>
    window.API_URL = "<?= BASE_URL ?>src/controllers/InstructorController.php";
  </script>

  <script src="<?= BASE_URL ?>src/assets/js/gestionarInstructor.js?v=3" defer></script>
</body>
</html>
