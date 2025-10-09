<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

include_once __DIR__ . '/../../config/database.php';
include_once __DIR__ . '/../models/Trimestralizacion.php';

if (!isset($conn)) {
    echo json_encode(['error' => 'No se pudo establecer conexión con la base de datos']);
    exit;
}

$trimestral = new Trimestralizacion($conn);
$accion = isset($_GET['accion']) ? $_GET['accion'] : null;

if (!$accion) {
    echo json_encode(['error' => 'Debe especificar la acción en la URL, por ejemplo: ?accion=listar']);
    exit;
}

switch ($accion) {
    case 'listar':
        echo json_encode($trimestral->listar());
        break;

    case 'obtener':
        $id = $_GET['id'] ?? null;
        echo json_encode($trimestral->obtenerPorId($id));
        break;

    case 'crear':
        $data = json_decode(file_get_contents("php://input"), true);
        if (!$data) {
            echo json_encode(['error' => 'No se recibieron datos para crear la trimestralización']);
            exit;
        }

        $res = $trimestral->crear($data);
        echo json_encode($res);
        break;

    case 'actualizar':
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $_GET['id'] ?? null;
        $res = $trimestral->actualizar($id, $data);
        echo json_encode($res);
        break;

    case 'eliminar':
        $id = $_GET['id'] ?? null;
        $res = $trimestral->eliminar($id);
        echo json_encode($res);
        break;

    default:
        echo json_encode(['error' => 'Acción no reconocida']);
        break;
}
