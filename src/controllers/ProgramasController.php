<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config/database.php';

// ===============================
// VALIDAR CONEXIÓN
// ===============================
if (!isset($conn)) {
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit;
}

// ===============================
// DETECTAR ACCIÓN
// ===============================
$accion = $_GET['accion'] ?? '';

// ===============================
// FUNCIONES AUXILIARES
// ===============================
function limpiar($v) {
    return htmlspecialchars(trim($v ?? ''), ENT_QUOTES, 'UTF-8');
}

// ===============================
// LISTAR
// ===============================
if ($accion === 'listar') {
    try {
        $sql = "SELECT id_programa, nombre_programa, descripcion, duracion, estado FROM programas ORDER BY nombre_programa ASC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($data ?: []);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// ===============================
// AGREGAR
// ===============================
if ($accion === 'agregar') {
    $json = json_decode(file_get_contents('php://input'), true);
    $id_programa     = limpiar($json['id_programa'] ?? '');
    $nombre_programa = limpiar($json['nombre_programa'] ?? '');
    $descripcion     = limpiar($json['descripcion'] ?? '');
    $duracion        = limpiar($json['duracion'] ?? '');

    if (!$id_programa || !$nombre_programa) {
        echo json_encode(['error' => 'Campos obligatorios faltantes.']);
        exit;
    }

    try {
        $sql = "INSERT INTO programas (id_programa, nombre_programa, descripcion, duracion, estado) 
                VALUES (?, ?, ?, ?, 1)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id_programa, $nombre_programa, $descripcion, $duracion]);
        echo json_encode(['success' => 'Programa agregado correctamente.']);
    } catch (PDOException $e) {
        // Detectar error de clave duplicada (código 1062)
        if (strpos($e->getMessage(), '1062') !== false) {
            echo json_encode(['error' => 'Ya existe un programa con ese código.']);
        } else {
            echo json_encode(['error' => 'Error al agregar el programa.']);
        }
    }
    exit;
}


// ===============================
// ACTUALIZAR
// ===============================
if ($accion === 'actualizar') {
    $json = json_decode(file_get_contents('php://input'), true);
    $id_programa     = limpiar($json['id_programa'] ?? '');
    $nombre_programa = limpiar($json['nombre_programa'] ?? '');
    $descripcion     = limpiar($json['descripcion'] ?? '');
    $duracion        = limpiar($json['duracion'] ?? '');

    if (!$id_programa || !$nombre_programa) {
        echo json_encode(['error' => 'Datos insuficientes para actualizar.']);
        exit;
    }

    try {
        $sql = "UPDATE programas 
                SET nombre_programa = ?, descripcion = ?, duracion = ?
                WHERE id_programa = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$nombre_programa, $descripcion, $duracion, $id_programa]);
        echo json_encode(['success' => 'Programa actualizado correctamente.']);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error al actualizar: ' . $e->getMessage()]);
    }
    exit;
}

// ===============================
// ELIMINAR
// ===============================
if ($accion === 'eliminar') {
    $id_programa = $_POST['id_programa'] ?? '';

    if (!$id_programa) {
        echo json_encode(['error' => 'ID de programa faltante.']);
        exit;
    }

    try {
        $sql = "DELETE FROM programas WHERE id_programa = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id_programa]);
        echo json_encode(['success' => 'Programa eliminado correctamente.']);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error al eliminar: ' . $e->getMessage()]);
    }
    exit;
}

// ===============================
// ACTIVAR
// ===============================
if ($accion === 'activar') {
    $id_programa = $_POST['id_programa'] ?? '';

    if (!$id_programa) {
        echo json_encode(['error' => 'ID de programa faltante.']);
        exit;
    }

    try {
        $sql = "UPDATE programas SET estado = 1 WHERE id_programa = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id_programa]);
        echo json_encode(['success' => 'Programa activado correctamente.']);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error al activar: ' . $e->getMessage()]);
    }
    exit;
}

// ===============================
// INHABILITAR
// ===============================
if ($accion === 'inhabilitar') {
    $id_programa = $_POST['id_programa'] ?? '';

    if (!$id_programa) {
        echo json_encode(['error' => 'ID de programa faltante.']);
        exit;
    }

    try {
        $sql = "UPDATE programas SET estado = 0 WHERE id_programa = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id_programa]);
        echo json_encode(['success' => 'Programa inhabilitado correctamente.']);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error al inhabilitar: ' . $e->getMessage()]);
    }
    exit;
}

// ===============================
// ACCIÓN DESCONOCIDA
// ===============================
echo json_encode(['error' => 'Acción no válida.']);
exit;
