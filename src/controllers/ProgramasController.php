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

/**
 * Duración en horas: entero >= 1. Devuelve null si es inválida o <= 0.
 */
function parse_duracion_horas($raw) {
    $s = trim((string)($raw ?? ''));
    if ($s === '' || !ctype_digit($s)) {
        return null;
    }
    $n = (int) $s;
    return $n >= 1 ? $n : null;
}

// ===============================
// LISTAR
// ===============================
if ($accion === 'listar') {
    try {
        $soloActivos = isset($_GET['solo_activos'])
            && ($_GET['solo_activos'] === '1' || $_GET['solo_activos'] === 'true');
        $idIncluir = trim((string) ($_GET['id_programa_incluir'] ?? ''));

        $sql = "SELECT id_programa, nombre_programa, descripcion, duracion, estado, nivel_formacion
        FROM programas ";
        $params = [];
        if ($soloActivos) {
            if ($idIncluir !== '') {
                $sql .= "WHERE (COALESCE(estado, 1) = 1 OR id_programa = ?) ";
                $params[] = $idIncluir;
            } else {
                $sql .= "WHERE COALESCE(estado, 1) = 1 ";
            }
        }
        $sql .= "ORDER BY id_programa DESC";

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($data ?: []);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]); // Enviar mensaje de error
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
    $nivel_formacion = limpiar($json['nivel_formacion'] ?? '');
    $duracionNum     = parse_duracion_horas($json['duracion'] ?? '');

    if (!$id_programa || !$nombre_programa || !$nivel_formacion) {
        echo json_encode(['error' => 'Campos obligatorios faltantes.']);
        exit;
    }
    if ($duracionNum === null) {
        echo json_encode(['error' => 'La duración es obligatoria y debe ser un número entero de horas mayor a 0.']);
        exit;
    }
    $duracion = (string) $duracionNum;

    try {
        $sql = "INSERT INTO programas 
                (id_programa, nombre_programa, descripcion, duracion, nivel_formacion, estado) 
                VALUES (?, ?, ?, ?, ?, 1)";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $id_programa,
            $nombre_programa,
            $descripcion,
            $duracion,
            $nivel_formacion
        ]);

        echo json_encode(['success' => 'Programa agregado correctamente.']);

    } catch (PDOException $e) {
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

    $id_programa_actual = limpiar($json['id_programa'] ?? '');
    $nuevo_id_programa  = limpiar($json['nuevo_id_programa'] ?? $id_programa_actual);

    $nombre_programa = limpiar($json['nombre_programa'] ?? '');
    $descripcion     = limpiar($json['descripcion'] ?? '');
    $nivel_formacion = limpiar($json['nivel_formacion'] ?? '');
    $duracionNum     = parse_duracion_horas($json['duracion'] ?? '');

    if (!$id_programa_actual || !$nombre_programa || !$nivel_formacion) {
        echo json_encode(['error' => 'Datos insuficientes para actualizar.']);
        exit;
    }
    if ($duracionNum === null) {
        echo json_encode(['error' => 'La duración es obligatoria y debe ser un número entero de horas mayor a 0.']);
        exit;
    }
    $duracion = (string) $duracionNum;

    try {
        $conn->beginTransaction();

        if ($nuevo_id_programa !== $id_programa_actual) {
            $chk = $conn->prepare("SELECT 1 FROM programas WHERE id_programa = ?");
            $chk->execute([$nuevo_id_programa]);
            if ($chk->fetchColumn()) {
                $conn->rollBack();
                echo json_encode(['error' => 'Ya existe un programa con el nuevo código.']);
                exit;
            }
        }

        $sql = "UPDATE programas 
                   SET id_programa = ?, 
                       nombre_programa = ?, 
                       descripcion = ?, 
                       duracion = ?,
                       nivel_formacion = ?
                 WHERE id_programa = ?";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $nuevo_id_programa,
            $nombre_programa,
            $descripcion,
            $duracion,
            $nivel_formacion,
            $id_programa_actual
        ]);

        $conn->commit();

        echo json_encode([
            'success' => 'Programa actualizado correctamente.',
            'id_programa' => $nuevo_id_programa
        ]);

    } catch (PDOException $e) {
        if ($conn->inTransaction()) $conn->rollBack();

        if (strpos($e->getMessage(), '1062') !== false) {
            echo json_encode(['error' => 'Ya existe un programa con ese código.']);
        } else {
            echo json_encode(['error' => 'Error al actualizar: ' . $e->getMessage()]);
        }
    }
    exit;
}


// ===============================
// ELIMINAR
// ===============================
if ($accion === 'eliminar') { // eliminar programa
    $id_programa = $_POST['id_programa'] ?? ''; // obtener id_programa
    // Validar si se proporcionó ID
    if (!$id_programa) {
        echo json_encode(['error' => 'ID de programa faltante.']);
        exit;
    }
    // Intentar eliminar
    try {
        $sql = "DELETE FROM programas WHERE id_programa = ?";
        $stmt = $conn->prepare($sql); 
        $stmt->execute([$id_programa]); // Ejecutar eliminación
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
    // Validar si se proporcionó ID
    if (!$id_programa) {
        echo json_encode(['error' => 'ID de programa faltante.']);
        exit;
    }
    // Intentar activar
    try {
        $sql = "UPDATE programas SET estado = 1 WHERE id_programa = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id_programa]); // Ejecutar activación
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
    // Validar si se proporcionó ID
    if (!$id_programa) {
        echo json_encode(['error' => 'ID de programa faltante.']);
        exit;
    }
    // Intentar inhabilitar
    try {
        $sql = "UPDATE programas SET estado = 0 WHERE id_programa = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id_programa]);
        echo json_encode(['success' => 'Programa inhabilitado correctamente.']);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error al inhabilitar: ' . $e->getMessage()]);
    }  
    exit; // fin inhabilitar
}

// ===============================
// LISTAR INSTRUCTORES
// ===============================
// ===============================
// LISTAR INSTRUCTORES
// ===============================
if ($accion === 'listar_instructores') {
    try {
        $sql = "SELECT id_usuario, nombre_completo 
                FROM usuarios 
                WHERE cargo = 'INSTRUCTOR' 
                AND estado = 1
                AND COALESCE(es_sistema, 0) = 0
                ORDER BY nombre_completo ASC";

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
// ACCIÓN DESCONOCIDA
// ===============================
echo json_encode(['error' => 'Acción no válida.']);
exit;
