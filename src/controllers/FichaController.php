<?php
// ============================================
// fichaController.php
// ============================================

// --- Configuración de encabezados y CORS ---
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=utf-8");

// Manejar preflight (CORS OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// --- Errores visibles solo en desarrollo ---
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// --- Conexión y modelo ---
include_once __DIR__ . '/../../config/database.php';
include_once __DIR__ . '/../models/Ficha.php';

// --- Verificar conexión ---
if (!isset($conn) || !$conn) {
    echo json_encode([
        'status' => 'error',
        'message' => 'No se pudo establecer conexión con la base de datos'
    ]);
    exit;
}

$ficha = new Ficha($conn);
$accion = $_GET['accion'] ?? $_POST['accion'] ?? null;

if (!$accion) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Debe especificar la acción, por ejemplo: ?accion=listar'
    ]);
    exit;
}

// --- Manejo de acciones ---
try {
    switch ($accion) {

        // Listar fichas (opcional: filtrar por programa y/o búsqueda por número o nombre programa)
        case 'listar':
            $buscar = trim($_GET['buscar'] ?? '');
            $id_programa = trim($_GET['id_programa'] ?? '');
            $res = $ficha->listar($buscar, $id_programa);
            echo json_encode([
                'status' => 'success',
                'data' => $res,
                'message' => 'Fichas listadas correctamente'
            ]);
            break;

        // Obtener datos para los selectores (programas e instructores)
        case 'obtener_datos_selectores':
            $programas = $ficha->obtenerProgramas();
            $instructores = $ficha->obtenerInstructores();
            
            echo json_encode([
                'status' => 'success',
                'data' => [
                    'programas' => $programas,
                    'lideres' => $instructores
                ],
                'message' => 'Datos cargados correctamente'
            ]);
            break;

        // Obtener ficha por ID
        case 'obtener':
            $id_ficha = $_GET['id_ficha'] ?? null;
            
            if (!$id_ficha) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Debe enviar el parámetro id_ficha'
                ]);
                exit;
            }

            $res = $ficha->obtenerPorId($id_ficha);
            if ($res) {
                echo json_encode([
                    'status' => 'success',
                    'data' => $res,
                    'message' => 'Ficha encontrada'
                ]);
            } else {
                echo json_encode([
                    'status' => 'warning',
                    'message' => 'Ficha no encontrada'
                ]);
            }
            break;

        // Crear nueva ficha
        case 'crear':
            $data = json_decode(file_get_contents("php://input"), true);
            
            $numero_ficha = $data['numero_ficha'] ?? $_POST['numero_ficha'] ?? null;
            $id_programa = $data['id_programa'] ?? $_POST['id_programa'] ?? null;
            $jornada = $data['jornada'] ?? $_POST['jornada'] ?? null;
            $modalidad = $data['modalidad'] ?? $_POST['modalidad'] ?? null;
            $id_lider_grupo = $data['id_lider_grupo'] ?? $_POST['id_lider_grupo'] ?? null;

            // Validaciones
            if (!$numero_ficha || !$id_programa || !$jornada || !$modalidad || !$id_lider_grupo) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Debe enviar numero_ficha, id_programa, jornada, modalidad e id_lider_grupo'
                ]);
                exit;
            }

            // Validar jornada
            $jornadas_validas = ['DIURNA', 'MIXTA', 'NOCTURNA'];
            if (!in_array($jornada, $jornadas_validas)) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Jornada no válida. Debe ser: DIURNA, MIXTA o NOCTURNA'
                ]);
                exit;
            }

            // Validar modalidad
            $modalidades_validas = ['PRESENCIAL', 'VIRTUAL', 'A DISTANCIA'];
            if (!in_array($modalidad, $modalidades_validas)) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Modalidad no válida. Debe ser: PRESENCIAL, VIRTUAL o A DISTANCIA'
                ]);
                exit;
            }

            // Validar que el número de ficha sea numérico
            if (!is_numeric($numero_ficha)) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'El número de ficha debe ser un valor numérico'
                ]);
                exit;
            }

            // Validar que el programa existe
            $programas = $ficha->obtenerProgramas();
            $idsProgramas = array_map('strval', array_column($programas, 'id_programa'));
            if (!in_array((string) $id_programa, $idsProgramas, true)) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'El programa seleccionado no es válido. Por favor seleccione un programa de la lista.'
                ]);
                exit;
            }

            // Validar que el número de la ficha no exista ya
            $existeFicha = $ficha->existeNumeroFicha($numero_ficha);
            if ($existeFicha) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Ya existe una ficha con ese número'
                ]);
                exit;
            }
            
            // Validar que el número de ficha tenga máximo 10 dígitos
            if (!preg_match('/^\d{1,10}$/', $numero_ficha)) {
                echo json_encode([
                    "error" => "El número de ficha debe tener máximo 10 dígitos"
                ]);
                exit;
            }
                
            $id_creado = $ficha->crear($numero_ficha, $id_programa, $jornada, $modalidad, $id_lider_grupo);
            
            echo json_encode([
                'status' => 'success',
                'data' => ['id_ficha' => $id_creado],
                'message' => 'Ficha creada correctamente'
            ]);
            break;

        // Actualizar ficha
        case 'actualizar':
            $data = json_decode(file_get_contents("php://input"), true);
            
            $id_ficha = $data['id_ficha'] ?? $_POST['id_ficha'] ?? null;
            $numero_ficha = $data['numero_ficha'] ?? $_POST['numero_ficha'] ?? null;
            $id_programa = $data['id_programa'] ?? $_POST['id_programa'] ?? null;
            $jornada = $data['jornada'] ?? $_POST['jornada'] ?? null;
            $modalidad = $data['modalidad'] ?? $_POST['modalidad'] ?? null;
            $id_lider_grupo = $data['id_lider_grupo'] ?? $_POST['id_lider_grupo'] ?? null;

            if (!$id_ficha || !$numero_ficha || !$id_programa || !$jornada || !$modalidad || !$id_lider_grupo) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Debe enviar id_ficha, numero_ficha, jornada, modalidad e id_lider_grupo'
                ]);
                exit;
            }

            // Validar jornada
            $jornadas_validas = ['DIURNA', 'MIXTA', 'NOCTURNA'];
            if (!in_array($jornada, $jornadas_validas)) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Jornada no válida. Debe ser: DIURNA, MIXTA o NOCTURNA'
                ]);
                exit;
            }

            // Validar modalidad
            $modalidades_validas = ['PRESENCIAL', 'VIRTUAL', 'A DISTANCIA'];
            if (!in_array($modalidad, $modalidades_validas)) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Modalidad no válida. Debe ser: PRESENCIAL o VIRTUAL'
                ]);
                exit;
            }

            // Validar que el número de ficha sea numérico
            if (!is_numeric($numero_ficha)) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'El número de ficha debe ser un valor numérico'
                ]);
                exit;
            }

            // Validar que el programa existe
            $programas = $ficha->obtenerProgramas();
            $idsProgramas = array_map('strval', array_column($programas, 'id_programa'));
            if (!in_array((string) $id_programa, $idsProgramas, true)) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'El programa seleccionado no es válido'
                ]);
                exit;
            }

            $ficha->actualizar($id_ficha, $numero_ficha, $id_programa, $jornada, $modalidad, $id_lider_grupo);
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Ficha actualizada correctamente'
            ]);
            break;

        // Cambiar estado (activar/desactivar)
        case 'cambiarEstado':
        case 'cambiar_estado':
            $data = json_decode(file_get_contents("php://input"), true);
            $id_ficha = $data['id_ficha'] ?? $_POST['id_ficha'] ?? null;
            $estado = isset($data['estado']) ? (int)$data['estado'] : (isset($_POST['estado']) ? (int)$_POST['estado'] : null);

            if (!$id_ficha || ($estado !== 0 && $estado !== 1)) {
                echo json_encode(['status' => 'error', 'message' => 'Debe enviar id_ficha y estado (0 o 1)']);
                exit;
            }

            $ficha->cambiarEstado($id_ficha, $estado);
            echo json_encode(['status' => 'success', 'message' => $estado ? 'Grupo activado' : 'Grupo desactivado']);
            break;

        // Eliminar ficha
        case 'eliminar':
            $data = json_decode(file_get_contents("php://input"), true);
            $id_ficha = $data['id_ficha'] ?? $_POST['id_ficha'] ?? null;

            if (!$id_ficha) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Debe enviar id_ficha'
                ]);
                exit;
            }

            $ficha->eliminar($id_ficha);
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Ficha eliminada correctamente'
            ]);
            break;

        // Cambiar líder de grupo
        case 'cambiar_lider':
            $data = json_decode(file_get_contents("php://input"), true);
            
            $id_ficha = $data['id_ficha'] ?? $_POST['id_ficha'] ?? null;
            $id_lider_grupo = $data['id_lider_grupo'] ?? $_POST['id_lider_grupo'] ?? null;

            if (!$id_ficha || !$id_lider_grupo) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Debe enviar id_ficha y id_lider_grupo'
                ]);
                exit;
            }

            $ficha->cambiarLider($id_ficha, $id_lider_grupo);
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Líder de grupo actualizado correctamente'
            ]);
            break;

        // Buscar fichas por número
        case 'buscar':
            $numero_ficha = $_GET['numero_ficha'] ?? $_POST['numero_ficha'] ?? null;
            
            if (!$numero_ficha) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Debe enviar numero_ficha para buscar'
                ]);
                exit;
            }

            $res = $ficha->buscarPorNumero($numero_ficha);
            
            echo json_encode([
                'status' => 'success',
                'data' => $res,
                'message' => 'Búsqueda completada'
            ]);
            break;

        // Acción no válida
        default:
            echo json_encode([
                'status' => 'error',
                'message' => 'Acción no válida'
            ]);
            break;
    }
} catch (PDOException $e) {
    $msg = 'Error al procesar la solicitud.';
    if ($e->getCode() == 23000 || strpos($e->getMessage(), 'foreign key') !== false) {
        $msg = 'Error de datos: el programa o el líder seleccionado no es válido. Verifique que existan en el sistema.';
    } else {
        $msg = 'Error interno: ' . $e->getMessage();
    }
    echo json_encode(['status' => 'error', 'message' => $msg]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error interno: ' . $e->getMessage()
    ]);
}
?>