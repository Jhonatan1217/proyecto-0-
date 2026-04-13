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

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- Permisos según sesión (coordinador / es_sistema ven todo; instructores solo lo propio) ---
function sol_sess_id() {
    return (int) ($_SESSION['usuario_id'] ?? 0);
}

function sol_sess_cargo_norm() {
    return strtoupper(trim((string) ($_SESSION['usuario_cargo'] ?? '')));
}

function sol_sess_es_sistema() {
    return (int) ($_SESSION['usuario_es_sistema'] ?? 0) === 1;
}

function sol_es_coordinador_rol() {
    return sol_sess_cargo_norm() === 'COORDINADOR';
}

/** Coordinador o cuenta sistema: listan y gestionan solicitudes de terceros */
function sol_puede_ver_todas_solicitudes() {
    return sol_sess_es_sistema() || sol_es_coordinador_rol();
}

/** Solo coordinador o administrador (es_sistema) pueden aprobar / devolver */
function sol_puede_responder_solicitudes() {
    return sol_sess_es_sistema() || sol_es_coordinador_rol();
}

function sol_requiere_sesion_json() {
    if (sol_sess_id() <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Debe iniciar sesión para continuar.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

function sol_requiere_gestion_solicitudes_json() {
    sol_requiere_sesion_json();
    if (!sol_puede_responder_solicitudes()) {
        echo json_encode(['status' => 'error', 'message' => 'No tiene permiso para esta acción.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

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
        sol_requiere_sesion_json();
        if (sol_puede_ver_todas_solicitudes()) {
            $data = $solicitud->listar();
        } else {
            $data = $solicitud->listarPorInstructor(sol_sess_id(), null);
        }
        $response = ["status" => "success", "data" => $data];
        break;

    // ===============================
    // LISTAR SOLICITUDES PENDIENTES
    // ===============================
    case 'listar_pendientes':
        sol_requiere_sesion_json();
        if (sol_puede_ver_todas_solicitudes()) {
            $data = $solicitud->listarPendientes();
        } else {
            $data = $solicitud->listarPorInstructor(sol_sess_id(), 'PENDIENTE');
        }
        $response = ["status" => "success", "data" => $data];
        break;

    // ===============================
    // LISTAR SOLICITUDES APROBADAS
    // ===============================
    case 'listar_aprobadas':
        sol_requiere_sesion_json();
        if (sol_puede_ver_todas_solicitudes()) {
            $data = $solicitud->listarAprobadas();
        } else {
            $data = $solicitud->listarPorInstructor(sol_sess_id(), 'APROBADO');
        }
        $response = ["status" => "success", "data" => $data];
        break;

    // ===============================
    // LISTAR SOLICITUDES DEVUELTAS
    // ===============================
    case 'listar_devueltas':
        sol_requiere_sesion_json();
        if (sol_puede_ver_todas_solicitudes()) {
            $data = $solicitud->listarDevueltas();
        } else {
            $data = $solicitud->listarPorInstructor(sol_sess_id(), 'DEVUELTO');
        }
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
        $estado = strtoupper(trim((string) $estado));
        if (!in_array($estado, ['PENDIENTE', 'APROBADO', 'DEVUELTO'], true)) {
            $response = ["status" => "error", "message" => "Estado no válido (use PENDIENTE, APROBADO o DEVUELTO)"];
            break;
        }
        sol_requiere_sesion_json();
        if (sol_puede_ver_todas_solicitudes()) {
            $data = $solicitud->listarPorEstado($estado);
        } else {
            $data = $solicitud->listarPorInstructor(sol_sess_id(), $estado);
        }
        if (is_array($data) && isset($data['status']) && $data['status'] === 'error') {
            $response = $data;
        } else {
            $response = ["status" => "success", "data" => $data];
        }
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
        sol_requiere_sesion_json();
        $id_instructor = (int) $id_instructor;
        if (!sol_puede_ver_todas_solicitudes() && $id_instructor !== sol_sess_id()) {
            $response = ["status" => "error", "message" => "No autorizado para consultar solicitudes de otro usuario."];
            break;
        }
        $data = $solicitud->listarPorInstructor($id_instructor, null);
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

        sol_requiere_sesion_json();
        $data = $solicitud->obtenerPorId($id_solicitud);
        if (!$data) {
            $response = ["status" => "error", "message" => "Solicitud no encontrada"];
            break;
        }
        if (!sol_puede_ver_todas_solicitudes()) {
            $idSol = (int) ($data['id_instructor_solicitante'] ?? 0);
            if ($idSol !== sol_sess_id()) {
                $response = ["status" => "error", "message" => "No autorizado para ver esta solicitud."];
                break;
            }
        }
        $response = ["status" => "success", "data" => $data];
        break;

    // ===============================
    // CREAR SOLICITUD CON DETALLES
    // ===============================
    case 'crear':
        sol_requiere_sesion_json();
        $tipo_solicitud = $input['tipo_solicitud'] ?? $_POST['tipo_solicitud'] ?? null;
        $id_instructor_solicitante = $input['id_instructor_solicitante'] ?? $_POST['id_instructor_solicitante'] ?? null;
        $detalles = $input['detalles'] ?? $_POST['detalles'] ?? null;
        $cambios = $input['cambios'] ?? null;

        if (is_string($detalles)) {
            $dec = json_decode($detalles, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $detalles = $dec;
            }
        }

        if (!$tipo_solicitud || !$id_instructor_solicitante) {
            $response = ["status" => "error", "message" => "Debe enviar tipo_solicitud (HORARIO/DATOS) e id_instructor_solicitante"];
            break;
        }
        $id_ins = (int) $id_instructor_solicitante;
        if ($id_ins !== sol_sess_id()) {
            $response = ["status" => "error", "message" => "Solo puede crear solicitudes asociadas a su propio usuario."];
            break;
        }
        if($cambios && empty($detalles)){
            $detalles = [
                [
                "campo_modificado" => "HORARIO",
                "valor_anterior" => "",
                "valor_nuevo" => $cambios
            ]
        ];
        }

        $response = $solicitud->crear($tipo_solicitud, $id_ins, $detalles ?: []);
        break;

    // ===============================
    // RESPONDER SOLICITUD (APROBAR/DEVOLVER)
    // ===============================
    case 'responder':
        sol_requiere_sesion_json();
        if (!sol_puede_responder_solicitudes()) {
            $response = ["status" => "error", "message" => "Solo coordinadores o administradores pueden aprobar o devolver solicitudes."];
            break;
        }

        $id_solicitud = $_POST['id_solicitud'] ?? null;
        $estado = $_POST['estado'] ?? null;
        $observacion_respuesta = $_POST['observacion_respuesta'] ?? '';
        $id_coordinador_aprobador = $_POST['id_coordinador_aprobador'] ?? null;

        if (!$id_solicitud || !$estado || !$id_coordinador_aprobador) {
            $response = ["status" => "error", "message" => "Debe enviar id_solicitud, estado (APROBADO/DEVUELTO) e id_coordinador_aprobador"];
            break;
        }

        $idApr = (int) $id_coordinador_aprobador;
        if ($idApr !== sol_sess_id()) {
            $response = ["status" => "error", "message" => "Identificador de aprobador no válido para la sesión actual."];
            break;
        }

        $filaSol = $solicitud->obtenerPorId($id_solicitud);
        if (!$filaSol) {
            $response = ["status" => "error", "message" => "Solicitud no encontrada"];
            break;
        }
        $idSolicitante = (int) ($filaSol['id_instructor_solicitante'] ?? 0);
        // Coordinador no puede aprobar/devolver sus propias solicitudes (sí puede un administrador es_sistema)
        if ($idSolicitante === sol_sess_id() && sol_es_coordinador_rol() && !sol_sess_es_sistema()) {
            $response = ["status" => "error", "message" => "Un coordinador no puede aprobar ni devolver sus propias solicitudes. Debe hacerlo otro coordinador o un administrador."];
            break;
        }

        $response = $solicitud->responder($id_solicitud, $estado, $observacion_respuesta, $idApr);
        break;

    // ===============================
    // ACTUALIZAR SOLICITUD Y DETALLES
    // ===============================
    case 'actualizar':
        sol_requiere_gestion_solicitudes_json();
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
        sol_requiere_gestion_solicitudes_json();
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
        sol_requiere_gestion_solicitudes_json();
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
        sol_requiere_gestion_solicitudes_json();
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
        sol_requiere_sesion_json();
        if (sol_puede_ver_todas_solicitudes()) {
            $data = $solicitud->contarPorEstado();
        } else {
            $mis = $solicitud->listarPorInstructor(sol_sess_id(), null);
            $data = ['pendientes' => 0, 'aprobadas' => 0, 'devueltas' => 0, 'total' => 0];
            foreach ($mis as $row) {
                $e = strtoupper((string) ($row['estado'] ?? ''));
                if ($e === 'PENDIENTE') {
                    $data['pendientes']++;
                } elseif ($e === 'APROBADO') {
                    $data['aprobadas']++;
                } elseif ($e === 'DEVUELTO') {
                    $data['devueltas']++;
                }
                $data['total']++;
            }
        }
        $response = ["status" => "success", "data" => $data];
        break;

    default:
        $response = ["status" => "error", "message" => "Acción no válida"];
        break;
}

// Devuelve JSON
echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>