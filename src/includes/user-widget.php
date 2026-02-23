<?php
$nombre = $_SESSION['user_name'] ?? 'Usuario';
$rol = $_SESSION['user_role'] ?? 'Invitado';
?>

<div class="relative flex items-center space-x-3 border-r pr-4 border-gray-200">

  <button onclick="toggleUserMenu()" class="flex items-center space-x-3 focus:outline-none">
    
    <div class="text-right hidden sm:block">
      <p class="text-sm font-semibold text-gray-800 leading-none">
        <?= htmlspecialchars($nombre) ?>
      </p>
      <p class="text-xs text-gray-500">
        <?= htmlspecialchars($rol) ?>
      </p>
    </div>

    <div class="h-9 w-9 bg-[#39a900] text-white rounded-full flex items-center justify-center font-bold">
      <?= strtoupper(substr($nombre, 0, 1)) ?>
    </div>
  </button>

  <!-- Dropdown -->
  <div id="userMenu" class="hidden absolute right-0 top-12 w-48 bg-white border border-gray-200 rounded-lg shadow-lg z-50">
    <a href="/perfil" class="block px-4 py-2 text-sm hover:bg-gray-100">
      Mi perfil
    </a>
    <a href="/cambiar-password" class="block px-4 py-2 text-sm hover:bg-gray-100">
      Cambiar contraseña
    </a>
    <hr>
    <a href="/logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
      Cerrar sesión
    </a>
  </div>

</div>

<script>
function toggleUserMenu() {
  document.getElementById("userMenu").classList.toggle("hidden");
}
</script>