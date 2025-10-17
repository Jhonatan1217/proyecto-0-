<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once __DIR__ . '/../../config/database.php';
include_once __DIR__ . '/../models/Zona.php';

$zona = new Zona($conn);
$response = ["status" => "error", "message" => "Acción no válida"];

// 🔹 PRIMERO: leer el cuerpo JSON si existe
$inputJSON = file_get_contents("php://input");
if ($inputJSON) {
    $data = json_decode($inputJSON, true);
    if (is_array($data)) {
        $_POST = array_merge($_POST, $data);
    }
}

// 🔹 DESPUÉS: obtener la acción (ya fusionado)
$accion = $_POST["accion"] ?? $_GET["accion"] ?? null;

if ($accion) {
    switch ($accion) {

        case "crear":
    $id_zona = $_POST["id_zona"] ?? null;
    $id_area = $_POST["id_area"] ?? null;

    if (!$id_zona || !$id_area) {
        $response = ["status" => "error", "message" => "Debe enviar id_zona y id_area"];
        break;
    }

    // ✅ Verificar que el área exista en la tabla "areas"
    $stmt = $conn->prepare("SELECT COUNT(*) FROM areas WHERE id_area = ?");
    $stmt->execute([$id_area]);
    if ($stmt->fetchColumn() == 0) {
        $response = ["status" => "error", "message" => "El área seleccionada no existe"];
        break;
    }

    // ✅ Verificar si ya existe la zona
    $existe = $zona->obtenerPorId($id_zona);
    if ($existe) {
        $response = ["status" => "error", "message" => "Ya existe una zona con ese número"];
        break;
    }

    // ✅ Crear zona
    $ok = $zona->crear($id_zona, $id_area);
    $response = $ok
        ? ["status" => "success", "message" => "Zona creada correctamente"]
        : ["status" => "error", "message" => "Error al crear zona"];
    break;


        case "actualizar":
            $id_zona_actual = $_POST["id_zona_actual"] ?? null;
            $id_zona_nueva = $_POST["id_zona_nueva"] ?? null;
            $id_area = $_POST["id_area"] ?? null;

            if (!$id_zona_actual || !$id_zona_nueva || !$id_area) {
                $response = ["status" => "error", "message" => "Debe enviar id_zona_actual, id_zona_nueva e id_area"];
                break;
            }

            $ok = $zona->actualizar($id_zona_actual, $id_zona_nueva, $id_area);
            $response = $ok
                ? ["status" => "success", "message" => "Zona actualizada correctamente"]
                : ["status" => "error", "message" => "Error al actualizar la zona"];
            break;

        case "cambiarEstado":
            $id_zona = $_POST["id_zona"] ?? null;
            $estado = $_POST["estado"] ?? null;

            if ($id_zona === null || $estado === null) {
                $response = ["status" => "error", "message" => "Debe enviar id_zona y estado"];
                break;
            }

            $ok = $zona->cambiarEstado($id_zona, $estado);
            $response = $ok
                ? ["status" => "success", "message" => "Estado actualizado correctamente"]
                : ["status" => "error", "message" => "Error al cambiar estado"];
            break;

        case "listar":
            $data = $zona->listar();
            $response = ["status" => "success", "data" => $data];
            break;


        default:
            $response = ["status" => "error", "message" => "Acción no reconocida"];
            break;
    }
}

echo json_encode($response);
