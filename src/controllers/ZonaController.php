<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once __DIR__ . '/../../config/database.php';
include_once __DIR__ . '/../models/Zona.php';

$zona = new Zona($conn);
$response = ["status" => "error", "message" => "Acción no válida"];

// 🔹 Leer cuerpo JSON si existe
$inputJSON = file_get_contents("php://input");
if ($inputJSON) {
    $data = json_decode($inputJSON, true);
    if (is_array($data)) {
        $_POST = array_merge($_POST, $data);
    }
}

// 🔹 Obtener acción (fusionada)
$accion = $_POST["accion"] ?? $_GET["accion"] ?? null;

try {
    if ($accion) {
        switch ($accion) {

            // 🔹 Crear zona
            case "crear":
                $id_zona = $_POST["id_zona"] ?? null;
                $id_area = $_POST["id_area"] ?? null;

                if (!$id_zona || !$id_area) {
                    $response = ["status" => "error", "message" => "Debe enviar id_zona y id_area"];
                    break;
                }

                // ✅ Verificar que el área exista
                $stmt = $conn->prepare("SELECT COUNT(*) FROM areas WHERE id_area = ?");
                $stmt->execute([$id_area]);
                if ($stmt->fetchColumn() == 0) {
                    $response = ["status" => "error", "message" => "El área seleccionada no existe"];
                    break;
                }

                // ✅ Verificar si la zona ya existe
                $existe = $zona->obtenerPorId($id_zona);
                if ($existe) {
                    $response = ["status" => "error", "message" => "Ya existe una zona con ese número"];
                    break;
                }

                // ✅ Crear zona
                $ok = $zona->crear($id_zona, $id_area);
                $response = $ok
                    ? ["status" => "success", "message" => "Zona creada correctamente"]
                    : ["status" => "error", "message" => "Error al crear la zona"];
                break;

            // 🔹 Actualizar zona
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

            // 🔹 Cambiar estado (activo/inactivo)
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
                    : ["status" => "error", "message" => "Error al cambiar el estado"];
                break;

            // 🔹 Listar todas las zonas
            case "listar":
                $data = $zona->listar();
                $response = [
                    "status" => "success",
                    "data" => $data,
                    "message" => "Zonas listadas correctamente"
                ];
                break;

            // 🔹 Listar zonas por área (⚡ compatibilidad con tu JS: listarPorArea)
            case "listarPorArea":
                $id_area = $_GET["id_area"] ?? $_POST["id_area"] ?? null;
                if (!$id_area) {
                    $response = ["status" => "error", "message" => "Debe enviar id_area"];
                    break;
                }

                $data = $zona->listarPorArea($id_area);
                $response = [
                    "status" => "success",
                    "data" => $data,
                    "message" => "Zonas filtradas correctamente"
                ];
                break;

            default:
                $response = ["status" => "error", "message" => "Acción no reconocida"];
                break;
        }
    }
} catch (Exception $e) {
    $response = [
        "status" => "error",
        "message" => "Error interno del servidor: " . $e->getMessage()
    ];
}

echo json_encode($response);
?>
