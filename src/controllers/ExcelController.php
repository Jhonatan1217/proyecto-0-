<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once __DIR__ . '/../../config/database.php';

// ===============================================================
// SUBIR Y LEER ARCHIVO EXCEL (CSV SIN LIBRERÍA)
// ===============================================================

// Solo acepta método POST con un archivo adjunto
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Debe usar el método POST para subir el archivo']);
    exit;
}

if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['error' => 'No se recibió ningún archivo o hubo un error al subirlo']);
    exit;
}

// Ruta temporal
$archivo_tmp = $_FILES['archivo']['tmp_name'];

// Abrir el archivo CSV
$handle = fopen($archivo_tmp, 'r');

if ($handle === false) {
    echo json_encode(['error' => 'No se pudo leer el archivo']);
    exit;
}

// Leer encabezado (primera fila)
$encabezado = fgetcsv($handle, 1000, ',');

// Validar que tenga las columnas necesarias
$requeridas = ['nombre_competencia', 'codigo_competencia', 'descripcion_competencia', 'codigo_rae', 'descripcion_rae'];
foreach ($requeridas as $columna) {
    $encontrada = false;
    foreach ($encabezado as $enc) {
        if (trim(strtolower($enc)) === $columna) {
            $encontrada = true;
            break;
        }
    }
    if (!$encontrada) {
        echo json_encode(['error' => "Falta la columna '$columna' en el archivo"]);
        fclose($handle);
        exit;
    }
}

// Leer los datos
$datos = [];
while (($fila = fgetcsv($handle, 1000, ',')) !== false) {
    // Saltar filas vacías
    if (count(array_filter($fila)) === 0) continue;

    $registro = [
        'nombre_competencia' => trim($fila[0]),
        'codigo_competencia' => trim($fila[1]),
        'descripcion_competencia' => trim($fila[2]),
        'codigo_rae' => trim($fila[3]),
        'descripcion_rae' => trim($fila[4])
    ];

    $datos[] = $registro;
}

fclose($handle);

// ===============================================================
// GUARDAR EN BASE DE DATOS
// ===============================================================
$insertados = 0;
$errores = [];

foreach ($datos as $fila) {
    try {
        // Buscar si la competencia ya existe (por código)
        $stmt = $conn->prepare("SELECT id_competencia FROM competencias WHERE codigo_competencia = ?");
        $stmt->execute([$fila['codigo_competencia']]);
        $competencia = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$competencia) {
            // Crear competencia si no existe
            $stmt = $conn->prepare("INSERT INTO competencias (codigo_competencia, nombre_competencia, descripcion, estado) VALUES (?, ?, ?, 1)");
            $stmt->execute([
                $fila['codigo_competencia'],
                $fila['nombre_competencia'],
                $fila['descripcion_competencia']
            ]);
            $id_competencia = $conn->lastInsertId();
        } else {
            $id_competencia = $competencia['id_competencia'];
        }

        // Insertar la RAE correspondiente
        $stmt = $conn->prepare("INSERT INTO raes (codigo_rae, descripcion, id_competencia, estado) VALUES (?, ?, ?, 1)");
        $stmt->execute([
            $fila['codigo_rae'],
            $fila['descripcion_rae'],
            $id_competencia
        ]);

        $insertados++;
    } catch (Exception $e) {
        $errores[] = [
            'fila' => $fila,
            'error' => $e->getMessage()
        ];
    }
}

echo json_encode([
    'success' => true,
    'insertados' => $insertados,
    'errores' => $errores
]);
?>
