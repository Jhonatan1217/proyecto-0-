<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once __DIR__ . '/../../config/database.php';
include_once __DIR__ . '/../models/Trimestre.php';

$trimestre = new Trimestre($conn);
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'GET':
        if (isset($_GET['numero_trimestre'])) {
            $stmt = $trimestre->obtenerPorId($_GET['numero_trimestre']);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($data ?: ['mensaje' => 'No encontrado']);
        } else {
            $stmt = $trimestre->listar();
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        break;

    case 'POST':
        // Soporte tanto para crear, suspender o reactivar
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) $input = $_POST; // Si no viene en JSON, tomarlo como formulario

        if (isset($input['accion'])) {
            switch ($input['accion']) {
                case 'suspender':
                    if (!empty($input['numero_trimestre'])) {
                        $ok = $trimestre->eliminar($input['numero_trimestre']);
                        echo json_encode(['mensaje' => $ok ? 'Trimestre suspendido correctamente' : 'Error al suspender']);
                    } else {
                        echo json_encode(['error' => 'Falta el número de trimestre']);
                    }
                    break;

                case 'reactivar':
                    if (!empty($input['numero_trimestre'])) {
                        $ok = $trimestre->reactivar($input['numero_trimestre']);
                        echo json_encode(['mensaje' => $ok ? 'Trimestre reactivado correctamente' : 'Error al reactivar']);
                    } else {
                        echo json_encode(['error' => 'Falta el número de trimestre']);
                    }
                    break;

                default:
                    echo json_encode(['error' => 'Acción no reconocida']);
                    break;
            }
        } else {
            // Crear trimestre
            if (!empty($input['numero_trimestre'])) {
                $ok = $trimestre->crear($input['numero_trimestre'], $input['estado'] ?? 1);
                echo json_encode(['mensaje' => $ok ? 'Trimestre creado correctamente' : 'Error al crear']);
            } else {
                echo json_encode(['error' => 'Faltan datos obligatorios']);
            }
        }
        break;

    default:
        echo json_encode(['error' => 'Método no permitido']);
        break;
}
?>
