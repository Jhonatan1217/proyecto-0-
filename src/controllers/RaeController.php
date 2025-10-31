<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config/database.php';


// ============================================================
// CONTROLADOR RAE - MODO POR ACCIÓN (?accion=...)
// ============================================================

$accion = $_GET['accion'] ?? '';

switch ($accion) {

    // ============================================================
    // LISTAR TODAS LAS RAES O UNA SOLA
    // ============================================================
    case 'listar':
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $stmt = $conn->prepare("
                SELECT r.id_rae, r.descripcion, r.estado, 
                       c.id_competencia, c.nombre_competencia
                FROM raes r
                LEFT JOIN competencias c ON r.id_competencia = c.id_competencia
                WHERE r.id_rae = ?
            ");
            $stmt->execute([$id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($data ?: []);
        } else {
            $stmt = $conn->prepare("
                SELECT r.id_rae, r.descripcion, r.estado,
                       c.id_competencia, c.nombre_competencia
                FROM raes r
                LEFT JOIN competencias c ON r.id_competencia = c.id_competencia
                ORDER BY r.id_rae DESC
            ");
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($data);
        }
        break;

    // ============================================================
    // CREAR UNA NUEVA RAE
    // ============================================================
    case 'crear':
        if (empty($_GET['descripcion']) || empty($_GET['id_competencia'])) {
            echo json_encode(['error' => 'Faltan campos requeridos']);
            exit;
        }

        $descripcion = trim($_GET['descripcion']);
        $id_competencia = intval($_GET['id_competencia']);

        // Validar duplicado
        $check = $conn->prepare("SELECT COUNT(*) FROM raes WHERE descripcion = ? AND id_competencia = ?");
        $check->execute([$descripcion, $id_competencia]);
        if ($check->fetchColumn() > 0) {
            echo json_encode(['error' => 'Esta RAE ya existe en esa competencia']);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO raes (descripcion, id_competencia, estado) VALUES (?, ?, 1)");
        $ok = $stmt->execute([$descripcion, $id_competencia]);

        echo json_encode(['success' => $ok, 'message' => $ok ? 'RAE creada correctamente' : 'Error al crear la RAE']);
        break;

    // ============================================================
    // ACTUALIZAR UNA RAE EXISTENTE
    // ============================================================
    case 'actualizar':
        if (empty($_GET['id_rae']) || empty($_GET['descripcion']) || empty($_GET['id_competencia'])) {
            echo json_encode(['error' => 'Faltan datos']);
            exit;
        }

        $id = intval($_GET['id_rae']);
        $descripcion = trim($_GET['descripcion']);
        $id_competencia = intval($_GET['id_competencia']);

        // Validar duplicado al editar
        $check = $conn->prepare("SELECT COUNT(*) FROM raes WHERE descripcion = ? AND id_competencia = ? AND id_rae != ?");
        $check->execute([$descripcion, $id_competencia, $id]);
        if ($check->fetchColumn() > 0) {
            echo json_encode(['error' => 'Ya existe una RAE igual en esa competencia']);
            exit;
        }

        $stmt = $conn->prepare("UPDATE raes SET descripcion = ?, id_competencia = ? WHERE id_rae = ?");
        $ok = $stmt->execute([$descripcion, $id_competencia, $id]);

        echo json_encode(['success' => $ok, 'message' => $ok ? 'RAE actualizada correctamente' : 'Error al actualizar la RAE']);
        break;

    // ============================================================
    // INHABILITAR / ACTIVAR RAE
    // ============================================================
    case 'inhabilitar':
        if (empty($_GET['id_rae']) || !isset($_GET['estado'])) {
            echo json_encode(['error' => 'Faltan parámetros']);
            exit;
        }

        $id = intval($_GET['id_rae']);
        $estado = intval($_GET['estado']);

        $stmt = $conn->prepare("UPDATE raes SET estado = ? WHERE id_rae = ?");
        $ok = $stmt->execute([$estado, $id]);

        echo json_encode([
            'success' => $ok,
            'message' => $ok
                ? ($estado ? 'RAE activada correctamente' : 'RAE inhabilitada correctamente')
                : 'Error al cambiar el estado de la RAE'
        ]);
        break;

    default:
        echo json_encode(['error' => 'Acción no válida']);
        break;
}
?>
