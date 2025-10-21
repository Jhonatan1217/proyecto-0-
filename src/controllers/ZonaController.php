<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

include_once __DIR__ . '/../../config/database.php';
include_once __DIR__ . '/../models/Zona.php';

// Verifica la conexión con la base de datos
if (!isset($conn)) {
    echo json_encode(['status' => 'error', 'message' => 'No se pudo establecer conexión con la base de datos']);
    exit;
}

$zona = new Zona($conn);

// Acción desde GET o POST
$accion = $_POST['accion'] ?? $_GET['accion'] ?? null;
$response = ["status" => "error", "message" => "Acción no válida"];

if (!$accion) {
    echo json_encode(['status' => 'error', 'message' => 'Debe especificar la acción (?accion=...)']);
    exit;
}

switch ($accion) {

    // ===============================
    // LISTAR TODAS LAS ZONAS
    // ===============================
    case 'listar':
        $data = $zona->listar();
        $response = ["status" => "success", "data" => $data];
        break;

    // ===============================
    // OBTENER UNA ZONA ESPECÍFICA
    // ===============================
    case 'obtener':
        $id_zona = $_POST['id_zona'] ?? $_GET['id_zona'] ?? null;
        $id_area = $_POST['id_area'] ?? $_GET['id_area'] ?? null;

        if (!$id_zona || !$id_area) {
            $response = ["status" => "error", "message" => "Debe enviar id_zona e id_area"];
            break;
        }

        $data = $zona->obtenerPorId($id_zona, $id_area);
        $response = $data
            ? ["status" => "success", "data" => $data]
            : ["status" => "error", "message" => "Zona no encontrada"];
        break;

    // ===============================
    // CREAR ZONA
    // ===============================
    case 'crear':
        $id_zona = $_POST['id_zona'] ?? null;
        $id_area = $_POST['id_area'] ?? null;

        if (!$id_zona || !$id_area) {
            $response = ["status" => "error", "message" => "Debe enviar id_zona e id_area"];
            break;
        }

        $response = $zona->crear($id_zona, $id_area);
        break;

    // ===============================
    // ACTUALIZAR ZONA
    // ===============================
    case 'actualizar':
        $id_zona_actual = $_POST['id_zona_actual'] ?? null;
        $id_area_actual = $_POST['id_area_actual'] ?? null;
        $id_zona_nueva = $_POST['id_zona_nueva'] ?? null;
        $id_area_nueva = $_POST['id_area_nueva'] ?? null;

        if (!$id_zona_actual || !$id_area_actual || !$id_zona_nueva || !$id_area_nueva) {
            $response = ["status" => "error", "message" => "Debe enviar id_zona_actual, id_area_actual, id_zona_nueva e id_area_nueva"];
            break;
        }

        $response = $zona->actualizar($id_zona_actual, $id_area_actual, $id_zona_nueva, $id_area_nueva);
        break;

    // ===============================
    // CAMBIAR ESTADO
    // ===============================
    case 'cambiar_estado':
        $id_zona = $_POST['id_zona'] ?? $_GET['id_zona'] ?? null;
        $id_area = $_POST['id_area'] ?? $_GET['id_area'] ?? null;
        $estado = $_POST['estado'] ?? $_GET['estado'] ?? null;

        if (!$id_zona || !$id_area || $estado === null) {
            $response = ["status" => "error", "message" => "Debe enviar id_zona, id_area y estado (1 o 0)"];
            break;
        }

        $response = $zona->cambiarEstado($id_zona, $id_area, $estado);
        break;
    

    case "listarPorArea":
        $id_area = $_GET["id_area"] ?? $_POST["id_area"] ?? null;
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

// Devuelve JSON
echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
