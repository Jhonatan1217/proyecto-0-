<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Proyecto 0</title>

  <!-- SweetAlert2 local -->
  <script src="<?= BASE_URL ?>src/assets/js/sweetalert2.all.min.js"></script>

  <!-- Fuente -->
  <link rel="stylesheet" href="<?= BASE_URL ?>public/css/fonts.css">

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
</head>

<body class="flex flex-col min-h-screen font-sans bg-white text-gray-900">

  <!-- Header -->
  <header class="flex items-center justify-between px-6 py-4 border-b shadow-sm">
    <img src="<?= BASE_URL ?>src/assets/img/logoSena.png" alt="SENA Logo" class="h-10" />

    <!-- Botón imagen menú -->
    <img src="<?= BASE_URL ?>src/assets/img/menu.svg" alt="Menú" id="menu-hamburguesa" class="h-8 w-8 cursor-pointer" />
  </header>

  <!-- Menú lateral -->
  <nav id="menu-lateral" class="fixed top-0 right-0 h-full w-80 bg-white shadow-2xl transform translate-x-full transition-transform duration-300 ease-in-out z-50 pointer-events-none overflow-y-auto">
    <div class="flex justify-between items-center p-4 border-b border-gray-400 mx-4">
      <h2 class="font-semibold text-gray-800 text-xl">Menú de navegación</h2>
      <button id="cerrar-menu" class="text-gray-600 text-2xl hover:text-black">×</button>
    </div>

    <ul class="p-4 space-y-4 text-gray-700">
      <li class="flex items-center space-x-2 hover:text-[#39a900] cursor-pointer p-2">
        <img src="<?= BASE_URL ?>src/assets/img/calendar-days.svg" alt="Icono de Horarios">
        <a href="<?= BASE_URL ?>index.php?page=src/views/register_tables">Horarios</a>
      </li>

      <li class="flex items-center space-x-2 hover:text-[#39a900] cursor-pointer p-2">
        <img src="<?= BASE_URL ?>src/assets/img/map-pin.svg" alt="Icono de Áreas">
        <a href="<?= BASE_URL ?>index.php?page=src/views/gestionAreas">Áreas</a>
      </li>
      
      <li class="flex items-center space-x-2 hover:text-[#39a900] cursor-pointer p-2">
        <img src="<?= BASE_URL ?>src/assets/img/house-plus.svg" alt="Icono de Zonas">
        <a href="<?= BASE_URL ?>index.php?page=src/views/gestionZonas">Zonas</a>
      </li>

      <li class="flex items-center space-x-2 hover:text-[#39a900] cursor-pointer p-2">
        <img src="<?= BASE_URL ?>src/assets/img/layers.svg" alt="Icono de Trimestres">
        <a href="<?= BASE_URL ?>index.php?page=src/views/gestionTrimestres">Trimestres</a>
      </li>

      <li class="flex items-center space-x-2 hover:text-[#39a900] cursor-pointer p-2">
        <img src="<?= BASE_URL ?>src/assets/img/users.svg" alt="Icono de Usuarios">
        <a href="#">Usuarios</a>
      </li>

      <li class="flex items-center space-x-2 hover:text-[#39a900] cursor-pointer p-2">
        <img src="<?= BASE_URL ?>src/assets/img/folder-minus.svg" alt="Icono de Grupos">
        <a href="#">Grupos</a>
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
          <li class="flex items-center space-x-2 hover:text-[#39a900] cursor-pointer p-2">
            <img src="<?= BASE_URL ?>src/assets/img/upload.svg" alt="Icono de Carga Excel" class="w-5 h-5">
            <a href="#">Carga Excel</a>
          </li>
          <li class="flex items-center space-x-2 hover:text-[#39a900] cursor-pointer p-2">
            <img src="<?= BASE_URL ?>src/assets/img/graduation-cap.svg" alt="Icono de Programas" class="w-5 h-5">
            <a href="#">Programas</a>
          </li>
          <li class="flex items-center space-x-2 hover:text-[#39a900] cursor-pointer p-2">
            <img src="<?= BASE_URL ?>src/assets/img/book.svg" alt="Icono de Competencias" class="w-5 h-5">
            <a href="<?= BASE_URL ?>index.php?page=src/views/gestionCompetencias">Competencias</a>
          </li>
          <li class="flex items-center space-x-2 hover:text-[#39a900] cursor-pointer p-2">
            <img src="<?= BASE_URL ?>src/assets/img/target.svg" alt="Icono de RAE" class="w-5 h-5">
            <a href="#">RAE</a>
          </li>
        </ul>
      </li>

      <li class="flex items-center space-x-2 hover:text-[#39a900] cursor-pointer p-2">
        <img src="<?= BASE_URL ?>src/assets/img/clipboard-list.svg" alt="Icono de Solicitudes">
        <a href="<?= BASE_URL ?>index.php?page=src/views/gestionSolicitudes">Solicitudes</a>
      </li>

      <li class="flex items-center space-x-2 hover:text-[#39a900] cursor-pointer p-2">
        <img src="<?= BASE_URL ?>src/assets/img/history.svg" alt="Icono de Trimestres">
        <a href="<?= BASE_URL ?>index.php?page=src/views/historialRegistrosInactivos">Historial</a>
      </li>
    </ul>
  </nav>

  <script src="<?= BASE_URL ?>src/assets/js/header.js"></script>
</body>
</html>
