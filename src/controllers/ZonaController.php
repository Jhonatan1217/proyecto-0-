<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

include_once __DIR__ . '/../../config/database.php';
include_once __DIR__ . '/../models/Zona.php';

if (!isset($conn)) {
    echo json_encode(['status' => 'error', 'message' => 'No se pudo establecer conexión con la base de datos']);
    exit;
}

$zona = new Zona($conn);

$accion = $_POST['accion'] ?? $_GET['accion'] ?? null;
$response = ["status" => "error", "message" => "Acción no válida"];

if (!$accion) {
    echo json_encode(['status' => 'error', 'message' => 'Debe especificar la acción (?accion=...)']);
    exit;
}

switch ($accion) {

    case 'listar':
        $data = $zona->listar();
        $response = ["status" => "success", "data" => $data];
        break;

    case 'obtener':
        $id_zona = $_POST['id_zona'] ?? $_GET['id_zona'] ?? null;
        if (!$id_zona) {
            $response = ["status" => "error", "message" => "Debe enviar id_zona"];
            break;
        }
        $data = $zona->obtenerPorId($id_zona);
        $response = $data
            ? ["status" => "success", "data" => $data]
            : ["status" => "error", "message" => "Zona no encontrada"];
        break;

    case 'crear':
        $nombre_zona = trim($_POST['nombre_zona'] ?? '');
        $id_area     = $_POST['id_area'] ?? null;
        if ($nombre_zona === '' || ($id_area === null || $id_area === '')) {
            $response = ["status" => "error", "message" => "Debe enviar nombre_zona e id_area"];
            break;
        }
        $response = $zona->crear($nombre_zona, $id_area);
        break;

    case 'actualizar':
        $id_zona          = $_POST['id_zona'] ?? null;
        $nombre_zona_nueva = trim($_POST['nombre_zona_nueva'] ?? '');
        $id_area_nueva    = $_POST['id_area_nueva'] ?? null;
        $missing = ($id_zona === null || $id_zona === '') || ($nombre_zona_nueva === '') || ($id_area_nueva === null || $id_area_nueva === '');
        if ($missing) {
            $response = ["status" => "error", "message" => "Debe enviar id_zona, nombre_zona_nueva e id_area_nueva"];
            break;
        }
        $response = $zona->actualizar($id_zona, $nombre_zona_nueva, $id_area_nueva);
        break;

    case 'cambiar_estado':
        $id_zona = $_POST['id_zona'] ?? $_GET['id_zona'] ?? null;
        $estado  = $_POST['estado']  ?? $_GET['estado']  ?? null;
        if (!$id_zona || $estado === null) {
            $response = ["status" => "error", "message" => "Debe enviar id_zona y estado (1 o 0)"];
            break;
        }
        $response = $zona->cambiarEstado($id_zona, $estado);
        break;

    case 'listarPorArea':
        $id_area = $_GET['id_area'] ?? $_POST['id_area'] ?? null;
        if (!$id_area) {
            $response = ["status" => "error", "message" => "Debe enviar id_area"];
            break;
        }
        $data = $zona->listarPorArea($id_area);
        $response = ["status" => "success", "data" => $data];
        break;

    default:
        $response = ["status" => "error", "message" => "Acción no válida"];
        break;
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
