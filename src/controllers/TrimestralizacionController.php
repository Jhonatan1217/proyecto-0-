<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

include_once __DIR__ . '/../../config/database.php';
include_once __DIR__ . '/../models/Trimestralizacion.php';

// Verificar conexión
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
        $res = $trimestral->listar();
        echo json_encode($res);
        break;

    case 'obtener':
        if (!isset($_GET['id_trimestral'])) {
            echo json_encode(['error' => 'Debe enviar el parámetro id_trimestral']);
            exit;
        }
        $res = $trimestral->obtenerPorId($_GET['id_trimestral']);
        echo json_encode($res);
        break;

    case 'crear':
        // Leer JSON o POST tradicional
        $data = json_decode(file_get_contents("php://input"), true);
        $id_horario = $data['id_horario'] ?? $_POST['id_horario'] ?? null;

        if (!$id_horario) {
            echo json_encode(['error' => 'Debe enviar el campo id_horario']);
            exit;
        }

        $res = $trimestral->crear($id_horario);
        echo json_encode($res);
        break;

    case 'eliminar':
        $data = json_decode(file_get_contents("php://input"), true);
        $id_trimestral = $data['id_trimestral'] ?? $_POST['id_trimestral'] ?? null;

        if (!$id_trimestral) {
            echo json_encode(['error' => 'Debe enviar el parámetro id_trimestral']);
            exit;
        }

        $res = $trimestral->eliminar($id_trimestral);
        echo json_encode($res);
        break;

    default:
        echo json_encode(['error' => 'Acción no válida']);
        break;
}
?>
