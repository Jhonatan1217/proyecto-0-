
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


$horario = new Horario($conn);
$accion = $_POST["accion"] ?? $_GET["accion"] ?? null;

// Respuesta base
$response = ["status" => "error", "message" => "Acción no válida"];

if ($accion) {
    switch ($accion) {

        // ===============================
        // CREAR HORARIO
        // ===============================
        case "crear":
            $dia = $_POST["dia"];
            $hora_inicio = $_POST["hora_inicio"];
            $hora_fin = $_POST["hora_fin"];
            $id_zona = $_POST["id_zona"];
            $id_area = $_POST["id_area"];
            $id_ficha = $_POST["id_ficha"];
            $id_instructor = $_POST["id_instructor"];
            $id_competencia = $_POST["id_competencia"];
            $numero_trimestre = $_POST["numero_trimestre"];

            $resultado = $horario->crearHorario($dia, $hora_inicio, $hora_fin, $id_zona, $id_area, $id_ficha, $id_instructor, $id_competencia, $numero_trimestre);

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
            $id_horario = $_POST["id_horario"];
            $id_ficha = $_POST["id_ficha"];
            $numero_trimestre = $_POST["numero_trimestre"];
            $id_instructor = $_POST["id_instructor"];
            $id_competencia = $_POST["id_competencia"];

            $resultado = $horario->actualizarHorario($id_horario, $id_ficha, $numero_trimestre, $id_instructor, $id_competencia);

            if ($resultado) {
                $response = ["status" => "success", "message" => "Horario actualizado correctamente."];
            } else {
                $response = ["status" => "error", "message" => "Error al actualizar el horario."];
            }
            break;

        // ===============================
        // INHABILITAR HORARIOS POR ZONA
        // ===============================
        case "inhabilitarZona":
            $id_zona = $_POST["id_zona"];

            $resultado = $horario->inhabilitarPorZona($id_zona);

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
            $id_horario = $_POST["id_horario"];

            $resultado = $horario->activarHorario($id_horario);

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
            $estado = $_POST["estado"] ?? $_GET["estado"] ?? 1;
            $stmt = $horario->listarHorarios($estado);
            $data = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
            $response = ["status" => "success", "data" => $data];
            break;
    }
}

// Devuelve respuesta JSON al frontend
header("Content-Type: application/json; charset=utf-8");
echo json_encode($response);
?>

