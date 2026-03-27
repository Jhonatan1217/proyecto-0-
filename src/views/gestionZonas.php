<?php /* views/gestionZonas.php */ ?>
<?php
$cargo = $_SESSION['cargo'] ?? '';
if ($cargo === 'INSTRUCTOR') {
  header('Location: index.php?page=register_tables');
  exit;
}
if (!defined('BASE_URL')) {
  $base = '/';
  define('BASE_URL', $base);
}
?>
<link rel="stylesheet" href="<?= BASE_URL ?>src/assets/css/components/combobox.css">
<link rel="stylesheet" href="<?= BASE_URL ?>src/assets/css/components/table-edit.css">
<link rel="stylesheet" href="<?= BASE_URL ?>src/assets/css/components/modal-enterprise.css">
<style>
.table-zonas { table-layout: fixed; min-width: 36rem; width: 100%; }
.table-zonas .col-nombre { min-width: 12rem; }
.table-zonas .col-area { min-width: 12rem; }
.table-zonas .col-acciones { min-width: 8rem; white-space: nowrap; }
.table-zonas tr.editando input.cell-edit.nombre-zona { width: 100%; min-width: 8rem; max-width: 20rem; }
</style>

<div class="max-w-6xl mx-auto px-4 py-10">
  <h1 class="text-4xl font-extrabold tracking-tight mb-2 text-[#39A900]">Zonas</h1>
  <p class="text-gray-500 mb-6">Administra las zonas</p>

  <div class="bg-white shadow rounded-2xl border border-gray-200">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 p-6">
      <div>
        <h2 class="text-xl font-semibold text-gray-800">Zonas</h2>
        <p class="text-sm text-gray-500">Lista de zonas registradas</p>
      </div>
      <button id="btnAbrirModalZonas" type="button"
        class="w-full md:w-auto bg-[#0a3a57] hover:bg-[#00304D] active:scale-95 transition text-white px-5 py-2.5 rounded-xl flex items-center justify-center gap-2 shadow-sm">
        <span>+ Nueva Zona</span>
      </button>
    </div>

    <div class="px-6 py-4">
      <div class="flex flex-col md:flex-row gap-4">
        <div id="filtroAreaWrap" class="relative w-full md:w-64">
          <select id="filtroArea" class="w-full appearance-none rounded-xl border border-gray-300 bg-white px-4 py-2.5 pr-10 text-sm focus:ring-2 focus:ring-[#39A900]/20 focus:border-[#39A900] outline-none transition" autocomplete="off">
            <option value="todas">Todas las áreas</option>
          </select>
          <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none filtro-area-chevron" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.18l3.71-3.95a.75.75 0 111.08 1.04l-4.24 4.52a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
          </svg>
        </div>
        <div class="relative w-full md:w-64">
          <input type="text" id="buscadorZonas" placeholder="Buscar zona..." autocomplete="off"
            class="w-full rounded-xl border border-gray-300 bg-white pl-10 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-[#39A900]/20 focus:border-[#39A900] outline-none transition" />
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
          </svg>
        </div>
      </div>
    </div>

    <div id="wrapTablaZonas" class="table-zonas-wrap overflow-x-auto">
      <table class="w-full text-left table-zonas" id="tablaZonas">
        <thead class="bg-gray-50 border-b border-gray-200 text-sm text-gray-600">
          <tr>
            <th class="font-medium col-nombre">Nombre de Zona</th>
            <th class="font-medium col-area text-center">Área</th>
            <th class="font-medium text-right col-acciones">Acciones</th>
          </tr>
        </thead>
        <tbody id="tbodyZonas" class="text-sm divide-y divide-gray-100"></tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Nueva Zona -->
<div id="modalZonas" role="dialog" aria-labelledby="modalZonasTitle" aria-modal="true"
  class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 modal-zona-overlay hidden">
  <div id="modalBackdrop" class="absolute inset-0 -z-0" aria-hidden="true"></div>
  <div class="modal-zona-box bg-white w-full max-w-xl rounded-2xl flex flex-col overflow-hidden relative z-10">
    <header class="modal-zona-header flex items-center justify-between">
      <h2 id="modalZonasTitle" class="text-xl font-bold text-[#39A900] tracking-tight">Nueva Zona</h2>
      <button type="button" id="btnCerrarModalZonas" aria-label="Cerrar"
        class="p-2 -m-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </header>
    <form id="formNuevaZona" class="flex flex-col flex-1 min-h-0" autocomplete="off">
      <div class="modal-zona-body flex-1">
        <div class="space-y-5">
          <div>
            <label for="nombre_zona" class="label-enterprise">Nombre de la Zona</label>
            <input type="text" name="nombre_zona" id="nombre_zona" maxlength="125" required placeholder="Ej: Zona 1" class="input-enterprise" autocomplete="off" />
          </div>
          <div>
            <label for="id_area" class="label-enterprise">Área perteneciente</label>
            <div id="id_areaWrap">
              <select name="id_area" id="id_area" required class="select-zona input-enterprise w-full" autocomplete="off">
                <option disabled selected value="">Seleccione un Área</option>
              </select>
            </div>
          </div>
        </div>
      </div>
      <footer class="modal-zona-footer flex justify-end gap-3">
        <button type="button" id="btnCancelarModalZonas" class="btn-modal-secondary">Cancelar</button>
        <button type="submit" class="btn-modal-primary">Crear Zona</button>
      </footer>
    </form>
  </div>
</div>

<script>
window.BASE_URL = <?= json_encode(BASE_URL ?? '') ?>;
</script>
<script src="<?= BASE_URL ?>src/assets/js/components/combobox.js?v=6"></script>
<script src="<?= BASE_URL ?>src/assets/js/gestionZonas.js?v=2"></script>
