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
<style>
    .rotate-180 {
      transform: rotate(180deg);
    }
    #academicos-submenu {
      padding-left: 1.5rem !important;
    }
    #academicos-submenu::before {
      content: '';
      position: absolute;
      left: 1.2rem;
      top: 3rem;
      bottom: 0;
      width: 1px;
      background-color: #000000;
    }
    #academicos-submenu li {
      position: relative;
      padding-left: 1.5rem !important;
    }
    #menu-lateral::-webkit-scrollbar {
      width: 8px;
    }
    #menu-lateral::-webkit-scrollbar-track {
      background: #E8E8E8;
    }
    #menu-lateral::-webkit-scrollbar-thumb {
      background: #BFBFBF;
      border-radius: 4px;
    }
  </style>

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

    <!-- Botón imagen menú -->
    <img src="<?= BASE_URL ?>src/assets/img/menu.svg" alt="Menú" id="menu-hamburguesa" class="h-8 w-8 cursor-pointer" />

  </div>

</header>

<!-- Menú lateral -->
  <nav id="menu-lateral" class="fixed top-0 right-0 h-full w-80 bg-white shadow-2xl transform translate-x-full transition-transform duration-300 ease-in-out z-50 pointer-events-none overflow-y-auto">
    <div class="flex justify-between items-center p-4 border-b border-gray-400 mx-4">
      <h2 class="font-semibold text-gray-800 text-xl">Menú de navegación</h2>
      <button id="cerrar-menu" class="text-gray-600 text-2xl hover:text-black">×</button>
    </div>

    <ul class="p-4 space-y-4 text-gray-700">
      <li>
        <a href="<?= BASE_URL ?>index.php?page=src/views/register_tables" class="flex items-center space-x-2 hover:text-[#39a900] cursor-pointer p-2">
          <img src="<?= BASE_URL ?>src/assets/img/calendar-days.svg" alt="Icono de Horarios">
          <span>Horarios</span>
        </a>
      </li>

      <li>
        <a href="<?= BASE_URL ?>index.php?page=src/views/gestionAreas" class="flex items-center space-x-2 hover:text-[#39a900] cursor-pointer p-2">
          <img src="<?= BASE_URL ?>src/assets/img/map-pin.svg" alt="Icono de Áreas">
          <span>Áreas</span>
        </a>
      </li>
      
      <li>
        <a href="<?= BASE_URL ?>index.php?page=src/views/gestionZonas" class="flex items-center space-x-2 hover:text-[#39a900] cursor-pointer p-2">
          <img src="<?= BASE_URL ?>src/assets/img/house-plus.svg" alt="Icono de Zonas">
          <span>Zonas</span>
        </a>
      </li>

      <li>
        <a href="<?= BASE_URL ?>index.php?page=src/views/gestionTrimestres" class="flex items-center space-x-2 hover:text-[#39a900] cursor-pointer p-2">
          <img src="<?= BASE_URL ?>src/assets/img/layers.svg" alt="Icono de Trimestres">
          <span>Trimestres</span>
        </a>
      </li>

      <li>
        <a href="#" class="flex items-center space-x-2 hover:text-[#39a900] cursor-pointer p-2">
          <img src="<?= BASE_URL ?>src/assets/img/users.svg" alt="Icono de Usuarios">
          <span>Usuarios</span>
        </a>
      </li>

      <li>
        <a href="<?= BASE_URL ?>index.php?page=src/views/gestionGrupos" class="flex items-center space-x-2 hover:text-[#39a900] cursor-pointer p-2">
          <img src="<?= BASE_URL ?>src/assets/img/folder-minus.svg" alt="Icono de Grupos">
          <span>Grupos</span>
        </a>
      </li>

      <li class="relative">
        <div class="flex items-center justify-between hover:text-[#39a900] cursor-pointer p-2" id="academicos-toggle">
          <div class="flex items-center space-x-2">
            <img src="<?= BASE_URL ?>src/assets/img/book-open.svg" alt="Icono de Académicos">
            <span>Académicos</span>
            <svg class="w-4 h-4 transition-transform duration-200" id="academicos-flecha" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        </div>
        
        <!-- Submenú de Académicos (inicialmente oculto) -->
        <ul class="pl-12 mt-2 space-y-2 hidden" id="academicos-submenu">
          <li>
            <a href="#" class="flex items-center space-x-2 hover:text-[#39a900] cursor-pointer p-2">
              <img src="<?= BASE_URL ?>src/assets/img/upload.svg" alt="Icono de Carga Excel" class="w-5 h-5">
              <span>Carga Excel</span>
            </a>
          </li>
          <li>
            <a href="#" class="flex items-center space-x-2 hover:text-[#39a900] cursor-pointer p-2">
              <img src="<?= BASE_URL ?>src/assets/img/graduation-cap.svg" alt="Icono de Programas" class="w-5 h-5">
              <span>Programas</span>
            </a>
          </li>
          <li>
            <a href="<?= BASE_URL ?>index.php?page=src/views/gestionCompetencias" class="flex items-center space-x-2 hover:text-[#39a900] cursor-pointer p-2">
              <img src="<?= BASE_URL ?>src/assets/img/book.svg" alt="Icono de Competencias" class="w-5 h-5">
              <span>Competencias</span>
            </a>
          </li>
          <li>
            <a href="#" class="flex items-center space-x-2 hover:text-[#39a900] cursor-pointer p-2">
              <img src="<?= BASE_URL ?>src/assets/img/target.svg" alt="Icono de RAE" class="w-5 h-5">
              <span>RAE</span>
            </a>
          </li>
        </ul>
      </li>

      <li>
        <a href="#" class="flex items-center space-x-2 hover:text-[#39a900] cursor-pointer p-2">
          <img src="<?= BASE_URL ?>src/assets/img/clipboard-list.svg" alt="Icono de Solicitudes">
          <span>Solicitudes</span>
        </a>
      </li>

      <li>
        <a href="<?= BASE_URL ?>index.php?page=src/views/historialRegistrosInactivos" class="flex items-center space-x-2 hover:text-[#39a900] cursor-pointer p-2">
          <img src="<?= BASE_URL ?>src/assets/img/history.svg" alt="Icono de Trimestres">
          <span>Historial</span>
        </a>
      </li>
    </ul>
  </nav>

  <script src="<?= BASE_URL ?>src/assets/js/header.js"></script>

