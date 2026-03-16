<?php /* views/gestionAreas.php - Refactorizado: table-edit + modal-enterprise */ ?>
<?php
$cargo = $_SESSION['cargo'] ?? '';
if ($cargo === 'INSTRUCTOR') {
    header("Location: index.php?page=register_tables");
    exit;
}
if (!defined('BASE_URL')) { $base = '/'; define('BASE_URL', $base); }
?>
<link rel="stylesheet" href="<?= BASE_URL ?>src/assets/css/components/table-edit.css">
<link rel="stylesheet" href="<?= BASE_URL ?>src/assets/css/components/modal-enterprise.css">
<style>
.table-areas-wrap table tbody tr { height: 56px; }
.table-areas-wrap table tbody td { height: 56px; }
.table-areas-wrap tr.editando input.cell-edit { width: 14ch; min-width: 10ch; max-width: 24ch; }
</style>

<div class="max-w-6xl mx-auto px-4 py-10">
  <h1 class="text-4xl font-extrabold tracking-tight mb-2 text-[#39A900]">Gestión de Áreas</h1>
  <p class="text-gray-500 mb-6">Administra las áreas</p>

  <div class="bg-white shadow rounded-2xl border border-gray-200">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 p-6">
      <div>
        <h2 class="text-xl font-semibold text-gray-800">Áreas</h2>
        <p class="text-sm text-gray-500">Administra las áreas</p>
      </div>
      <button id="btnAbrirModalArea" type="button"
        class="w-full md:w-auto bg-[#0a3a57] hover:bg-[#00304D] active:scale-95 transition text-white px-5 py-2.5 rounded-xl flex items-center justify-center gap-2 shadow-sm">
        <img class="w-4" src="<?= BASE_URL ?>src/assets/img/plus.svg" alt="Agregar" />
        <span class="text-sm font-medium">Nueva Área</span>
      </button>
    </div>

    <div class="px-6 py-4">
      <div class="flex flex-col md:flex-row gap-4">
        <div id="buscadorAreaWrap" class="relative w-full md:w-64">
          <input type="text" id="buscadorArea" placeholder="Buscar área por nombre..."
            class="w-full rounded-xl border border-gray-300 bg-white pl-10 pr-10 py-2.5 text-sm focus:ring-2 focus:ring-[#39A900]/20 focus:border-[#39A900] outline-none transition hover:border-gray-400" />
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>
      </div>
    </div>

    <div class="table-areas-wrap overflow-x-auto">
      <table class="w-full text-left" id="tablaAreas">
        <thead class="bg-gray-50 border-b border-gray-200 text-sm text-gray-600">
          <tr>
            <th class="px-6 py-3 font-medium">Nombre Área</th>
            <th class="px-6 py-3 font-medium text-right" style="min-width:120px">Acciones</th>
          </tr>
        </thead>
        <tbody id="tbodyAreas" class="text-sm divide-y divide-gray-100"></tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Nueva Área -->
<div id="modalArea" role="dialog" aria-labelledby="modalAreaTitle" aria-modal="true"
  class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 modal-area-overlay hidden">
  <div class="modal-area-box bg-white w-full max-w-xl rounded-2xl flex flex-col overflow-hidden">
    <header class="modal-area-header flex items-center justify-between">
      <h2 id="modalAreaTitle" class="text-xl font-bold text-[#39A900] tracking-tight">Nueva Área</h2>
      <button type="button" id="btnCerrarModalArea" aria-label="Cerrar"
        class="p-2 -m-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </header>
    <form id="formNuevaArea" class="flex flex-col flex-1 min-h-0">
      <div class="modal-area-body flex-1">
        <div class="space-y-5">
          <div>
            <label for="nombre_area" class="label-enterprise">Nombre del Área</label>
            <input type="text" name="nombre_area" id="nombre_area" placeholder="Ej: Área Polivalente" class="input-enterprise" />
          </div>
        </div>
      </div>
      <footer class="modal-area-footer flex justify-end gap-3">
        <button type="button" id="btnCancelarModalArea" class="btn-modal-secondary">Cancelar</button>
        <button type="submit" class="btn-modal-primary">Crear Área</button>
      </footer>
    </form>
  </div>
</div>

<script>
window.API_URL = "<?= BASE_URL ?>src/controllers/AreaController.php";
window.ICON_PENCIL_AREA = "<?= BASE_URL ?>src/assets/img/pencil-line.svg";
</script>
<script src="<?= BASE_URL ?>src/assets/js/gestionAreas.js?v=2"></script>