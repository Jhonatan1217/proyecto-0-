<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ✅ tu conexión actual devuelve $conn, no una clase
include_once __DIR__ . '/../../config/database.php';
include_once __DIR__ . '/../models/Trimestre.php';

// Usa directamente la variable $conn
$trimestre = new Trimestre($conn);

// Detectar el método HTTP
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    // 🔹 LISTAR todos o uno por número
    case 'GET':
        if (isset($_GET['numero_trimestre'])) {
            $stmt = $trimestre->obtenerPorId($_GET['numero_trimestre']);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($data ?: ['mensaje' => 'No encontrado']);
        } else {
            $stmt = $trimestre->listar();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($data);
        }
        break;

    // 🔹 CREAR nuevo trimestre
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        if (!empty($input['numero_trimestre'])) {
            $ok = $trimestre->crear($input['numero_trimestre'], $input['estado'] ?? 1);
            echo json_encode(['mensaje' => $ok ? 'Trimestre creado correctamente' : 'Error al crear']);
        } else {
            echo json_encode(['error' => 'Faltan datos obligatorios']);
        }
        break;

    // 🔹 ACTUALIZAR trimestre
    case 'PUT':
        $input = json_decode(file_get_contents('php://input'), true);
        if (!empty($input['numero_trimestre'])) {
            $ok = $trimestre->actualizar($input['numero_trimestre'], $input['estado']);
            echo json_encode(['mensaje' => $ok ? 'Trimestre actualizado correctamente' : 'Error al actualizar']);
        } else {
            echo json_encode(['error' => 'Datos incompletos']);
        }
        break;

    // 🔹 ELIMINAR trimestre
    case 'DELETE':
        parse_str(file_get_contents("php://input"), $_DELETE);
        if (!empty($_DELETE['numero_trimestre'])) {
            $ok = $trimestre->eliminar($_DELETE['numero_trimestre']);
            echo json_encode(['mensaje' => $ok ? 'Trimestre eliminado correctamente' : 'Error al eliminar']);
        } else {
            echo json_encode(['error' => 'Falta el número de trimestre']);
        }
        break;

    default:
        echo json_encode(['error' => 'Método no permitido']);
        break;
}
?>
