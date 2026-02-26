<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Horarios Inactivos</title>

    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@400;600;700&display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="flex flex-col min-h-screen font-sans bg-gray-100 text-gray-900">
    <div class="flex-1 max-w-6xl w-full mx-auto px-4 md:px-6 py-6 md:py-8">

        <div class="bg-white rounded-lg shadow-sm p-6 md:p-8 mb-6">
            <h1 class="text-2xl md:text-3xl font-bold mb-2" style="color:#39A900;">Horarios Inactivos</h1>
            <p class="text-gray-600 text-sm">Visualice y edite los horarios inactivos del sistema</p>
        </div>

        <!-- Bloque de filtros -->
        <div class="bg-white rounded-lg shadow-sm p-6 md:p-8 mb-6 border border-gray-200">
            <div class="flex flex-col md:flex-row md:justify-between gap-6">

                <div class="flex flex-col md:flex-row gap-4 md:gap-6 flex-1 flex-wrap">

                    <!-- Filtro Área (PRIMERO) -->
                    <div class="flex flex-col gap-2 min-w-48">
                        <label for="filterArea" class="text-xs font-medium text-gray-600 uppercase tracking-wider">Área</label>
                        <select id="filterArea"
                                class="px-3 py-2 border border-gray-300 rounded-lg bg-white text-sm font-medium text-gray-900 
                                       focus:outline-none focus:ring-2 focus:border-transparent cursor-pointer hover:border-gray-400 transition-colors"
                                style="--tw-ring-color:#39A900;">
                            <option value="">Todas las áreas</option>
                        </select>
                    </div>

                    <!-- Filtro Zona (DEPENDE de área seleccionada) -->
                    <div class="flex flex-col gap-2 min-w-48">
                        <label for="filterZona" class="text-xs font-medium text-gray-600 uppercase tracking-wider">Zona</label>
                        <select id="filterZona"
                                class="px-3 py-2 border border-gray-300 rounded-lg bg-white text-sm font-medium text-gray-900 
                                       focus:outline-none focus:ring-2 focus:border-transparent cursor-pointer hover:border-gray-400 transition-colors disabled:bg-gray-100 disabled:cursor-not-allowed"
                                style="--tw-ring-color:#39A900;">
                            <option value="">Todas las zonas</option>
                        </select>
                    </div>

                    <!-- Filtro Trimestre -->
                    <div class="flex flex-col gap-2 min-w-48">
                        <label for="filterTrimestre" class="text-xs font-medium text-gray-600 uppercase tracking-wider">Trimestre</label>
                        <select id="filterTrimestre"
                                class="px-3 py-2 border border-gray-300 rounded-lg bg-white text-sm font-medium text-gray-900 
                                       focus:outline-none focus:ring-2 focus:border-transparent cursor-pointer hover:border-gray-400 transition-colors"
                                style="--tw-ring-color:#39A900;">
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

                <!-- Acciones y contador -->
                <div class="flex flex-col md:flex-row gap-4 md:items-end">

                    <!-- Botón limpiar -->
                    <button id="clearFilters" class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-900 rounded-lg text-sm font-semibold transition-colors duration-200 whitespace-nowrap h-fit">
                        Limpiar Filtros
                    </button>

                    <!-- Contador -->
                    <div class="flex flex-col gap-1 text-right">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Resultados</span>
                        <span id="resultCount" class="text-lg font-bold" style="color:#39A900;">0 horarios</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Listado de horarios -->
        <div class="flex flex-col gap-4" id="scheduleContainer">
            <?php
            require_once(__DIR__ . '/../../config/database.php');

            // Consulta de horarios inactivos
            $sql = "SELECT * FROM horario WHERE estado = 0";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Función para obtener nombre por ID
            function getNombre($conn, $tabla, $id_col, $nombre_col, $id) {
                if (!$id) return '';
                $sql = "SELECT $nombre_col FROM $tabla WHERE $id_col = ? LIMIT 1";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$id]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                return $row ? $row[$nombre_col] : '';
            }

            // Función para obtener múltiples nombres/descripcion
            function getNombresMultiple($conn, $tabla, $id_col, $nombre_col, $ids) {
                if (!$ids) return '';
                $parts = array_filter(array_map('trim', explode(',', (string)$ids)), function($v) { return $v !== ''; });
                if (!$parts) return '';

                $placeholders = implode(',', array_fill(0, count($parts), '?'));
                $sql = "SELECT $id_col, $nombre_col FROM $tabla WHERE $id_col IN ($placeholders)";
                $stmt = $conn->prepare($sql);
                $stmt->execute(array_values($parts));
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $map = [];
                foreach ($rows as $r) {
                    $map[(string)$r[$id_col]] = $r[$nombre_col];
                }

                $out = [];
                foreach ($parts as $id) {
                    $key = (string)$id;
                    if (isset($map[$key]) && $map[$key] !== '') {
                        $out[] = $map[$key];
                    } else {
                        $out[] = $key;
                    }
                }

                return implode(' || ', $out);
            }
            ?>

            <?php if ($result && count($result) > 0): ?>
                <?php foreach ($result as $row): ?>
                    <?php
                        $nombreFicha = getNombre($conn, 'fichas', 'id_ficha', 'numero_ficha', $row['id_ficha']);
                        $nombreInstructor = getNombre($conn, 'instructores', 'id_instructor', 'nombre_instructor', $row['id_instructor']);
                        $nombreCompetencia = getNombre($conn, 'competencias', 'id_competencia', 'nombre_competencia', $row['id_competencia']);
                        $nombrePrograma = getNombre($conn, 'programas', 'id_programa', 'nombre_programa', $row['id_programa']);
                        $nombreArea = getNombre($conn, 'areas', 'id_area', 'nombre_area', $row['id_area']);
                        $descripcionRae = getNombresMultiple($conn, 'raes', 'id_rae', 'descripcion', $row['id_rae']);
                    ?>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5 hover:shadow-md hover:translate-y-[-2px] transition-all duration-200 schedule-item"
                         data-zona="<?php echo htmlspecialchars($row['id_zona']); ?>"
                         data-area-id="<?php echo htmlspecialchars($row['id_area']); ?>"
                         data-area-name="<?php echo htmlspecialchars($nombreArea); ?>"
                         data-trimestre="<?php echo htmlspecialchars($row['numero_trimestre']); ?>">

                        <div class="flex flex-col md:flex-row md:justify-between md:items-start pb-3 md:pb-4 border-b border-gray-100 mb-4">
                            <div class="flex items-center gap-3">
                                <span class="text-xs font-medium text-gray-600"><?php echo htmlspecialchars($row['id_horario']); ?></span>
                                <span class="text-lg font-bold text-gray-900"><?php echo htmlspecialchars(ucfirst(strtolower($row['dia']))); ?></span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                            <div class="flex flex-col gap-1">
                                <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Hora Inicio y Fin</span>
                                <span class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($row['hora_inicio'] . ' - ' . $row['hora_fin']); ?></span>
                            </div>

                            <div class="flex flex-col gap-1">
                                <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">ID Zona</span>
                                <span class="text-sm font-medium text-gray-900">Z-<?php echo htmlspecialchars($row['id_zona']); ?></span>
                            </div>

                            <div class="flex flex-col gap-1">
                                <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Área</span>
                                <span class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($nombreArea); ?></span>
                            </div>

                            <div class="flex flex-col gap-1">
                                <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Ficha</span>
                                <span class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($nombreFicha); ?></span>
                            </div>

                            <div class="flex flex-col gap-1">
                                <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Instructor</span>
                                <span class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($nombreInstructor); ?></span>
                            </div>

                            <div class="flex flex-col gap-1">
                                <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Competencia</span>
                                <span class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($nombreCompetencia); ?></span>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2 pt-3 border-t border-gray-100">
                            <span class="inline-flex items-center px-3 py-1 rounded text-xs font-semibold uppercase tracking-wider bg-blue-50 text-[#0a3a57]">
                                Trimestre <?php echo htmlspecialchars($row['numero_trimestre']); ?>
                            </span>
                            <span class="inline-flex items-center px-3 py-1 rounded text-xs font-semibold uppercase tracking-wider bg-blue-50 text-[#0a3a57]">
                                Programa <?php echo htmlspecialchars($nombrePrograma); ?>
                            </span>
                            <span class="inline-flex items-center px-3 py-1 rounded text-xs font-semibold uppercase tracking-wider bg-blue-50 text-[#0a3a57]">
                                Rae <?php echo htmlspecialchars($descripcionRae); ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>

            <?php else: ?>
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                    <span class="text-gray-600">No hay horarios inactivos.</span>
                </div>
            <?php endif; ?>
        </div>

        <div id="noResults" class="hidden bg-white rounded-lg shadow-sm border border-gray-200 p-5 mt-[15px]">
            <span class="text-gray-600">No hay horarios que coincidan con los filtros seleccionados.</span>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filterAreaSelect = document.getElementById('filterArea');
            const filterZonaSelect = document.getElementById('filterZona');
            const filterTrimestreSelect = document.getElementById('filterTrimestre');
            const clearButton = document.getElementById('clearFilters');
            const scheduleItems = document.querySelectorAll('.schedule-item');
            const resultCount = document.getElementById('resultCount');
            const noResults = document.getElementById('noResults');
            const scheduleContainer = document.getElementById('scheduleContainer');

            // Estructuras de datos para las relaciones
            const zonas = new Set();
            const areas = new Map();
            const zonasPorArea = new Map();

            // Recolectar datos de los horarios
            scheduleItems.forEach(item => {
                const zona = item.dataset.zona;
                const areaId = item.dataset.areaId;
                const areaName = item.dataset.areaName;

                if (zona) {
                    zonas.add(zona);
                }

                if (areaId && areaName) {
                    areas.set(areaId, areaName);
                    
                    if (!zonasPorArea.has(areaId)) {
                        zonasPorArea.set(areaId, new Set());
                    }
                    zonasPorArea.get(areaId).add(zona);
                }
            });

            // Función para actualizar estado del select de zonas
            function actualizarEstadoZonas() {
                const areaSeleccionada = filterAreaSelect.value;
                
                if (!areaSeleccionada) {
                    // Si no hay área seleccionada, deshabilitar zonas y limpiar selección
                    filterZonaSelect.disabled = true;
                    filterZonaSelect.value = '';
                    filterZonaSelect.innerHTML = '<option value="">Seleccione un área primero</option>';
                } else {
                    // Si hay área seleccionada, habilitar zonas y llenar con opciones
                    filterZonaSelect.disabled = false;
                    llenarZonas(areaSeleccionada);
                }
            }

            // Función para llenar select de zonas según el área seleccionada
            function llenarZonas(areaSeleccionada) {
                filterZonaSelect.innerHTML = '<option value="">Todas las zonas</option>';
                
                const zonasDeArea = zonasPorArea.get(areaSeleccionada) || new Set();
                const zonasArray = Array.from(zonasDeArea).sort((a, b) => parseInt(a) - parseInt(b));
                
                zonasArray.forEach(zona => {
                    const option = document.createElement('option');
                    option.value = zona;
                    option.textContent = `Zona ${zona}`;
                    filterZonaSelect.appendChild(option);
                });
            }

            // Función para llenar select de áreas
            function llenarAreas() {
                filterAreaSelect.innerHTML = '<option value="">Todas las áreas</option>';
                
                const areasArray = Array.from(areas.entries())
                    .map(([id, nombre]) => ({ id, nombre }))
                    .sort((a, b) => a.nombre.localeCompare(b.nombre));
                
                areasArray.forEach(area => {
                    const option = document.createElement('option');
                    option.value = area.id;
                    option.textContent = area.nombre;
                    filterAreaSelect.appendChild(option);
                });
            }

            // Evento para cuando cambia el área
            filterAreaSelect.addEventListener('change', function() {
                actualizarEstadoZonas();
                applyFilters();
            });

            filterZonaSelect.addEventListener('change', applyFilters);
            filterTrimestreSelect.addEventListener('change', applyFilters);

            // Llenar áreas inicialmente
            llenarAreas();
            
            // Configurar estado inicial de zonas (deshabilitado)
            actualizarEstadoZonas();

            function applyFilters() {
                const selectedArea = filterAreaSelect.value;
                const selectedZona = filterZonaSelect.value;
                const selectedTrimestre = filterTrimestreSelect.value;
                let visibleCount = 0;

                scheduleItems.forEach(item => {
                    const areaMatch = !selectedArea || item.dataset.areaId === selectedArea;
                    const zonaMatch = !selectedZona || item.dataset.zona === selectedZona;
                    const trimestreMatch = !selectedTrimestre || item.dataset.trimestre === selectedTrimestre;

                    if (areaMatch && zonaMatch && trimestreMatch) {
                        item.classList.remove('hidden');
                        visibleCount++;
                    } else {
                        item.classList.add('hidden');
                    }
                });

                if (visibleCount === 0) {
                    noResults.classList.remove('hidden');
                } else {
                    noResults.classList.add('hidden');
                }

                const word = visibleCount === 1 ? 'horario' : 'horarios';
                resultCount.textContent = `${visibleCount} ${word}`;
            }

            clearButton.addEventListener('click', function() {
                filterAreaSelect.value = '';
                filterTrimestreSelect.value = '';
                
                actualizarEstadoZonas();
                llenarAreas();
                
                applyFilters();
            });

            applyFilters();
        });
    </script>
</body>
</html>