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
          class="bg-black text-white px-4 py-2 rounded-xl flex items-center gap-2 hover:bg-gray-800 active:scale-[0.99] transition"
          type="button"
        >
          <img class="w-5 h-" src="../assets/img/plus.svg" alt="Agregar" />
          <span>Nuevo Instructor</span>
        </button>
      </div>

      <!-- Tabla -->
      <div class="overflow-x-auto">
        <table class="w-full text-left">
          <thead>
            <tr class="text-gray-600 text-sm border-b">
              <th class="px-6 py-3 font-medium">Nombre</th>
              <th class="px-6 py-3 font-medium">Tipo</th>
              <th class="px-6 py-3 font-medium">Acciones</th>
            </tr>
          </thead>

          <tbody class="text-sm">
            <!-- Fila 1 -->
            <tr class="border-b">
              <td class="px-6 py-4">Juan Pérez</td>
              <td class="px-6 py-4">
                <span class="bg-black text-white text-xs px-3 py-1 rounded-full">Titular</span>
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
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
              <td class="px-6 py-4">María García</td>
              <td class="px-6 py-4">
                <span class="bg-gray-100 text-gray-700 text-xs px-3 py-1 rounded-full">Suplente</span>
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <!-- Editar -->
                  <button
                    class="p-2 border rounded-xl hover:bg-gray-50 transition"
                    type="button" aria-label="Editar María García" title="Editar"
                  >
                    <img class="w-5 h-5" src="../assets/img/pencil-line.svg" alt="Editar" />
                  </button>

                  <!-- Switch -->
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

</body>
</html>
