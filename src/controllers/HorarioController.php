
<?php

// Habilita la visualización de todos los errores para facilitar la depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Incluye el archivo de configuración de la base de datos y el modelo de Horario
include_once __DIR__ . '/../../config/database.php';
include_once __DIR__ . '/../models/Horario.php';

// Verifica que la conexión a la base de datos se haya establecido correctamente
if (!isset($conn)) {
    echo json_encode(['error' => 'No se pudo establecer conexión con la base de datos']);
    exit;
}

// Instancia el modelo Horario pasando la conexión a la base de datos
$horario = new Horario($conn);

// Lee desde JSON body o POST/GET
$input = json_decode(file_get_contents('php://input'), true) ?? [];
$accion = $_POST["accion"] ?? $_GET["accion"] ?? $input["accion"] ?? null;

// Respuesta base
$response = ["status" => "error", "message" => "Acción no válida"];

if ($accion) {
    switch ($accion) {

        // ===============================
        // CREAR HORARIO
        // ===============================
        case "crear":
            // Parámetros necesarios
            $dia = $input["dia"] ?? $_POST["dia"] ?? null;
            $hora_inicio = $input["hora_inicio"] ?? $_POST["hora_inicio"] ?? null;
            $hora_fin = $input["hora_fin"] ?? $_POST["hora_fin"] ?? null;
            $id_zona = $input["id_zona"] ?? $_POST["id_zona"] ?? null;
            $id_area = $input["id_area"] ?? $_POST["id_area"] ?? null;
            $id_ficha = $input["id_ficha"] ?? $_POST["id_ficha"] ?? null;
            $id_instructor = $input["id_instructor"] ?? $_POST["id_instructor"] ?? null;
            $id_competencia = $input["id_competencia"] ?? $_POST["id_competencia"] ?? null;
            $id_rae = $input["id_rae"] ?? $_POST["id_rae"] ?? null;
            $numero_trimestre = $input["numero_trimestre"] ?? $_POST["numero_trimestre"] ?? null;
            // Llamar al método de creación
            $resultado = $horario->crearHorario($dia, $hora_inicio, $hora_fin, $id_zona, $id_area, $id_ficha, $id_instructor, $id_competencia, $id_rae, $numero_trimestre);
            // Responder según el resultado
            if ($resultado) {
                $response = ["status" => "success", "message" => "Horario creado correctamente."];
            } else {
                $response = ["status" => "error", "message" => "Error al crear el horario."];
            }
            break;

        // ===============================
        // ACTUALIZAR HORARIO
        // ===============================
        case "actualizar":
            // Parámetros necesarios
            $id_horario = $input["id_horario"] ?? $_POST["id_horario"] ?? null;
            $id_ficha = $input["id_ficha"] ?? $_POST["id_ficha"] ?? null;
            $numero_trimestre = $input["numero_trimestre"] ?? $_POST["numero_trimestre"] ?? null;
            $id_instructor = $input["id_instructor"] ?? $_POST["id_instructor"] ?? null;
            $id_competencia = $input["id_competencia"] ?? $_POST["id_competencia"] ?? null;
            $id_rae = $input["id_rae"] ?? $_POST["id_rae"] ?? null;
            // Llamar al método de actualización
            $resultado = $horario->actualizarHorario($id_horario, $id_ficha, $numero_trimestre, $id_instructor, $id_competencia, $id_rae);
            // Responder según el resultado
            if ($resultado) {
                $response = ["status" => "success", "message" => "Horario actualizado correctamente."];
            } else {
                $response = ["status" => "error", "message" => "Error al actualizar el horario."];
            }
            break;

        // ===============================
        // ACTUALIZAR HORARIO COMPLETO (con día y horas)
        // ===============================
        case "actualizarCompleto":
            // Parámetros desde JSON body o POST (enviados por aplicarCambiosHorario)
            $id_horario = $input["id_horario"] ?? $_POST["id_horario"] ?? null;
            $dia = $input["dia"] ?? $_POST["dia"] ?? null;
            $hora_inicio = $input["hora_inicio"] ?? $_POST["hora_inicio"] ?? null;
            $hora_fin = $input["hora_fin"] ?? $_POST["hora_fin"] ?? null;
            $id_ficha = $input["id_ficha"] ?? $_POST["id_ficha"] ?? null;
            $numero_trimestre = $input["numero_trimestre"] ?? $_POST["numero_trimestre"] ?? null;
            $id_instructor = $input["id_instructor"] ?? $_POST["id_instructor"] ?? null;
            $id_competencia = $input["id_competencia"] ?? $_POST["id_competencia"] ?? null;
            $id_rae = $input["id_rae"] ?? $_POST["id_rae"] ?? null;
            
            // Validar parámetro obligatorio
            if (!$id_horario) {
                $response = ["status" => "error", "message" => "id_horario es requerido"];
                break;
            }
            
            // Llamar al método de actualización completo
            $resultado = $horario->actualizarHorarioCompleto($id_horario, $dia, $hora_inicio, $hora_fin, $id_ficha, $numero_trimestre, $id_instructor, $id_competencia, $id_rae);
            
            // Responder según el resultado
            if ($resultado) {
                $response = ["status" => "success", "message" => "Horario actualizado correctamente (cambios aprobados)."];
            } else {
                $response = ["status" => "error", "message" => "Error al actualizar el horario."];
            }
            break;

        // ===============================
        // INHABILITAR HORARIOS POR ZONA
        // ===============================
        case "inhabilitarZona":
            $id_zona = $input["id_zona"] ?? $_POST["id_zona"] ?? null;
            // Llamar al método de inhabilitación
            $resultado = $horario->inhabilitarPorZona($id_zona);
            // Responder según el resultado
            if ($resultado) {
                $response = ["status" => "success", "message" => "Horario de la zona inhabilitado correctamente."];
            } else {
                $response = ["status" => "error", "message" => "Error al inhabilitar los horarios de la zona."];
            }
            break;

        // ===============================
        // ACTIVAR HORARIO
        // ===============================
        case "activar":
            $id_horario = $input["id_horario"] ?? $_POST["id_horario"] ?? null;
            // Llamar al método de activación
            $resultado = $horario->activarHorario($id_horario);
            // Responder según el resultado
            if ($resultado) {
                $response = ["status" => "success", "message" => "Horario activado correctamente."];
            } else {
                $response = ["status" => "error", "message" => "Error al activar el horario."];
            }
            break;

        // ===============================
        // LISTAR HORARIOS (opcional)
        // ===============================
        case "listar": 
            $estado = $_POST["estado"] ?? $_GET["estado"] ?? 1; // Parámetro opcional
            $stmt = $horario->listarHorarios($estado); // Llamar al método de listado
            $data = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
            $response = ["status" => "success", "data" => $data];
            break;
    } // fin switch
}

// Devuelve respuesta JSON al frontend
header("Content-Type: application/json; charset=utf-8");
echo json_encode($response);
?>

