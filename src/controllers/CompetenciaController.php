<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

include_once __DIR__ . '/../../config/database.php';
include_once __DIR__ . '/../models/Competencia.php';

if (!isset($conn)) {
    echo json_encode(['error' => 'No se pudo establecer conexión con la base de datos']);
    exit;
}

$competencia = new Competencia($conn);
$accion = isset($_GET['accion']) ? $_GET['accion'] : null;

if (!$accion) {
    echo json_encode(['error' => 'Debe especificar la acción en la URL, por ejemplo: ?accion=listar']);
    exit;
}

switch ($accion) {

    case 'listar':
        $res = $competencia->listar();
        echo json_encode($res);
        break;

    case 'obtener':
        if (!isset($_GET['id_competencia'])) {
            echo json_encode(['error' => 'Debe enviar el parámetro id_competencia']);
            exit;
        }

        $res = $competencia->obtenerPorId($_GET['id_competencia']);
        echo json_encode($res);
        break;

    case 'crear':
        $data = json_decode(file_get_contents("php://input"), true);
        $descripcion = $data['descripcion'] ?? $_POST['descripcion'] ?? null;

        if (!$descripcion || trim($descripcion) === '') {
            echo json_encode(['error' => 'Debe enviar una descripción válida']);
            exit;
        }

        $res = $competencia->crear(trim($descripcion));
        echo json_encode($res);
        break;

    case 'actualizar':
        $data = json_decode(file_get_contents("php://input"), true);
        $id_competencia = $data['id_competencia'] ?? $_POST['id_competencia'] ?? null;
        $descripcion = $data['descripcion'] ?? $_POST['descripcion'] ?? null;

        if (!$id_competencia || !$descripcion || trim($descripcion) === '') {
            echo json_encode(['error' => 'Debe enviar id_competencia y una descripción válida']);
            exit;
        }

        $res = $competencia->actualizar($id_competencia, trim($descripcion));
        echo json_encode($res);
        break;

    case 'eliminar':
        $data = json_decode(file_get_contents("php://input"), true);
        $id_competencia = $data['id_competencia'] ?? $_POST['id_competencia'] ?? null;

        if (!$id_competencia) {
            echo json_encode(['error' => 'Debe enviar el parámetro id_competencia']);
            exit;
        }

        $res = $competencia->eliminar($id_competencia);
        echo json_encode($res);
        break;

    default:
        echo json_encode(['error' => 'Acción no válida']);
        break;
}
?>
