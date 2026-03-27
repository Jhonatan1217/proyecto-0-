<?php
/**
 * Vista: Horarios inactivos (Historial).
 * Refactorizada para usar componentes y estilos del proyecto (table-edit, modal-enterprise).
 */
if (!defined('BASE_URL')) {
    $base = '/';
    define('BASE_URL', $base);
}

$cargo = $_SESSION['cargo'] ?? '';
$usuario_cargo = $_SESSION['usuario_cargo'] ?? '';
$puede_limpiar_filtros = $usuario_cargo !== 'INSTRUCTOR';

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../helpers/HistorialHelper.php';

// Resolver nombre de tabla (horario vs horarios)
$tablaHorario = (function () use ($conn) {
    $stmt = $conn->query("SHOW TABLES LIKE 'horario'");
    if ($stmt && $stmt->rowCount() > 0) return 'horario';
    $stmt = $conn->query("SHOW TABLES LIKE 'horarios'");
    return ($stmt && $stmt->rowCount() > 0) ? 'horarios' : 'horario';
})();

$sql = "SELECT * FROM {$tablaHorario} WHERE estado = 0";
$stmt = $conn->prepare($sql);
$stmt->execute();
$horariosInactivos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$e = static function ($s) { return htmlspecialchars((string) ($s ?? ''), ENT_QUOTES, 'UTF-8'); };
?>
<link rel="stylesheet" href="<?= BASE_URL ?>src/assets/css/components/table-edit.css">
<link rel="stylesheet" href="<?= BASE_URL ?>src/assets/css/components/modal-enterprise.css">
<link rel="stylesheet" href="<?= BASE_URL ?>src/assets/css/components/combobox.css">

