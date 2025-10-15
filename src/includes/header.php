<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Document</title>

  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- Tailwind -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Fuente -->
  <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@400;600;700&display=swap" rel="stylesheet"/>

  <!-- Tu CSS -->
  <link rel="stylesheet" href="src/assets/css/formulario_crear_trimestralizacion.css">
</head>

<body class="flex flex-col min-h-screen font-sans bg-white text-gray-900">

  <!-- Barra superior -->
  <header class="flex items-center justify-between px-6 py-4 border-b shadow-sm">
    <img src="src/assets/img/logoSena.png" alt="SENA Logo" class="h-10" />

    <!-- Imagen del menú -->
    <img src="src/assets/img/menu.svg" alt="Menú" id="menu-hamburguesa" class="h-8 w-8 cursor-pointer block" />
    
    <!-- Menú -->
    <nav id="menu" class="hidden absolute top-16 right-4 bg-white shadow-lg rounded-lg border w-48 text-left">
      <ul class="flex flex-col divide-y">
        <li><a href="#" class="block px-4 py-2 hover:bg-gray-100">Inicio</a></li>
        <li><a href="#" class="block px-4 py-2 hover:bg-gray-100">Trimestralización</a></li>
        <li><a href="#" class="block px-4 py-2 hover:bg-gray-100">Zonas</a></li>
        <li><a href="#" class="block px-4 py-2 hover:bg-gray-100">Cerrar sesión</a></li>
      </ul>
    </nav>
  </header>

  <!-- Script del menú -->
  <script src="src/assets/js/header.js"></script>

</body>
</html>
