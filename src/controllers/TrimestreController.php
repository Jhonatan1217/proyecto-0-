<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once __DIR__ . '/../../config/database.php';
include_once __DIR__ . '/../models/Trimestre.php';

$trimestre = new Trimestre($conn);
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    /* ================================
       LISTAR O CONSULTAR UN TRIMESTRE
    ================================== */
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

    /* ================================
       CREAR / EDITAR / SUSPENDER / REACTIVAR
    ================================== */
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) $input = $_POST; // soporte alternativo

        if (isset($input['accion'])) {
            switch ($input['accion']) {

                /* === SUSPENDER === */
                case 'suspender':
                    if (!empty($input['numero_trimestre'])) {
                        $ok = $trimestre->eliminar($input['numero_trimestre']);
                        echo json_encode([
                            'status' => $ok ? 'success' : 'error',
                            'mensaje' => $ok ? 'Trimestre suspendido correctamente' : 'Error al suspender'
                        ]);
                    } else {
                        echo json_encode(['status' => 'error', 'mensaje' => 'Falta el número de trimestre']);
                    }
                    break;

                /* === REACTIVAR === */
                case 'reactivar':
                    if (!empty($input['numero_trimestre'])) {
                        $ok = $trimestre->reactivar($input['numero_trimestre']);
                        echo json_encode([
                            'status' => $ok ? 'success' : 'error',
                            'mensaje' => $ok ? 'Trimestre reactivado correctamente' : 'Error al reactivar'
                        ]);
                    } else {
                        echo json_encode(['status' => 'error', 'mensaje' => 'Falta el número de trimestre']);
                    }
                    break;

                /* === EDITAR === */
                case 'editar':
                    if (!empty($input['numero_trimestre']) && !empty($input['nuevo_numero'])) {
                        // Validar duplicados
                        $existe = $trimestre->existe($input['nuevo_numero']);
                        if ($existe) {
                            echo json_encode(['status' => 'error', 'mensaje' => 'Ya existe un trimestre con ese número']);
                            break;
                        }

                        // Validar que sea número entero y no decimal
                        if (!ctype_digit(strval($input['nuevo_numero']))) {
                            echo json_encode(['status' => 'error', 'mensaje' => 'El número de trimestre debe ser un entero válido']);
                            break;
                        }

                        $ok = $trimestre->editar($input['numero_trimestre'], $input['nuevo_numero']);
                        echo json_encode([
                            'status' => $ok ? 'success' : 'error',
                            'mensaje' => $ok ? 'Trimestre actualizado correctamente' : 'Error al actualizar'
                        ]);
                    } else {
                        echo json_encode(['status' => 'error', 'mensaje' => 'Faltan datos para editar']);
                    }
                    break;

                default:
                    echo json_encode(['status' => 'error', 'mensaje' => 'Acción no reconocida']);
                    break;
            }
        } else {
            /* === CREAR === */
            if (!empty($input['numero_trimestre'])) {

                // Validar si ya existe
                $existe = $trimestre->existe($input['numero_trimestre']);
                if ($existe) {
                    echo json_encode(['status' => 'error', 'mensaje' => 'Ya existe un trimestre con ese número']);
                    break;
                }

                // Validar que sea número entero y positivo
                if (!ctype_digit(strval($input['numero_trimestre']))) {
                    echo json_encode(['status' => 'error', 'mensaje' => 'El número de trimestre debe ser un entero válido']);
                    break;
                }

                $ok = $trimestre->crear($input['numero_trimestre'], $input['estado'] ?? 1);
                echo json_encode([
                    'status' => $ok ? 'success' : 'error',
                    'mensaje' => $ok ? 'Trimestre creado correctamente' : 'Error al crear'
                ]);
            } else {
                echo json_encode(['status' => 'error', 'mensaje' => 'Faltan datos obligatorios']);
            }
        }
        break;

    /* ================================
       MÉTODO NO PERMITIDO
    ================================== */
    default:
        echo json_encode(['status' => 'error', 'mensaje' => 'Método no permitido']);
        break;
}
?>
