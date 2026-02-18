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
include_once __DIR__ . '/../../config/database.php';
include_once __DIR__ . '/../models/Area.php';

// --- Verificar conexión ---
if (!isset($conn) || !$conn) {
    echo json_encode([
        'status' => 'error',
        'message' => 'No se pudo establecer conexión con la base de datos'
    ]);
    exit;
}

$area   = new Area($conn);
$accion = $_GET['accion'] ?? $_POST['accion'] ?? null; // Permitir acción vía GET o POST

if (!$accion) { 
    echo json_encode([
        'status' => 'error',
        'message' => 'Debe especificar la acción, por ejemplo: ?accion=listar'
    ]);
    exit; // Terminar si no se especifica acción
}

// --- Manejo de acciones ---
try {
    switch ($accion) {

        // Listar áreas
        case 'listar':
            $res = $area->listar(); // Obtener todas las áreas
            echo json_encode([
                'status'  => 'success',
                'data'    => $res,
                'message' => 'Áreas listadas correctamente'
            ]);
            break;

        // Obtener área por ID
        case 'obtener':
            $id_area = $_GET['id_area'] ?? null; // ID vía GET
            if (!$id_area) { // Verificar si se proporcionó ID
                echo json_encode([
                    'status'  => 'error',
                    'message' => 'Debe enviar el parámetro id_area'
                ]);
                exit;
            }

            $res = $area->obtenerPorId($id_area); // Obtener área por ID
            if ($res) { // Verificar si se encontró el área
                echo json_encode([
                    'status'  => 'success',
                    'data'    => $res,
                    'message' => 'Área encontrada'
                ]);
            } else {
                echo json_encode([ // Área no encontrada
                    'status'  => 'warning',
                    'message' => 'Área no encontrada'
                ]);
            }
            break;

        // Crear nueva área
        case 'crear':
            $data        = json_decode(file_get_contents("php://input"), true); // Decodificar JSON
            $nombre_area = $data['nombre_area'] ?? $_POST['nombre_area'] ??  null; // Nombre vía JSON o POST

            if (!$nombre_area) { // Verificar si se proporcionó nombre
                echo json_encode([
                    'status'  => 'error',
                    'message' => 'Debe enviar nombre_area'
                ]);
                exit;
            }

            $area->crear($nombre_area); // Crear nueva área
            echo json_encode([ // Respuesta exitosa
                'status'  => 'success',
                'message' => 'Área creada correctamente'
            ]);
            break;

        // Actualizar área
        case 'actualizar':
            $data        = json_decode(file_get_contents("php://input"), true); // Decodificar JSON
            $id_area     = $data['id_area'] ?? $_POST['id_area'] ?? null; // ID vía JSON o POST
            $nombre_area = $data['nombre_area'] ?? $_POST['nombre_area'] ?? null; // Nombre vía JSON o POST

            if (!$id_area || !$nombre_area) { // Verificar si se proporcionaron ambos
                echo json_encode([ // Error si falta alguno
                    'status'  => 'error',
                    'message' => 'Debe enviar id_area y nombre_area'
                ]);
                exit;
            }

            $area->actualizar($id_area, $nombre_area); // Actualizar área
            echo json_encode([ // Respuesta exitosa
                'status'  => 'success',
                'message' => 'Área actualizada correctamente'
            ]);
            break;

        // Eliminar área
        case 'eliminar':
            $data    = json_decode(file_get_contents("php://input"), true); // Decodificar JSON
            $id_area = $data['id_area'] ?? $_POST['id_area'] ?? null;  // ID vía JSON o POST

            if (!$id_area) { // Verificar si se proporcionó ID
                echo json_encode([ // Error si falta ID
                    'status'  => 'error',
                    'message' => 'Debe enviar id_area'
                ]);
                exit;
            }

            $area->eliminar($id_area); // Eliminar área
            echo json_encode([ // Respuesta exitosa
                'status'  => 'success',
                'message' => 'Área eliminada correctamente'
            ]);
            break;

        // Cambiar estado (activo/inactivo) con opción de cascada
        case 'cambiar_estado':
            // Decodificar datos enviados en JSON
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

            $id_area = (int)$id_area;
            $estado  = (int)$estado;
            $cascada = (int)$cascada;

            // Si viene con cascada => área + zonas relacionadas con el mismo estado
            if ($cascada === 1) {
                try {
                    // Iniciar transacción
                    $conn->beginTransaction();

                    // 1) Cambiar estado del área (modelo)
                    $area->cambiarEstado($id_area, $estado);

                    // 2) Actualizar zonas relacionadas al mismo estado
                    //    Tabla: zonas, campos: id_area, estado
                    $sqlZonas = "UPDATE zonas SET estado = :estado WHERE id_area = :id_area";
                    $stmtZ    = $conn->prepare($sqlZonas);
                    $stmtZ->execute([
                        ':estado'  => $estado,
                        ':id_area' => $id_area
                    ]);

                    // Aquí puedes añadir más tablas relacionadas, siempre usando el mismo $estado

                    $conn->commit();

                    echo json_encode([
                        'status'  => 'success',
                        'message' => $estado === 1
                            ? 'Área y zonas relacionadas habilitadas correctamente'
                            : 'Área y zonas relacionadas deshabilitadas correctamente'
                    ]);
                } catch (Exception $e) {
                    if ($conn->inTransaction()) {
                        $conn->rollBack();
                    }
                    echo json_encode([
                        'status'  => 'error',
                        'message' => 'Error al cambiar el estado del área: ' . $e->getMessage()
                    ]);
                }
            } else {
                // Caso normal: solo cambiar el estado del área
                $area->cambiarEstado($id_area, $estado);
                echo json_encode([
                    'status'  => 'success',
                    'message' => 'Estado del área actualizado correctamente'
                ]);
            }
            break;

        // Acción no válida
        default:
            echo json_encode([
                'status'  => 'error',
                'message' => 'Acción no válida'
            ]);
            break;
    } // Fin switch
} catch (Exception $e) { // Capturar errores generales
    echo json_encode([
        'status'  => 'error',
        'message' => 'Error interno: ' . $e->getMessage()
    ]);
}
?>
