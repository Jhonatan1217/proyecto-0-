<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

include_once __DIR__ . '/../../config/database.php';
include_once __DIR__ . '/../models/Solicitud.php';

// Verifica la conexión con la base de datos
if (!isset($conn)) {
    echo json_encode(['status' => 'error', 'message' => 'No se pudo establecer conexión con la base de datos']);
    exit;
}

$solicitud = new Solicitud($conn);

// Acción desde GET, POST o JSON body
$input = json_decode(file_get_contents('php://input'), true) ?? [];
$accion = $_POST['accion'] ?? $_GET['accion'] ?? $input['accion'] ?? null;
$response = ["status" => "error", "message" => "Acción no válida"];

if (!$accion) {
    echo json_encode(['status' => 'error', 'message' => 'Debe especificar la acción (?accion=...)']);
    exit;
}

switch ($accion) {

    // ===============================
    // LISTAR TODAS LAS SOLICITUDES
    // ===============================
    case 'listar':
        $data = $solicitud->listar();
        $response = ["status" => "success", "data" => $data];
        break;

    // ===============================
    // LISTAR SOLICITUDES PENDIENTES
    // ===============================
    case 'listar_pendientes':
        $data = $solicitud->listarPendientes();
        $response = ["status" => "success", "data" => $data];
        break;

    // ===============================
    // LISTAR SOLICITUDES APROBADAS
    // ===============================
    case 'listar_aprobadas':
        $data = $solicitud->listarAprobadas();
        $response = ["status" => "success", "data" => $data];
        break;

    // ===============================
    // LISTAR SOLICITUDES DEVUELTAS
    // ===============================
    case 'listar_devueltas':
        $data = $solicitud->listarDevueltas();
        $response = ["status" => "success", "data" => $data];
        break;

    // ===============================
    // LISTAR SOLICITUDES POR ESTADO
    // ===============================
    case 'listar_por_estado':
        $estado = $_GET['estado'] ?? $_POST['estado'] ?? null;
        if (!$estado) {
            $response = ["status" => "error", "message" => "Debe enviar el estado (PENDIENTE, APROBADO, DEVUELTO)"];
            break;
        }
        $data = $solicitud->listarPorEstado($estado);
        $response = ["status" => "success", "data" => $data];
        break;

    // ===============================
    // LISTAR SOLICITUDES POR INSTRUCTOR
    // ===============================
    case 'listar_por_instructor':
        $id_instructor = $_GET['id_instructor'] ?? $_POST['id_instructor'] ?? null;
        if (!$id_instructor) {
            $response = ["status" => "error", "message" => "Debe enviar id_instructor"];
            break;
        }
        $data = $solicitud->listarPorInstructor($id_instructor);
        $response = ["status" => "success", "data" => $data];
        break;

    // ===============================
    // OBTENER UNA SOLICITUD ESPECÍFICA
    // ===============================
    case 'obtener':
        $id_solicitud = $_GET['id_solicitud'] ?? $_POST['id_solicitud'] ?? null;

        if (!$id_solicitud) {
            $response = ["status" => "error", "message" => "Debe enviar id_solicitud"];
            break;
        }

        $data = $solicitud->obtenerPorId($id_solicitud);
        if ($data) {
            $response = ["status" => "success", "data" => $data];
        } else {
            $response = ["status" => "error", "message" => "Solicitud no encontrada"];
        }
        break;

    // ===============================
    // CREAR SOLICITUD CON DETALLES
    // ===============================
    case 'crear':
        $tipo_solicitud = $input['tipo_solicitud'] ?? $_POST['tipo_solicitud'] ?? null;
        $id_instructor_solicitante = $input['id_instructor_solicitante'] ?? $_POST['id_instructor_solicitante'] ?? null;
        $detalles = $input['detalles'] ?? $_POST['detalles'] ?? null;
        $cambios = $input['cambios'] ?? null;

        if (!$tipo_solicitud || !$id_instructor_solicitante) {
            $response = ["status" => "error", "message" => "Debe enviar tipo_solicitud (HORARIO/DATOS) e id_instructor_solicitante"];
            break;
        }
        if($cambios){
            $detalles = [
                [
                "campo_modificado" => "HORARIO",
                "valor_anterior" => "",
                "valor_nuevo" => $cambios
            ]
        ];
        }

        $response = $solicitud->crear($tipo_solicitud, $id_instructor_solicitante, $detalles ?: []);
        break;

    // ===============================
    // RESPONDER SOLICITUD (APROBAR/DEVOLVER)
    // ===============================
    case 'responder':
        $id_solicitud = $_POST['id_solicitud'] ?? null;
        $estado = $_POST['estado'] ?? null;
        $observacion_respuesta = $_POST['observacion_respuesta'] ?? '';
        $id_coordinador_aprobador = $_POST['id_coordinador_aprobador'] ?? null;

        if (!$id_solicitud || !$estado || !$id_coordinador_aprobador) {
            $response = ["status" => "error", "message" => "Debe enviar id_solicitud, estado (APROBADO/DEVUELTO) e id_coordinador_aprobador"];
            break;
        }

        $response = $solicitud->responder($id_solicitud, $estado, $observacion_respuesta, $id_coordinador_aprobador);
        break;

    // ===============================
    // ACTUALIZAR SOLICITUD Y DETALLES
    // ===============================
    case 'actualizar':
        $id_solicitud = $_POST['id_solicitud'] ?? null;
        $tipo_solicitud = $_POST['tipo_solicitud'] ?? null;
        $detalles = $_POST['detalles'] ?? null;

        if (!$id_solicitud || !$tipo_solicitud) {
            $response = ["status" => "error", "message" => "Debe enviar id_solicitud y tipo_solicitud"];
            break;
        }

        // Validar tipo_solicitud
        if (!in_array($tipo_solicitud, ['HORARIO', 'DATOS'])) {
            $response = ["status" => "error", "message" => "tipo_solicitud debe ser HORARIO o DATOS"];
            break;
        }

        // Procesar detalles si vienen en JSON
        if ($detalles && is_string($detalles)) {
            $detalles = json_decode($detalles, true);
        }

        $response = $solicitud->actualizar($id_solicitud, $tipo_solicitud, $detalles ?: []);
        break;

    // ===============================
    // AGREGAR DETALLE A SOLICITUD
    // ===============================
    case 'agregar_detalle':
        $id_solicitud = $_POST['id_solicitud'] ?? null;
        $campo_modificado = $_POST['campo_modificado'] ?? null;
        $valor_anterior = $_POST['valor_anterior'] ?? null;
        $valor_nuevo = $_POST['valor_nuevo'] ?? null;

        if (!$id_solicitud || !$campo_modificado || !$valor_nuevo) {
            $response = ["status" => "error", "message" => "Debe enviar id_solicitud, campo_modificado y valor_nuevo"];
            break;
        }

        $response = $solicitud->agregarDetalle($id_solicitud, $campo_modificado, $valor_anterior, $valor_nuevo);
        break;

    // ===============================
    // ELIMINAR DETALLE
    // ===============================
    case 'eliminar_detalle':
        $id_detalle = $_POST['id_detalle'] ?? $_GET['id_detalle'] ?? null;

        if (!$id_detalle) {
            $response = ["status" => "error", "message" => "Debe enviar id_detalle"];
            break;
        }

        $response = $solicitud->eliminarDetalle($id_detalle);
        break;

    // ===============================
    // ELIMINAR SOLICITUD
    // ===============================
    case 'eliminar':
        $id_solicitud = $_POST['id_solicitud'] ?? $_GET['id_solicitud'] ?? null;

        if (!$id_solicitud) {
            $response = ["status" => "error", "message" => "Debe enviar id_solicitud"];
            break;
        }

        $response = $solicitud->eliminar($id_solicitud);
        break;

    // ===============================
    // CONTAR SOLICITUDES POR ESTADO (ESTADÍSTICAS)
    // ===============================
    case 'contar_por_estado':
        $data = $solicitud->contarPorEstado();
        $response = ["status" => "success", "data" => $data];
        break;

    default:
        $response = ["status" => "error", "message" => "Acción no válida"];
        break;
}

// Devuelve JSON
echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>