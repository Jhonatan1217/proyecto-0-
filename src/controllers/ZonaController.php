<?php
// ===============================
// CONFIGURACIÓN INICIAL
// ===============================
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

include_once __DIR__ . '/../../config/database.php';
include_once __DIR__ . '/../models/Zona.php';

// Verificar conexión
if (!isset($conn)) {
    echo json_encode(['status' => 'error', 'message' => 'No se pudo establecer conexión con la base de datos']);
    exit;
}

// Instancia del modelo
$zona = new Zona($conn);
$response = ["status" => "error", "message" => "Acción no válida"];

// ===============================
// OBTENER ACCIÓN Y DATOS
// ===============================
$accion = $_POST["accion"] ?? $_GET["accion"] ?? null;

// Permitir recibir JSON (php://input)
$inputJSON = file_get_contents("php://input");
if ($inputJSON) {
    $data = json_decode($inputJSON, true);
    if (is_array($data)) {
        $_POST = array_merge($_POST, $data);
    }
}

if ($accion) {
    switch ($accion) {

        // ===============================
        // CREAR ZONA
        // ===============================
        case "crear":
            $id_zona = $_POST["id_zona"] ?? null;
            $id_area = $_POST["id_area"] ?? null;

            if (!$id_zona || !$id_area) {
                $response = ["status" => "error", "message" => "Debe enviar id_zona y id_area."];
                break;
            }

            // Verificar si ya existe la zona con ese número
            $existe = $zona->obtenerPorId($id_zona);
            if ($existe) {
                $response = ["status" => "error", "message" => "Ya existe una zona con ese número."];
                break;
            }

            $resultado = $zona->crear($id_zona, $id_area);

            if ($resultado) {
                $response = ["status" => "success", "message" => "Zona creada correctamente."];
            } else {
                $response = ["status" => "error", "message" => "Error al crear la zona."];
            }
            break;

        // ===============================
        // ACTUALIZAR ZONA (número + área)
        // ===============================
        case "actualizar":
            $id_zona_actual = $_POST["id_zona_actual"] ?? null;
            $id_zona_nueva = $_POST["id_zona_nueva"] ?? null;
            $id_area = $_POST["id_area"] ?? null;

            if (!$id_zona_actual || !$id_zona_nueva || !$id_area) {
                $response = ["status" => "error", "message" => "Debe enviar id_zona_actual, id_zona_nueva e id_area."];
                break;
            }

            // Si el número nuevo es diferente, validar que no exista ya
            if ($id_zona_actual != $id_zona_nueva) {
                $existeNueva = $zona->obtenerPorId($id_zona_nueva);
                if ($existeNueva) {
                    $response = ["status" => "error", "message" => "Ya existe otra zona con el número ingresado."];
                    break;
                }
            }

            $resultado = $zona->actualizar($id_zona_actual, $id_zona_nueva, $id_area);

            if ($resultado) {
                $response = ["status" => "success", "message" => "Zona actualizada correctamente."];
            } else {
                $response = ["status" => "error", "message" => "Error al actualizar la zona."];
            }
            break;

        // ===============================
        // CAMBIAR ESTADO (activar/desactivar)
        // ===============================
        case "cambiarEstado":
            $id_zona = $_POST["id_zona"] ?? null;
            $estado = $_POST["estado"] ?? null;

            if ($id_zona === null || $estado === null) {
                $response = ["status" => "error", "message" => "Debe enviar id_zona y estado (1 o 0)."];
                break;
            }

            if (!in_array($estado, [0, 1, "0", "1"], true)) {
                $response = ["status" => "error", "message" => "El estado debe ser 1 (activo) o 0 (inactivo)."];
                break;
            }

            $resultado = $zona->cambiarEstado($id_zona, $estado);

            if ($resultado) {
                $response = ["status" => "success", "message" => "Estado de la zona actualizado correctamente."];
            } else {
                $response = ["status" => "error", "message" => "Error al cambiar el estado de la zona."];
            }
            break;

        // ===============================
        // LISTAR ZONAS
        // ===============================
        case "listar":
            $stmt = $zona->listar();
            $data = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
            $response = ["status" => "success", "data" => $data];
            break;

        // ===============================
        // OBTENER ZONA POR ID
        // ===============================
        case "obtener":
            $id_zona = $_POST["id_zona"] ?? $_GET["id_zona"] ?? null;
            if (!$id_zona) {
                $response = ["status" => "error", "message" => "Debe enviar id_zona."];
                break;
            }

            $zonaData = $zona->obtenerPorId($id_zona);

            if ($zonaData) {
                $response = ["status" => "success", "data" => $zonaData];
            } else {
                $response = ["status" => "error", "message" => "Zona no encontrada."];
            }
            break;
    }
}

// ===============================
// RESPUESTA FINAL
// ===============================
echo json_encode($response);
?>
