<?php /* (Opcional) includes/seguridad aquí */ ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestión de Áreas</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-white text-gray-900 font-sans">

  <div class="max-w-6xl mx-auto px-4 py-10">
    <!-- Encabezado -->
    <h1 class="text-4xl font-extrabold tracking-tight mb-2 text-[#39A900]">Gestión de Áreas</h1>
    <p class="text-gray-500 mb-6">Administra las áreas</p>

    <!-- Card -->
    <div class="bg-white shadow rounded-2xl border border-gray-200">
      <!-- Header card -->
      <div class="flex items-center justify-between px-6 pt-6 pb-4 border-b">
        <div>
          <h2 class="text-xl font-semibold">Áreas</h2>
          <p class="text-sm text-gray-500">Administra las áreas</p>
        </div>

        <!-- Botón Nueva Área -->
        <button
          id="btnAbrirModalArea"
          class="bg-[#00324D] text-white px-5 py-2 rounded-lg flex items-center gap-2 hover:bg-[#00283D] active:scale-[0.98] transition"
          type="button"
        >
          <img class="w-4" src="<?= BASE_URL ?>src/assets/img/plus.svg" alt="Agregar" />
          <span class="text-sm font-medium">Nueva Área</span>
        </button>
      </div>

      <!-- Tabla -->
      <div class="overflow-x-auto">
        <table class="w-full text-left" id="tablaAreas">
          <thead>
            <tr class="text-gray-600 text-sm border-b">
              <th class="px-6 py-3 font-medium w-3/4">Nombre Área</th>
              <th class="px-6 py-3 font-medium text-right w-1/4">Acciones</th>
            </tr>
          </thead>

          <tbody class="text-sm">
            <!-- Filas de ejemplo (serán reemplazadas al cargar) -->
            <tr class="border-b">
              <td class="px-6 py-4">Polivalente</td>
              <td class="px-6 py-4 text-right">
                <div class="flex justify-end items-center gap-3">
                  <button class="btn-editar p-2 border rounded-lg hover:bg-gray-50 transition" type="button" title="Editar">
                    <img class="w-5 h-5" src="<?= BASE_URL ?>src/assets/img/pencil-line.svg" alt="Editar" />
                  </button>
                  <!-- Switch -->
                  <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" class="sr-only peer" checked>
                    <div class="w-11 h-6 bg-gray-300 rounded-full peer-checked:bg-[#39A900] transition"></div>
                    <div class="absolute left-0.5 top-0.5 bg-white w-5 h-5 rounded-full transition peer-checked:translate-x-5"></div>
                  </label>
                </div>
              </td>
            </tr>

            <tr>
              <td class="px-6 py-4">Infraestructura</td>
              <td class="px-6 py-4 text-right">
                <div class="flex justify-end items-center gap-3">
                  <button class="btn-editar p-2 border rounded-lg hover:bg-gray-50 transition" type="button" title="Editar">
                    <img class="w-5 h-5" src="<?= BASE_URL ?>src/assets/img/pencil-line.svg" alt="Editar" />
                  </button>
                  <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-300 rounded-full peer-checked:bg-[#39A900] transition"></div>
                    <div class="absolute left-0.5 top-0.5 bg-white w-5 h-5 rounded-full transition peer-checked:translate-x-5"></div>
                  </label>
                </div>
              </td>
            </tr>
            <!-- /Ejemplo -->
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- ========== MODAL: Nueva Área ========== -->
  <div id="modalArea" class="fixed inset-0 z-50 hidden">
    <!-- Fondo (animable) -->
    <div id="modalBackdrop" class="absolute inset-0 bg-black/60 backdrop-blur-[1px] opacity-0 transition-opacity duration-200"></div>

    <!-- Contenedor -->
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div id="modalPanel"
        class="w-full max-w-[720px] bg-white rounded-2xl shadow-2xl border border-gray-100 p-6 md:p-8 lg:p-10 relative
               opacity-0 scale-95 translate-y-2 transition-all duration-200 ease-out">
        
        <!-- Botón cerrar -->
        <button id="btnCerrarModalArea"
          class="absolute right-4 top-4 p-2 rounded-full hover:bg-gray-100 transition"
          type="button">✕</button>

        <!-- Contenido -->
        <div class="space-y-6">
          <div>
            <h3 class="text-2xl font-semibold">Nueva Área</h3>
            <p class="text-gray-400 mt-1">Ingresa el nombre del área</p>
          </div>

          <!-- Formulario -->
          <form id="formNuevaArea" class="space-y-6">
            <div class="space-y-2">
              <label class="block text-sm font-semibold">Nombre del Área</label>
              <input
                type="text"
                name="nombre_area"
                placeholder="Ej: Área Polivalente"
                class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm placeholder:text-gray-400 focus:ring-0 focus:outline-none focus:border-gray-300"
              />
            </div>

            <!-- Acciones -->
            <div class="pt-2 flex items-center justify-end gap-4">
              <button type="button" id="btnCancelarModalArea"
                class="px-5 py-2.5 rounded-xl border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 transition">
                Cancelar
              </button>
              <button type="submit"
                class="px-6 py-2.5 rounded-xl bg-[#00324D] text-white hover:bg-[#00273A] transition">
                Crear Área
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Librería de toasts (para los avisos del JS) -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <!-- Tu JS que consume AreaController.php -->
  <script src="../assets/js/gestionAreas.js"></script>

</body>
</html>