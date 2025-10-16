<?php
// Establece el tipo de contenido de la respuesta como JSON
header('Content-Type: application/json; charset=utf-8');

// Habilita la visualización de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Incluye la conexión y el modelo
include_once __DIR__ . '/../../config/database.php';
include_once __DIR__ . '/../models/Area.php';

// Verifica conexión
if (!isset($conn)) {
    echo json_encode(['error' => 'No se pudo establecer conexión con la base de datos']);
    exit;
}

// Instancia del modelo
$area = new Area($conn);

// Acción solicitada (?accion=)
$accion = isset($_GET['accion']) ? $_GET['accion'] : null;

// Verificación
if (!$accion) {
    echo json_encode(['error' => 'Debe especificar la acción en la URL, por ejemplo: ?accion=listar']);
    exit;
}

switch ($accion) {

    // 🔹 Listar áreas
    case 'listar':
        $res = $area->listar();
        echo json_encode($res);
        break;

    // 🔹 Obtener área por ID
    case 'obtener':
        if (!isset($_GET['id_area'])) {
            echo json_encode(['error' => 'Debe enviar el parámetro id_area']);
            exit;
        }
        $res = $area->obtenerPorId($_GET['id_area']);
        echo json_encode($res);
        break;

    // 🔹 Crear nueva área
    case 'crear':
        $data = json_decode(file_get_contents("php://input"), true);
        $nombre_area = $data['nombre_area'] ?? $_POST['nombre_area'] ?? null;

        if (!$nombre_area) {
            echo json_encode(['error' => 'Debe enviar nombre_area']);
            exit;
        }

        $area->crear($nombre_area);
        echo json_encode(['mensaje' => 'Área creada correctamente']);
        break;

    // 🔹 Actualizar área
    case 'actualizar':
        $data = json_decode(file_get_contents("php://input"), true);
        $id_area = $data['id_area'] ?? $_POST['id_area'] ?? null;
        $nombre_area = $data['nombre_area'] ?? $_POST['nombre_area'] ?? null;

        if (!$id_area || !$nombre_area) {
            echo json_encode(['error' => 'Debe enviar id_area y nombre_area']);
            exit;
        }

        $area->actualizar($id_area, $nombre_area);
        echo json_encode(['mensaje' => 'Área actualizada correctamente']);
        break;

    // 🔹 Eliminar área
    case 'eliminar':
        $data = json_decode(file_get_contents("php://input"), true);
        $id_area = $data['id_area'] ?? $_POST['id_area'] ?? null;

        if (!$id_area) {
            echo json_encode(['error' => 'Debe enviar id_area']);
            exit;
        }

        $area->eliminar($id_area);
        echo json_encode(['mensaje' => 'Área eliminada correctamente']);
        break;

    // 🔹 Cambiar estado (activo/inactivo)
    case 'cambiar_estado':
        $data = json_decode(file_get_contents("php://input"), true);
        $id_area = $data['id_area'] ?? $_POST['id_area'] ?? $_GET['id_area'] ?? null;
        $estado = $data['estado'] ?? $_POST['estado'] ?? $_GET['estado'] ?? null;

        if ($id_area === null || $estado === null) {
            echo json_encode(['error' => 'Debe enviar id_area y estado (1 o 0)']);
            exit;
        }

        if ($estado != 1 && $estado != 0) {
            echo json_encode(['error' => 'El estado debe ser 1 (activo) o 0 (inactivo)']);
            exit;
        }

        $area->cambiarEstado($id_area, $estado);
        echo json_encode(['mensaje' => 'Estado del área actualizado correctamente']);
        break;

    // 🔹 Acción no válida
    default:
        echo json_encode(['error' => 'Acción no válida']);
        break;
}
?>
