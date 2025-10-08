<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once __DIR__ . '/../../config/database.php';
include_once '../models/Zona.php';

$zona = new Zona($conn);
$accion = isset($_GET['accion']) ? $_GET['accion'] : null;

if (!$accion) {
    echo json_encode(['error' => 'Debe especificar la acción en la URL, por ejemplo: ?accion=listar']);
    exit;
}

switch ($accion) {
    case 'listar':
        $res = $zona->listar();
        echo json_encode($res);
        break;

    case 'obtener':
        if (!isset($_GET['id_zona'])) {
            echo json_encode(['error' => 'Debe enviar el parámetro id_zona']);
            exit;
        }
        $res = $zona->obtenerPorId($_GET['id_zona']);
        echo json_encode($res);
        break;

    case 'crear':
        // En la tabla actual no hay más campos, así que solo se crea una fila vacía (id auto-incremental)
        $res = $zona->crear();
        echo json_encode($res);
        break;

    case 'eliminar':
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['id_zona'])) {
            echo json_encode(['error' => 'Debe enviar el parámetro id_zona']);
            exit;
        }

        $res = $zona->eliminar($data['id_zona']);
        echo json_encode($res);
        break;

    default:
        echo json_encode(['error' => 'Acción no válida']);
        break;
}
?>