<div class="max-w-6xl mx-auto px-4 py-10">
  <h1 class="text-4xl font-extrabold tracking-tight mb-2 text-[#39A900]">Horarios Inactivos</h1>
  <p class="text-gray-500 mb-6">Visualice y edite los horarios inactivos del sistema</p>

  <div class="bg-white shadow rounded-2xl border border-gray-200 overflow-hidden">
    <!-- Cabecera de la caja (como en Grupos) -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 p-6 border-b border-gray-100">
      <div class="flex-1">
        <h2 class="text-xl font-semibold text-gray-800">Historial de horarios inactivos</h2>
        <p class="text-sm text-gray-500">Filtra y localiza los horarios inactivos registrados en el sistema.</p>
      </div>
      <div class="flex flex-col items-start md:items-end gap-1">
        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Resultados</span>
        <span id="resultCount" class="text-lg font-bold text-[#39A900]">0 horarios</span>
      </div>
    </div>

    <!-- Bloque de filtros -->
    <div class="px-6 py-4 border-b border-gray-100">
      <div class="flex flex-col md:flex-row md:justify-between gap-6">
        <div class="flex flex-col md:flex-row gap-4 md:gap-6 flex-1 flex-wrap">
          <div class="min-w-[12rem]">
            <div class="relative">
              <select id="filterArea"
                class="w-full input-enterprise filter-select-enterprise rounded-xl pr-10 cursor-pointer">
                <option value="">Todas las áreas</option>
              </select>
            </div>
          </div>
          <div class="min-w-[12rem]">
            <div class="relative">
              <select id="filterZona"
                class="w-full input-enterprise filter-select-enterprise rounded-xl pr-10 cursor-pointer disabled:bg-gray-100 disabled:cursor-not-allowed">
                <option value="">Todas las zonas</option>
              </select>
            </div>
          </div>
          <div class="min-w-[12rem]">
            <div class="relative">
              <select id="filterTrimestre"
                class="w-full input-enterprise filter-select-enterprise rounded-xl pr-10 cursor-pointer">
                <option value="">Todos los trimestres</option>
                <option value="1">Trimestre 1</option>
                <option value="2">Trimestre 2</option>
                <option value="3">Trimestre 3</option>
                <option value="4">Trimestre 4</option>
                <option value="5">Trimestre 5</option>
                <option value="6">Trimestre 6</option>
              </select>
            </div>
          </div>
        </div>
        <div class="flex flex-col md:flex-row gap-4 md:items-end md:justify-start">
          <?php if ($puede_limpiar_filtros): ?>
          <button type="button" id="clearFilters" class="btn-modal-secondary filter-action-secondary whitespace-nowrap px-5">
            Limpiar Filtros
          </button>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Listado de horarios -->
    <div class="p-6 historial-inactivos-wrap">
      <div class="flex flex-col gap-4" id="scheduleContainer">
        <?php if ($horariosInactivos && count($horariosInactivos) > 0): ?>
          <?php foreach ($horariosInactivos as $row): ?>
            <?php
            $nombreFicha = historial_getNombre($conn, 'fichas', 'id_ficha', 'numero_ficha', $row['id_ficha'] ?? null);
            // El módulo cambió de "instructores" a "usuarios".
            // En horarios/horario la FK se mantiene como id_instructor, pero apunta a usuarios.id_usuario.
            $nombreInstructor = historial_getNombre($conn, 'usuarios', 'id_usuario', 'nombre_completo', $row['id_instructor'] ?? null);
            $nombreCompetencia = historial_getNombre($conn, 'competencias', 'id_competencia', 'nombre_competencia', $row['id_competencia'] ?? null);
            $nombrePrograma = historial_getNombre($conn, 'programas', 'id_programa', 'nombre_programa', $row['id_programa'] ?? null);
            // En el sistema la tabla de áreas se llama `area` (no `areas`).
            $nombreArea = historial_getNombre($conn, 'area', 'id_area', 'nombre_area', $row['id_area'] ?? null);
            $descripcionRae = historial_getNombresMultiple($conn, 'raes', 'id_rae', 'descripcion', $row['id_rae'] ?? null);
            ?>
            <div class="schedule-item card-hover-enterprise bg-white rounded-2xl border border-gray-200 p-5 shadow-sm"
                 data-zona="<?= $e($row['id_zona'] ?? '') ?>"
                 data-area-id="<?= $e($row['id_area'] ?? '') ?>"
                 data-area-name="<?= $e($nombreArea) ?>"
                 data-trimestre="<?= $e($row['numero_trimestre'] ?? '') ?>">
              <div class="flex flex-col md:flex-row md:justify-between md:items-start pb-3 md:pb-4 border-b border-gray-100 mb-4">
                <div class="flex items-center gap-3">
                  <span class="text-xs font-medium text-gray-600"><?= $e($row['id_horario'] ?? '') ?></span>
                  <span class="text-lg font-bold text-gray-900"><?= $e(isset($row['dia']) ? ucfirst(strtolower($row['dia'])) : '') ?></span>
                </div>
              </div>
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                <div class="flex flex-col gap-1">
                  <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Hora Inicio y Fin</span>
                  <span class="text-sm font-medium text-gray-900"><?= $e(($row['hora_inicio'] ?? '') . ' - ' . ($row['hora_fin'] ?? '')) ?></span>
                </div>
                <div class="flex flex-col gap-1">
                  <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">ID Zona</span>
                  <span class="text-sm font-medium text-gray-900">Z-<?= $e($row['id_zona'] ?? '') ?></span>
                </div>
                <div class="flex flex-col gap-1">
                  <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Área</span>
                  <span class="text-sm font-medium text-gray-900"><?= $e($nombreArea) ?></span>
                </div>
                <div class="flex flex-col gap-1">
                  <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Ficha</span>
                  <span class="text-sm font-medium text-gray-900"><?= $e($nombreFicha) ?></span>
                </div>
                <div class="flex flex-col gap-1">
                  <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Instructor</span>
                  <span class="text-sm font-medium text-gray-900"><?= $e($nombreInstructor) ?></span>
                </div>
                <div class="flex flex-col gap-1">
                  <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Competencia</span>
                  <span class="text-sm font-medium text-gray-900"><?= $e($nombreCompetencia) ?></span>
                </div>
              </div>
              <div class="flex flex-wrap gap-2 pt-3 border-t border-gray-100">
                <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-semibold uppercase tracking-wider bg-blue-50 text-[#0a3a57]">
                  Trimestre <?= $e($row['numero_trimestre'] ?? '') ?>
                </span>
                <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-semibold uppercase tracking-wider bg-blue-50 text-[#0a3a57]">
                  Programa <?= $e($nombrePrograma) ?>
                </span>
                <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-semibold uppercase tracking-wider bg-blue-50 text-[#0a3a57]">
                  Rae <?= $e($descripcionRae) ?>
                </span>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="bg-white rounded-2xl border border-gray-200 p-5 text-gray-600">
            No hay horarios inactivos.
          </div>
        <?php endif; ?>
      </div>

      <div id="noResults" class="hidden mt-4 bg-white rounded-2xl border border-gray-200 p-5 text-gray-600">
        No hay horarios que coincidan con los filtros seleccionados.
      </div>
    </div>
  </div>
</div>

<script>
window.BASE_URL = <?= json_encode(BASE_URL ?? '') ?>;
(function () {
  document.addEventListener('DOMContentLoaded', function () {
    const filterAreaSelect = document.getElementById('filterArea');
    const filterZonaSelect = document.getElementById('filterZona');
    const filterTrimestreSelect = document.getElementById('filterTrimestre');
    const clearButton = document.getElementById('clearFilters');
    const scheduleItems = document.querySelectorAll('.schedule-item');
    const resultCount = document.getElementById('resultCount');
    const noResults = document.getElementById('noResults');

    const areas = new Map();
    const zonasPorArea = new Map();

    scheduleItems.forEach(function (item) {
      const zona = item.dataset.zona;
      const areaId = item.dataset.areaId;
      const areaName = item.dataset.areaName;
      if (zona) {
        if (!zonasPorArea.has(areaId)) zonasPorArea.set(areaId, new Set());
        zonasPorArea.get(areaId).add(zona);
      }
      if (areaId && areaName) areas.set(areaId, areaName);
    });

    function actualizarEstadoZonas() {
      const areaSeleccionada = filterAreaSelect.value;
      if (!areaSeleccionada) {
        filterZonaSelect.disabled = true;
        filterZonaSelect.value = '';
        filterZonaSelect.innerHTML = '<option value="">Seleccione un área primero</option>';
      } else {
        filterZonaSelect.disabled = false;
        var opts = zonasPorArea.get(areaSeleccionada) || new Set();
        var arr = Array.from(opts).sort(function (a, b) { return parseInt(a, 10) - parseInt(b, 10); });
        filterZonaSelect.innerHTML = '<option value="">Todas las zonas</option>';
        arr.forEach(function (z) {
          var opt = document.createElement('option');
          opt.value = z;
          opt.textContent = 'Zona ' + z;
          filterZonaSelect.appendChild(opt);
        });
      }
    }

    function llenarAreas() {
      filterAreaSelect.innerHTML = '<option value="">Todas las áreas</option>';
      var arr = Array.from(areas.entries()).map(function (e) { return { id: e[0], nombre: e[1] }; }).sort(function (a, b) { return a.nombre.localeCompare(b.nombre); });
      arr.forEach(function (a) {
        var opt = document.createElement('option');
        opt.value = a.id;
        opt.textContent = a.nombre;
        filterAreaSelect.appendChild(opt);
      });
    }

    function applyFilters() {
      var selectedArea = filterAreaSelect.value;
      var selectedZona = filterZonaSelect.value;
      var selectedTrimestre = filterTrimestreSelect.value;
      var visibleCount = 0;
      scheduleItems.forEach(function (item) {
        var areaMatch = !selectedArea || item.dataset.areaId === selectedArea;
        var zonaMatch = !selectedZona || item.dataset.zona === selectedZona;
        var trimestreMatch = !selectedTrimestre || item.dataset.trimestre === selectedTrimestre;
        if (areaMatch && zonaMatch && trimestreMatch) {
          item.classList.remove('hidden');
          visibleCount++;
        } else {
          item.classList.add('hidden');
        }
      });
      if (visibleCount === 0) noResults.classList.remove('hidden');
      else noResults.classList.add('hidden');
      resultCount.textContent = visibleCount === 1 ? '1 horario' : visibleCount + ' horarios';
    }

    filterAreaSelect.addEventListener('change', function () { actualizarEstadoZonas(); applyFilters(); });
    filterZonaSelect.addEventListener('change', applyFilters);
    filterTrimestreSelect.addEventListener('change', applyFilters);

    llenarAreas();
    actualizarEstadoZonas();

    if (clearButton) {
      clearButton.addEventListener('click', function () {
        filterAreaSelect.value = '';
        filterTrimestreSelect.value = '';
        actualizarEstadoZonas();
        llenarAreas();
        applyFilters();
      });
    }

    applyFilters();
  });
})();
</script>

<script src="<?= BASE_URL ?>src/assets/js/components/combobox.js?v=6"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  if (typeof ComboboxComponent === 'undefined') return;

  ComboboxComponent.enhance({
    selector: '#filterArea',
    dropdownClass: 'combobox-dropdown-filtro-area',
    optionClass: 'custom-option',
    placeholder: 'Todas las áreas',
    clearValue: '',
    restoreValueOnBlurWhenEmpty: false
  });

  ComboboxComponent.enhance({
    selector: '#filterZona',
    dropdownClass: 'combobox-dropdown-filtro-zona',
    optionClass: 'custom-option',
    placeholder: 'Todas las zonas',
    clearValue: '',
    restoreValueOnBlurWhenEmpty: false
  });

  ComboboxComponent.enhance({
    selector: '#filterTrimestre',
    dropdownClass: 'combobox-dropdown-filtro-trimestre',
    optionClass: 'custom-option',
    placeholder: 'Todos los trimestres',
    clearValue: '',
    restoreValueOnBlurWhenEmpty: false
  });
});
</script>
