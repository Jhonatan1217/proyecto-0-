<?php /* views/gestionUsuarios.php - Refactorizado: combobox + table-edit + modal-enterprise */ ?>
<?php
if (!defined('BASE_URL')) { $base = '/senlock/'; define('BASE_URL', $base); }
?>
<link rel="stylesheet" href="<?= BASE_URL ?>src/assets/css/components/combobox.css">
<link rel="stylesheet" href="<?= BASE_URL ?>src/assets/css/components/select-styled.css">
<link rel="stylesheet" href="<?= BASE_URL ?>src/assets/css/components/table-edit.css">
<link rel="stylesheet" href="<?= BASE_URL ?>src/assets/css/components/modal-enterprise.css">
<link rel="stylesheet" href="<?= BASE_URL ?>src/assets/css/components/gestionUsuarios-usuario.css">

<div class="max-w-6xl mx-auto px-4 py-10">
  <h1 class="text-4xl font-extrabold tracking-tight mb-2 text-[#39A900]">Gestión de Usuarios</h1>
  <p class="text-gray-500 mb-6">Administra los Usuarios</p>

  <div class="bg-white shadow rounded-2xl border border-gray-200">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 p-6">
      <div>
        <h2 class="text-xl font-semibold text-gray-800">Usuarios</h2>
        <p class="text-sm text-gray-500">Lista de todos los usuarios registrados</p>
      </div>
      <button id="btnAbrirModalUsuario" type="button"
        class="w-full md:w-auto bg-[#0a3a57] hover:bg-[#00304D] active:scale-95 transition text-white px-5 py-2.5 rounded-xl flex items-center justify-center gap-2 shadow-sm">
        Nuevo Usuario
      </button>
    </div>

    <div class="px-6 py-4 border-b">
      <div class="flex flex-col md:flex-row gap-4">
        <div id="filtroCargosWrap" class="relative w-full md:w-64">
          <select id="filtroCargos" class="select-usuario w-full appearance-none rounded-xl border border-gray-300 bg-white px-4 py-2.5 pr-10 text-sm text-gray-700 focus:ring-2 focus:ring-[#39A900]/20 focus:border-[#39A900] outline-none transition hover:border-gray-400">
            <option value="">Todos los cargos</option>
            <option value="Instructor">Instructor</option>
            <option value="Coordinador">Coordinador</option>
          </select>
          <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none select-usuario-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </div>
        <div id="filtroRolesWrap" class="relative w-full md:w-64">
          <select id="filtroRoles" class="select-usuario w-full appearance-none rounded-xl border border-gray-300 bg-white px-4 py-2.5 pr-10 text-sm text-gray-700 focus:ring-2 focus:ring-[#39A900]/20 focus:border-[#39A900] outline-none transition hover:border-gray-400">
            <option value="">Todos los roles</option>
          </select>
          <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none select-usuario-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </div>
        <div id="buscadorUsuarioWrap" class="relative w-full md:w-64">
          <input type="text" id="buscadorUsuario" placeholder="Buscar usuario..."
            class="w-full rounded-xl border border-gray-300 bg-white pl-10 pr-10 py-2.5 text-sm focus:ring-2 focus:ring-[#39A900]/20 focus:border-[#39A900] outline-none transition" />
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>
      </div>
    </div>

    <div id="errorTablaUsuarios" class="hidden alert-error mx-6 mb-4" role="alert">
      <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
      <span class="alert-error-text"></span>
    </div>

    <div class="table-usuarios-wrap overflow-x-auto">
      <table class="w-full text-left">
        <thead class="bg-gray-50 border-b border-gray-200 text-sm text-gray-600">
          <tr>
            <th class="px-6 py-3 font-medium">Número de Documento</th>
            <th class="px-6 py-3 font-medium">Nombre Completo</th>
            <th class="px-6 py-3 font-medium">Cargo</th>
            <th class="px-6 py-3 font-medium">Correo electrónico</th>
            <th class="px-6 py-3 font-medium text-right">Acciones</th>
          </tr>
        </thead>
        <tbody id="tbodyUsuarios" class="text-sm divide-y divide-gray-100"></tbody>
      </table>
    </div>
  </div>
</div>

<?php require __DIR__ . '/partials/modalesUsuarios.php'; ?>

<script>
window.API_USUARIO = "<?= BASE_URL ?>src/controllers/UsuarioController.php";
window.ICON_EDIT_USUARIO = "<?= BASE_URL ?>src/assets/img/pencil.svg";
window.ICON_VER_USUARIO = "<?= BASE_URL ?>src/assets/img/eye.svg";
</script>
<script src="<?= BASE_URL ?>src/assets/js/components/combobox.js"></script>
<script src="<?= BASE_URL ?>src/assets/js/gestionUsuarios.js"></script>
