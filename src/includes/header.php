<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Proyecto 0</title>

  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- Tailwind -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Fuente -->
  <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@400;600;700&display=swap" rel="stylesheet"/>
</head>

<body class="flex flex-col min-h-screen font-sans bg-white text-gray-900">

  <!-- Header -->
  <header class="flex items-center justify-between px-6 py-4 border-b shadow-sm">
    <img src="src/assets/img/logoSena.png" alt="SENA Logo" class="h-10" />

    <!-- Botón imagen menú -->
    <img src="src/assets/img/menu.svg" alt="Menú" id="menu-hamburguesa" class="h-8 w-8 cursor-pointer" />
  </header>

  <!-- Menú lateral -->
  <nav id="menu-lateral" class="fixed top-0 right-0 h-full w-80 bg-white shadow-2xl transform translate-x-full transition-transform duration-300 ease-in-out z-50 pointer-events-none">
    <div class="flex justify-between items-center p-4 border-b border-gray-400 mx-4">
      <h2 class="font-semibold text-gray-800 text-xl">Menú de navegación</h2>
      <button id="cerrar-menu" class="text-gray-600 text-2xl hover:text-black">×</button>
    </div>
    <ul class="p-4 space-y-4 text-gray-700">
      <li class="flex items-center space-x-2 hover:text-green-500 cursor-pointer p-2">
        <img src="src/assets/img/map-pin.svg" alt="Icono de Zonas">
        <a href="#">Zonas</a>
      </li>
      <li class="flex items-center space-x-2 hover:text-green-500 cursor-pointer p-2">
        <img src="src/assets/img/users.svg" alt="Icono de Instructores">
        <a href="#">Instructores</a>
      </li>
      <li class="flex items-center space-x-2 hover:text-green-500 cursor-pointer p-2">
        <img src="src/assets/img/house-plus.svg" alt="Icono no Áreas">
        <a href="#">Áreas</a>
      </li>
      <li class="flex items-center space-x-2 hover:text-green-500 cursor-pointer p-2">
        <img src="src/assets/img/calendar-days.svg" alt="Icono de Horarios">
        <a href="#">Horarios</a>
      </li>
      <li class="flex items-center space-x-2 hover:text-green-500 cursor-pointer p-2">
        <img src="src/assets/img/calendar-range.svg" alt="Icono de Trimestres">
        <a href="#">Trimestres</a>
      </li>
    </ul>
  </nav>

  <script src="src/assets/js/header.js"></script>
</body>
</html>
