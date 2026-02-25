<script>
document.addEventListener("DOMContentLoaded", function () {

  const btn = document.getElementById("userDropdownBtn");
  const menu = document.getElementById("userMenu");
  const chevron = document.getElementById("chevronIcon");
  const container = document.getElementById("userDropdownContainer");

  btn.addEventListener("click", function (e) {
    e.stopPropagation();
    menu.classList.toggle("hidden");
    chevron.classList.toggle("rotate-180");
  });

  document.addEventListener("click", function (e) {
    if (!container.contains(e.target)) {
      menu.classList.add("hidden");
      chevron.classList.remove("rotate-180");
    }
  });

});
</script>

<!-- Header -->
<header class="flex items-center justify-between px-6 py-4 border-b shadow-sm bg-white">

  <!-- IZQUIERDA: LOGO -->
  <div class="flex items-center">
    <img src="<?= BASE_URL ?>src/assets/img/logoSena.png"
         alt="SENA Logo"
         class="h-10" />
  </div>

  <!-- DERECHA -->
  <div class="flex items-center gap-8">

    <?php
      $nombre = $_SESSION['usuario_nombre'] ?? 'Usuario';
      $correo = $_SESSION['usuario_correo'] ?? '';
      $iniciales = strtoupper(substr($nombre, 0, 2));
    ?>

    <!-- USER DROPDOWN -->
    <div class="relative" id="userDropdownContainer">

      <button id="userDropdownBtn"
        class="flex items-center space-x-3 focus:outline-none">

        <!-- AVATAR -->
       <div class="w-10 h-10 min-w-[40px] min-h-[40px]
            bg-gray-600 text-white
            rounded-full
            flex items-center justify-center
            font-semibold text-sm
            leading-none">
        <?= $iniciales ?>
      </div>

        <!-- TEXT -->
        <div class="text-left">
          <p class="text-sm font-semibold text-gray-800 leading-none">
            <?= htmlspecialchars($nombre) ?>
          </p>
          <p class="text-xs text-gray-500">
            <?= htmlspecialchars($correo) ?>
          </p>
        </div>

        <!-- CHEVRON -->
        <svg id="chevronIcon"
             class="w-4 h-4 text-gray-600 transition-transform duration-200"
             fill="none"
             stroke="currentColor"
             viewBox="0 0 24 24"
             stroke-width="2">
          <path stroke-linecap="round"
                stroke-linejoin="round"
                d="M19 9l-7 7-7-7" />
        </svg>

      </button>

      <!-- DROPDOWN -->
      <div id="userMenu"
           class="hidden absolute right-0 mt-3 w-56
                  bg-white border border-gray-200
                  rounded-xl shadow-lg z-50 overflow-hidden">

        <a href="#" class="block px-4 py-3 text-sm hover:bg-gray-100">
          Mi perfil
        </a>

        <a href="#" class="block px-4 py-3 text-sm hover:bg-gray-100">
          Cambiar contraseña
        </a>

        <div class="border-t"></div>

        <a href="index.php?page=logout"
           class="block px-4 py-3 text-sm text-red-600 hover:bg-gray-100">
           Cerrar sesión
        </a>

      </div>
    </div>

    <!-- MENU HAMBURGUESA -->
    <img src="<?= BASE_URL ?>src/assets/img/menu.svg"
         alt="Menú"
         id="menu-hamburguesa"
         class="h-8 w-8 cursor-pointer" />

  </div>

</header>

<!-- Menú lateral -->
<nav id="menu-lateral" class="fixed top-0 right-0 h-full w-80 bg-white shadow-2xl transform translate-x-full transition-transform duration-300 ease-in-out z-50 pointer-events-none">
  <div class="flex justify-between items-center p-4 border-b border-gray-400 mx-4">
    <h2 class="font-semibold text-gray-800 text-xl">Menú de navegación</h2>
    <button id="cerrar-menu" class="text-gray-600 text-2xl hover:text-black">×</button>
  </div>

  <ul class="p-4 space-y-4 text-gray-700">
    <li class="flex items-center space-x-2 hover:text-[#39a900] cursor-pointer p-2">
      <img src="<?= BASE_URL ?>src/assets/img/house.svg" alt="Icono de Inicio">
      <a href="<?= BASE_URL ?>index.php">Inicio</a>
    </li>

    <li class="flex items-center space-x-2 hover:text-[#39a900] cursor-pointer p-2">
      <img src="<?= BASE_URL ?>src/assets/img/layout-grid.svg" alt="Icono de Áreas">
      <a href="<?= BASE_URL ?>index.php?page=src/views/gestionAreas">Áreas</a>
    </li>

    <li class="flex items-center space-x-2 hover:text-[#39a900] cursor-pointer p-2">
      <img src="<?= BASE_URL ?>src/assets/img/map-pin.svg" alt="Icono de Zonas">
      <a href="<?= BASE_URL ?>index.php?page=src/views/gestionZonas">Zonas</a>
    </li>


    <li class="flex items-center space-x-2 hover:text-[#39a900] cursor-pointer p-2">
      <img src="<?= BASE_URL ?>src/assets/img/layers.svg" alt="Icono de Trimestres">
      <a href="<?= BASE_URL ?>index.php?page=src/views/gestionTrimestres">Trimestres</a>
    </li>

    <li class="flex items-center space-x-2 hover:text-[#39a900] cursor-pointer p-2">
      <img src="<?= BASE_URL ?>src/assets/img/users.svg" alt="Icono de Instructores">
      <a href="<?= BASE_URL ?>index.php?page=src/views/gestionInstructores">Instructores</a>
    </li>

    <li class="flex items-center space-x-2 hover:text-[#39a900] cursor-pointer p-2">
      <img src="<?= BASE_URL ?>src/assets/img/book-open.svg" alt="Icono de Competencias">
      <a href="<?= BASE_URL ?>index.php?page=src/views/gestionCompetencias">Competencias</a>
    </li>

    <li class="flex items-center space-x-2 hover:text-[#39a900] cursor-pointer p-2">
      <img src="<?= BASE_URL ?>src/assets/img/calendar-days.svg" alt="Icono de Horarios">
      <a href="<?= BASE_URL ?>index.php?page=src/views/register_tables  ">Horarios</a>
    </li>

    <li class="flex items-center space-x-2 hover:text-[#39a900] cursor-pointer p-2">
      <img src="<?= BASE_URL ?>src/assets/img/history.svg" alt="Icono de Trimestres">
      <a href="<?= BASE_URL ?>index.php?page=src/views/historialRegistrosInactivos">Historial</a>
    </li>
  </ul>
</nav>

  <script src="<?= BASE_URL ?>src/assets/js/header.js"></script>

