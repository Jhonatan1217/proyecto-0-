<?php
// ============================================
// areaController.php
// ============================================

// --- Configuración de encabezados y CORS ---
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
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
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Area.php';

// --- Verificar conexión ---
if (!isset($conn) || !$conn) {
    echo json_encode([
        'status' => 'error',
        'message' => 'No se pudo establecer conexión con la base de datos'
    ]);
    exit;
}

$area   = new Area($conn);
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

        // Listar áreas
        case 'listar':
            $res = $area->listar();
            echo json_encode([
                'status'  => 'success',
                'data'    => $res,
                'message' => 'Áreas listadas correctamente'
            ]);
            break;

        // Obtener área por ID
        case 'obtener':
            $id_area = $_GET['id_area'] ?? null;
            if (!$id_area) {
                echo json_encode([
                    'status'  => 'error',
                    'message' => 'Debe enviar el parámetro id_area'
                ]);
                exit;
            }

            $res = $area->obtenerPorId($id_area);
            if ($res) {
                echo json_encode([
                    'status'  => 'success',
                    'data'    => $res,
                    'message' => 'Área encontrada'
                ]);
            } else {
                echo json_encode([
                    'status'  => 'warning',
                    'message' => 'Área no encontrada'
                ]);
            }
            break;

        // Crear nueva área
        case 'crear':
            $data = json_decode(file_get_contents("php://input"), true);
            $nombre_area = trim($data['nombre_area'] ?? $_POST['nombre_area'] ?? '');

            if (empty($nombre_area)) {
                echo json_encode([
                    'status'  => 'error',
                    'message' => 'Debe enviar el nombre del área'
                ]);
                exit;
            }

            try {
                $id_creado = $area->crear($nombre_area);
                echo json_encode([
                    'status'  => 'success',
                    'data'    => ['id_area' => $id_creado],
                    'message' => 'Área creada correctamente'
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'status'  => 'error',
                    'message' => $e->getMessage()
                ]);
            }
            break;

        // Actualizar área
        case 'actualizar':
            $data = json_decode(file_get_contents("php://input"), true);
            $id_area = $data['id_area'] ?? $_POST['id_area'] ?? null;
            $nombre_area = trim($data['nombre_area'] ?? $_POST['nombre_area'] ?? '');

            if (!$id_area || empty($nombre_area)) {
                echo json_encode([
                    'status'  => 'error',
                    'message' => 'Debe enviar id_area y nombre_area'
                ]);
                exit;
            }

            try {
                $area->actualizar($id_area, $nombre_area);
                echo json_encode([
                    'status'  => 'success',
                    'message' => 'Área actualizada correctamente'
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'status'  => 'error',
                    'message' => $e->getMessage()
                ]);
            }
            break;

        // Eliminar área
        case 'eliminar':
            $data = json_decode(file_get_contents("php://input"), true);
            $id_area = $data['id_area'] ?? $_POST['id_area'] ?? null;

            if (!$id_area) {
                echo json_encode([
                    'status'  => 'error',
                    'message' => 'Debe enviar id_area'
                ]);
                exit;
            }

            try {
                $area->eliminar($id_area);
                echo json_encode([
                    'status'  => 'success',
                    'message' => 'Área eliminada correctamente'
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'status'  => 'error',
                    'message' => $e->getMessage()
                ]);
            }
            break;

        // Cambiar estado
        case 'cambiar_estado':
            $data = json_decode(file_get_contents("php://input"), true);
            if (!is_array($data)) {
                $data = [];
            }

            $id_area = $data['id_area'] ?? $_POST['id_area'] ?? $_GET['id_area'] ?? null;
            $estado  = $data['estado']  ?? $_POST['estado']  ?? $_GET['estado']  ?? null;
            $cascada = $data['cascada'] ?? $_POST['cascada'] ?? $_GET['cascada'] ?? 0;

            if ($id_area === null || $estado === null) {
                echo json_encode([
                    'status'  => 'error',
                    'message' => 'Debe enviar id_area y estado (1 o 0)'
                ]);
                exit;
            }

            if ($estado != 1 && $estado != 0) {
                echo json_encode([
                    'status'  => 'error',
                    'message' => 'El estado debe ser 1 (activo) o 0 (inactivo)'
                ]);
                exit;
            }

            try {
                if ($cascada == 1) {
                    // Iniciar transacción
                    $conn->beginTransaction();

                    // Cambiar estado del área
                    $area->cambiarEstado($id_area, $estado);

                    // Actualizar zonas relacionadas
                    $sqlZonas = "UPDATE zonas SET estado = :estado WHERE id_area = :id_area";
                    $stmtZ = $conn->prepare($sqlZonas);
                    $stmtZ->execute([
                        ':estado' => $estado,
                        ':id_area' => $id_area
                    ]);

                    $conn->commit();

                    echo json_encode([
                        'status'  => 'success',
                        'message' => $estado == 1
                            ? 'Área y zonas relacionadas habilitadas correctamente'
                            : 'Área y zonas relacionadas deshabilitadas correctamente'
                    ]);
                } else {
                    $area->cambiarEstado($id_area, $estado);
                    echo json_encode([
                        'status'  => 'success',
                        'message' => 'Estado del área actualizado correctamente'
                    ]);
                }
            } catch (Exception $e) {
                if (isset($conn) && $conn->inTransaction()) {
                    $conn->rollBack();
                }
                echo json_encode([
                    'status'  => 'error',
                    'message' => 'Error: ' . $e->getMessage()
                ]);
            }
            break;

        default:
            echo json_encode([
                'status'  => 'error',
                'message' => 'Acción no válida'
            ]);
            break;
    }
} catch (Exception $e) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Error interno: ' . $e->getMessage()
    ]);
}
?>