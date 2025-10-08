<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Proyecto 0</title>
    <script src="https://cdn.tailwindcss.com"></script>
  </head>

  <body class="flex flex-col min-h-screen font-sans text-center bg-white text-gray-900">
    <!-- Contenido principal -->
    <main class="flex flex-col items-center mt-20 flex-1">
      <h1 class="text-6xl font-bold text-[#39A900] mb-2">PROYECTO 0</h1>
      <p class="text-base mb-8">Crea y ajusta horarios en segundos</p>

      <div class="flex flex-col gap-3">
        <!-- Botón de crear -->
        <button class="px-6 py-2 border border-gray-400 text-sm rounded-md text-[#00324D] hover:bg-[#004A70] font-bold hover:text-white">
          CREAR TRIMESTRALIZACIÓN
        </button>

        <!-- Menú desplegable -->
        <?php
          // Opciones del menú
          $zonas = ["Zona 1", "Zona 2", "Zona 3", "Zona 5", "Zona 6"];
        ?>

        <div class="relative inline-block text-left">
          <!-- Botón principal -->
          <button
            id="dropdownButton"
            class="px-6 py-2 border border-gray-400 text-sm rounded-md hover:bg-gray-100 flex items-center justify-between w-60 text-[#00324D] font-bold"
          >
            VISUALIZAR ZONA
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-2 transition-transform duration-200"
              fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>

          <!-- Contenido desplegable -->
          <div
            id="dropdownMenu"
            class="hidden absolute left-0 mt-2 w-60 bg-white border border-gray-200 rounded-md shadow-lg z-10"
          >
            <?php foreach ($zonas as $zona): ?>
              <a href="?zona=<?php echo urlencode($zona); ?>"
                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                <?php echo htmlspecialchars($zona); ?>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </main>

    <!-- Script del menú desplegable -->
    <script>
      const btn = document.getElementById("dropdownButton");
      const menu = document.getElementById("dropdownMenu");
      const arrow = btn.querySelector("svg");

      btn.addEventListener("click", () => {
        menu.classList.toggle("hidden");
        arrow.classList.toggle("rotate-180");
      });

      // Cerrar al hacer clic fuera
      window.addEventListener("click", (e) => {
        if (!btn.contains(e.target)) {
          menu.classList.add("hidden");
          arrow.classList.remove("rotate-180");
        }
      });
    </script>
  </body>
</html>
