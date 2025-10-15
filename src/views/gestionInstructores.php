<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestión de Instructores</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white text-gray-900 font-sans">

  <div class="max-w-6xl mx-auto px-4 py-10">
    <!-- Encabezado -->
    <h1 class="text-4xl font-extrabold tracking-tight mb-2 text-[#39A900]">Gestión de Instructores</h1>
    <p class="text-gray-500 mb-6">Administra los instructores y sus tipos</p>

    <!-- Card -->
    <div class="bg-white shadow rounded-2xl border border-gray-200">
      <!-- Header card -->
      <div class="flex items-center justify-between p-6 border-b">
        <div>
          <h2 class="text-xl font-semibold">Instructores</h2>
          <p class="text-sm text-gray-500">Lista de todos los instructores registrados</p>
        </div>

        <!-- Botón Nuevo Instructor -->
        <button 
          id="btnAbrirModalInstructor"
          class="bg-[#00324D] text-white px-4 py-2 rounded-xl flex items-center gap-2 hover:bg-[#00273A] active:scale-[0.99] transition"
          type="button"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path d="M12 5v14M5 12h14" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <span>Nuevo Instructor</span>
        </button>
      </div>

      <!-- Tabla -->
      <div class="overflow-x-auto">
        <table class="w-full text-left">
          <!-- Encabezado -->
          <thead>
            <tr class="text-gray-600 text-sm border-b">
              <th class="px-6 py-3 font-medium">Nombre</th>
              <th class="px-6 py-3 font-medium text-center">Tipo</th>
              <th class="px-6 py-3 font-medium text-right">Acciones</th>
            </tr>
          </thead>

          <!-- Cuerpo -->
          <tbody class="text-sm">
            <!-- Fila 1 -->
            <tr class="border-b">
              <td class="px-6 py-4 align-middle">Juan Pérez</td>
              <td class="px-6 py-4 align-middle text-center">
                <span class="bg-black text-white text-xs px-3 py-1 rounded-full">Titular</span>
              </td>
              <td class="px-6 py-4 align-middle text-right">
                <div class="flex justify-end items-center gap-3">
                  <!-- Editar -->
                  <button
                    class="p-2 border rounded-xl hover:bg-gray-50 transition"
                    type="button" aria-label="Editar Juan Pérez" title="Editar"
                  >
                    <img class="w-5 h-5" src="../assets/img/pencil-line.svg" alt="Editar" />
                  </button>

                  <!-- Switch -->
                  <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" class="sr-only peer" checked>
                    <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-green-500 transition"></div>
                    <div class="absolute left-0.5 top-0.5 bg-white w-5 h-5 rounded-full transition peer-checked:translate-x-5"></div>
                  </label>
                </div>
              </td>
            </tr>

            <!-- Fila 2 -->
            <tr>
              <td class="px-6 py-4 align-middle">María García</td>
              <td class="px-6 py-4 align-middle text-center">
                <span class="bg-gray-100 text-gray-700 text-xs px-3 py-1 rounded-full">Suplente</span>
              </td>
              <td class="px-6 py-4 align-middle text-right">
                <div class="flex justify-end items-center gap-3">
                  <button
                    class="p-2 border rounded-xl hover:bg-gray-50 transition"
                    type="button" aria-label="Editar María García" title="Editar"
                  >
                    <img class="w-5 h-5" src="../assets/img/pencil-line.svg" alt="Editar" />
                  </button>
                  <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-green-500 transition"></div>
                    <div class="absolute left-0.5 top-0.5 bg-white w-5 h-5 rounded-full transition peer-checked:translate-x-5"></div>
                  </label>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- ========== MODAL: Nuevo Instructor ========== -->
  <div id="modalInstructor" class="fixed inset-0 z-50 hidden">
    <!-- Fondo -->
    <div id="modalBackdrop" class="absolute inset-0 bg-black/60 backdrop-blur-[1px]"></div>

    <!-- Contenedor del modal -->
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div
        class="w-full max-w-[720px] bg-white rounded-2xl shadow-2xl border border-gray-100 p-6 md:p-8 lg:p-10 relative"
        role="dialog" aria-modal="true" aria-labelledby="mi-titulo-modal" aria-describedby="mi-subtitulo-modal"
      >
        <!-- Botón cerrar (X) -->
        <button id="btnCerrarModalInstructor"
          class="absolute right-4 top-4 p-2 rounded-full hover:bg-gray-100 transition"
          aria-label="Cerrar modal"
          type="button"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path d="M6 6l12 12M18 6L6 18" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>

        <!-- Contenido -->
        <div class="space-y-6">
          <div>
            <h3 id="mi-titulo-modal" class="text-2xl font-semibold">Nuevo Instructor</h3>
            <p id="mi-subtitulo-modal" class="text-gray-400 mt-1">Ingresa los datos del nuevo instructor</p>
          </div>

          <!-- Formulario -->
          <form id="formNuevoInstructor" class="space-y-6">
            <!-- Nombre -->
            <div class="space-y-2">
              <label class="block text-sm font-semibold">Nombre del Instructor</label>
              <input
                type="text"
                placeholder="Ej: Juan Pérez"
                class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-black/10"
              />
            </div>

            <!-- Tipo -->
            <div class="space-y-2">
              <label class="block text-sm font-semibold">Tipo de Instructor</label>
              <div class="relative inline-block">
                <select
                  class="appearance-none rounded-xl border border-gray-200 bg-white px-4 py-3 pr-10 shadow-sm focus:outline-none focus:ring-2 focus:ring-black/10"
                >
                  <option>Titular</option>
                  <option>Suplente</option>
                </select>
                <!-- Flecha custom -->
                <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.184l3.71-3.954a.75.75 0 111.08 1.04l-4.24 4.52a.75.75 0 01-1.08 0l-4.24-4.52a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
                </svg>
              </div>
            </div>

            <!-- Acciones -->
            <div class="pt-2 flex items-center justify-end gap-4">
              <button
                type="button"
                id="btnCancelarModalInstructor"
                class="px-5 py-2.5 rounded-xl border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 transition"
              >
                Cancelar
              </button>
              <button
                type="submit"
                class="px-6 py-2.5 rounded-xl bg-black text-white hover:opacity-90 transition"
              >
                Crear Instructor
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  <!-- ========== /MODAL ========== -->

  <!-- JS: abrir/cerrar modal (sin tocar tu base) -->
  <script>
    (() => {
      const openBtn  = document.getElementById('btnAbrirModalInstructor');
      const modal    = document.getElementById('modalInstructor');
      const closeBtn = document.getElementById('btnCerrarModalInstructor');
      const cancelBt = document.getElementById('btnCancelarModalInstructor');
      const backdrop = document.getElementById('modalBackdrop');

      const open = () => modal.classList.remove('hidden');
      const close = () => modal.classList.add('hidden');

      openBtn?.addEventListener('click', open);
      closeBtn?.addEventListener('click', close);
      cancelBt?.addEventListener('click', close);
      backdrop?.addEventListener('click', (e) => {
        // Cierra si se hace click en el fondo oscuro
        if (e.target === backdrop) close();
      });

      // Cerrar con ESC
      window.addEventListener('keydown', (e) => {
        if (!modal.classList.contains('hidden') && e.key === 'Escape') close();
      });

      // Submit de ejemplo (evita recarga)
      document.getElementById('formNuevoInstructor')?.addEventListener('submit', (e) => {
        e.preventDefault();
        // Aquí iría tu lógica de creación...
        close();
      });
    })();
  </script>
</body>
</html>
