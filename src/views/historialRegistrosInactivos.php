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
        <!-- Header section with green title matching SENA branding -->
        <div class="bg-white rounded-lg shadow-sm p-6 md:p-8 mb-6">
            <h1 class="text-2xl md:text-3xl font-bold text-green-500 mb-2">Horarios Inactivos</h1>
            <p class="text-gray-600 text-sm">Visualice y edite los horarios inactivos del sistema</p>
        </div>

        <!-- Card-based layout using Tailwind flexbox -->
        <div class="flex flex-col gap-4">
            <?php
            require_once(__DIR__ . '/../../config/database.php');
            // Consulta para obtener horarios inactivos usando PDO
            $sql = "SELECT * FROM horarios WHERE estado = 0";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <?php if ($result && count($result) > 0): ?>
                <?php foreach ($result as $row): ?>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5 hover:shadow-md hover:translate-y-[-2px] transition-all duration-200">
                        <div class="flex flex-col md:flex-row md:justify-between md:items-start pb-3 md:pb-4 border-b border-gray-100 mb-4">
                            <div class="flex items-center gap-3">
                                <span class="text-xs font-medium text-gray-600"><?php echo $row['id_horario']; ?></span>
                                <span class="text-lg font-bold text-gray-900"><?php echo ucfirst(strtolower($row['dia'])); ?></span>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                            <div class="flex flex-col gap-1">
                                <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Hora Inicio y Fin</span>
                                <span class="text-sm font-medium text-gray-900"><?php echo $row['hora_inicio'] . ' - ' . $row['hora_fin']; ?></span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">ID Zona</span>
                                <span class="text-sm font-medium text-gray-900">Z-<?php echo $row['id_zona']; ?></span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Area</span>
                                <span class="text-sm font-medium text-gray-900">Area <?php echo $row['id_area']; ?></span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Ficha</span>
                                <span class="text-sm font-medium text-gray-900">Ficha <?php echo $row['id_ficha']; ?></span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Instructor</span>
                                <span class="text-sm font-medium text-gray-900">Instructor <?php echo $row['id_instructor']; ?></span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Competencia</span>
                                <span class="text-sm font-medium text-gray-900">Competencia <?php echo $row['id_competencia']; ?></span>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2 pt-3 border-t border-gray-100">
                            <span class="inline-flex items-center px-3 py-1 rounded text-xs font-semibold uppercase tracking-wider bg-blue-50 text-blue-600">Trimestre <?php echo $row['numero_trimestre']; ?></span>
                            <span class="inline-flex items-center px-3 py-1 rounded text-xs font-semibold uppercase tracking-wider bg-blue-50 text-blue-600">Programa <?php echo $row['id_programa']; ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                    <span class="text-gray-600">No hay horarios inactivos.</span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
