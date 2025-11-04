<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config/database.php';

// ===============================
// DETECTAR ACCIÓN
// ===============================
$accion = $_GET['accion'] ?? $_POST['accion'] ?? 'listar';

switch ($accion) {

    // ===============================
    // LISTAR PROGRAMAS
    // ===============================
    case 'listar':
        try {
            $stmt = $conn->query("SELECT * FROM programas ORDER BY id_programa ASC");
            $programas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($programas);
        } catch (PDOException $e) {
            echo json_encode(["error" => $e->getMessage()]);
        }
        break;

    // ===============================
    // AGREGAR PROGRAMA
    // ===============================
    case 'agregar':
        $data = json_decode(file_get_contents("php://input"), true) ?? $_POST;

        if (!isset($data['id_programa'], $data['nombre_programa'], $data['descripcion'], $data['duracion'])) {
            echo json_encode(["error" => "Faltan datos obligatorios."]);
            exit;
        }

        try {
            $stmt = $conn->prepare("INSERT INTO programas (id_programa, nombre_programa, descripcion, duracion, estado)
                                    VALUES (?, ?, ?, ?, 1)");
            $stmt->execute([
                $data['id_programa'],
                $data['nombre_programa'],
                $data['descripcion'],
                $data['duracion']
            ]);
            echo json_encode(["success" => "Programa agregado correctamente."]);
        } catch (PDOException $e) {
            echo json_encode(["error" => $e->getMessage()]);
        }
        break;

    // ===============================
    // ACTUALIZAR PROGRAMA
    // ===============================
    case 'actualizar':
        $data = json_decode(file_get_contents("php://input"), true) ?? $_POST;

        if (!isset($data['id_programa'], $data['nombre_programa'], $data['descripcion'], $data['duracion'])) {
            echo json_encode(["error" => "Faltan datos obligatorios."]);
            exit;
        }

        try {
            $stmt = $conn->prepare("UPDATE programas 
                                    SET nombre_programa=?, descripcion=?, duracion=? 
                                    WHERE id_programa=?");
            $stmt->execute([
                $data['nombre_programa'],
                $data['descripcion'],
                $data['duracion'],
                $data['id_programa']
            ]);
            echo json_encode(["success" => "Programa actualizado correctamente."]);
        } catch (PDOException $e) {
            echo json_encode(["error" => $e->getMessage()]);
        }
        break;

    // ===============================
    // ELIMINAR PROGRAMA
    // ===============================
    case 'eliminar':
        $id = $_GET['id_programa'] ?? $_POST['id_programa'] ?? null;
        if (!$id) {
            echo json_encode(["error" => "No se especificó el id_programa."]);
            exit;
        }

        try {
            $stmt = $conn->prepare("DELETE FROM programas WHERE id_programa = ?");
            $stmt->execute([$id]);
            echo json_encode(["success" => "Programa eliminado correctamente."]);
        } catch (PDOException $e) {
            echo json_encode(["error" => $e->getMessage()]);
        }
        break;

    // ===============================
    // OBTENER UN PROGRAMA POR ID
    // ===============================
    case 'obtener':
        $id = $_GET['id_programa'] ?? $_POST['id_programa'] ?? null;
        if (!$id) {
            echo json_encode(["error" => "No se especificó el id_programa."]);
            exit;
        }

        try {
            $stmt = $conn->prepare("SELECT * FROM programas WHERE id_programa = ?");
            $stmt->execute([$id]);
            $programa = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($programa) {
                echo json_encode($programa);
            } else {
                echo json_encode(["error" => "Programa no encontrado."]);
            }
        } catch (PDOException $e) {
            echo json_encode(["error" => $e->getMessage()]);
        }
        break;

    // ===============================
    // INHABILITAR PROGRAMA
    // ===============================
    case 'inhabilitar':
        $id = $_GET['id_programa'] ?? $_POST['id_programa'] ?? null;
        if (!$id) {
            echo json_encode(["error" => "No se especificó el id_programa."]);
            exit;
        }

        try {
            $stmt = $conn->prepare("UPDATE programas SET estado = 0 WHERE id_programa = ?");
            $stmt->execute([$id]);
            echo json_encode(["success" => "Programa inhabilitado correctamente."]);
        } catch (PDOException $e) {
            echo json_encode(["error" => $e->getMessage()]);
        }
        break;

    // ===============================
    // ACTIVAR PROGRAMA
    // ===============================
    case 'activar':
        $id = $_GET['id_programa'] ?? $_POST['id_programa'] ?? null;
        if (!$id) {
            echo json_encode(["error" => "No se especificó el id_programa."]);
            exit;
        }

        try {
            $stmt = $conn->prepare("UPDATE programas SET estado = 1 WHERE id_programa = ?");
            $stmt->execute([$id]);
            echo json_encode(["success" => "Programa activado correctamente."]);
        } catch (PDOException $e) {
            echo json_encode(["error" => $e->getMessage()]);
        }
        break;

    // ===============================
    // ACCIÓN INVÁLIDA
    // ===============================
    default:
        echo json_encode([
            "error" => "Acción no válida. Usa: listar, agregar, actualizar, eliminar, obtener, activar o inhabilitar."
        ]);
        break;
}
?>
