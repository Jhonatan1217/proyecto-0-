<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

include_once __DIR__ . '/../../config/database.php';
include_once __DIR__ . '/../models/Instructor.php';

// Verificar conexión
if (!isset($conn)) {
    echo json_encode(['error' => 'No se pudo establecer conexión con la base de datos']);
    exit;
}

$instructor = new Instructor($conn);
$accion = isset($_GET['accion']) ? $_GET['accion'] : null;

if (!$accion) {
    echo json_encode(['error' => 'Debe especificar la acción en la URL, por ejemplo: ?accion=listar']);
    exit;
}

switch ($accion) {

    case 'listar':
        $res = $instructor->listar();
        echo json_encode($res);
        break;

    case 'obtener':
        if (!isset($_GET['id_instructor'])) {
            echo json_encode(['error' => 'Debe enviar el parámetro id_instructor']);
            exit;
        }
        $res = $instructor->obtenerPorId($_GET['id_instructor']);
        echo json_encode($res);
        break;

    case 'crear':
        $data = json_decode(file_get_contents("php://input"), true);

        $nombre = $data['nombre_instructor'] ?? $_POST['nombre_instructor'] ?? null;
        $apellido = $data['apellido_instructor'] ?? $_POST['apellido_instructor'] ?? null;
        $tipo = $data['tipo_instructor'] ?? $_POST['tipo_instructor'] ?? null;

        // Validaciones
        if (!$nombre || !$apellido || !$tipo) {
            echo json_encode(['error' => 'Debe enviar nombre_instructor, apellido_instructor y tipo_instructor']);
            exit;
        }

        $tiposValidos = ['TRANSVERSAL', 'TECNICO'];
        if (!in_array(strtoupper($tipo), $tiposValidos)) {
            echo json_encode(['error' => 'El tipo_instructor debe ser TRANSVERSAL o TECNICO']);
            exit;
        }

        $res = $instructor->crear($nombre, $apellido, strtoupper($tipo));
        echo json_encode($res);
        break;

    case 'actualizar':
        $data = json_decode(file_get_contents("php://input"), true);

        $id_instructor = $data['id_instructor'] ?? $_POST['id_instructor'] ?? null;
        $nombre = $data['nombre_instructor'] ?? $_POST['nombre_instructor'] ?? null;
        $apellido = $data['apellido_instructor'] ?? $_POST['apellido_instructor'] ?? null;
        $tipo = $data['tipo_instructor'] ?? $_POST['tipo_instructor'] ?? null;

        // Validaciones
        if (!$id_instructor || !$nombre || !$apellido || !$tipo) {
            echo json_encode(['error' => 'Debe enviar id_instructor, nombre_instructor, apellido_instructor y tipo_instructor']);
            exit;
        }

        $tiposValidos = ['TRANSVERSAL', 'TECNICO'];
        if (!in_array(strtoupper($tipo), $tiposValidos)) {
            echo json_encode(['error' => 'El tipo_instructor debe ser TRANSVERSAL o TECNICO']);
            exit;
        }

        $res = $instructor->actualizar($id_instructor, $nombre, $apellido, strtoupper($tipo));
        echo json_encode($res);
        break;

    case 'eliminar':
        $data = json_decode(file_get_contents("php://input"), true);
        $id_instructor = $data['id_instructor'] ?? $_POST['id_instructor'] ?? null;

        if (!$id_instructor) {
            echo json_encode(['error' => 'Debe enviar el parámetro id_instructor']);
            exit;
        }

        $res = $instructor->eliminar($id_instructor);
        echo json_encode($res);
        break;

    default:
        echo json_encode(['error' => 'Acción no válida']);
        break;
}
?>
