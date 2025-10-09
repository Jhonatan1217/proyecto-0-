<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

include_once __DIR__ . '/../../config/database.php';
include_once __DIR__ . '/../models/Trimestralizacion.php';

if (!isset($conn)) {
    echo json_encode(['error' => 'No se pudo establecer conexión con la base de datos']);
    exit;
}

$trimestral = new Trimestralizacion($conn);
$accion = $_GET['accion'] ?? null;

if (!$accion) {
    echo json_encode(['error' => 'Debe especificar la acción en la URL, por ejemplo: ?accion=listar']);
    exit;
}

switch ($accion) {
    case 'listar':
        echo json_encode($trimestral->listar());
        break;

    case 'obtener':
        $id = $_GET['id'] ?? null;
        echo json_encode($trimestral->obtenerPorId($id));
        break;

case 'crear':
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['error' => 'Método no permitido']);
        exit;
    }

    // Mapeo de campos del formulario a los del modelo
    $data = [
        'dia'           => $_POST['dia_semana'] ?? null,
        'hora_inicio'   => $_POST['hora_inicio'] ?? null,
        'hora_fin'      => $_POST['hora_fin'] ?? null,
        'id_zona'       => $_POST['zona'] ?? null,
        'id_ficha'      => $_POST['numero_ficha'] ?? null,
        'id_instructor' => $_POST['nombre_instructor'] ?? null
    ];

    try {
        // ✅ --- FICHA ---
        if (empty($data['id_ficha'])) {
            $conn->exec("INSERT INTO fichas () VALUES ()");
            $data['id_ficha'] = $conn->lastInsertId();
        } else {
            $stmt = $conn->prepare("SELECT COUNT(*) FROM fichas WHERE id_ficha = :id");
            $stmt->bindParam(':id', $data['id_ficha'], PDO::PARAM_INT);
            $stmt->execute();
            if (!$stmt->fetchColumn()) {
                $stmt = $conn->prepare("INSERT INTO fichas (id_ficha) VALUES (:id)");
                $stmt->bindParam(':id', $data['id_ficha'], PDO::PARAM_INT);
                $stmt->execute();
            }
        }

        // ✅ --- ZONA ---
        if (empty($data['id_zona'])) {
            $conn->exec("INSERT INTO zonas () VALUES ()");
            $data['id_zona'] = $conn->lastInsertId();
        } else {
            $stmt = $conn->prepare("SELECT COUNT(*) FROM zonas WHERE id_zona = :id");
            $stmt->bindParam(':id', $data['id_zona'], PDO::PARAM_INT);
            $stmt->execute();
            if (!$stmt->fetchColumn()) {
                $stmt = $conn->prepare("INSERT INTO zonas (id_zona) VALUES (:id)");
                $stmt->bindParam(':id', $data['id_zona'], PDO::PARAM_INT);
                $stmt->execute();
            }
        }

        // ✅ --- INSTRUCTOR ---
        if (empty($data['id_instructor'])) {
            // Crear instructor genérico si no se envía nada
            $stmt = $conn->prepare("INSERT INTO instructores (nombre_instructor, apellido_instructor, tipo_instructor) 
                                    VALUES ('Desconocido', '', 'TECNICO')");
            $stmt->execute();
            $data['id_instructor'] = $conn->lastInsertId();
        } else {
            // Verificar si existe instructor con ese ID
            $stmt = $conn->prepare("SELECT COUNT(*) FROM instructores WHERE id_instructor = :id");
            $stmt->bindParam(':id', $data['id_instructor'], PDO::PARAM_INT);
            $stmt->execute();
            if (!$stmt->fetchColumn()) {
                // Si no existe, crearlo con nombre genérico
                $stmt = $conn->prepare("INSERT INTO instructores (id_instructor, nombre_instructor, apellido_instructor, tipo_instructor) 
                                        VALUES (:id, 'Instructor', '', 'TECNICO')");
                $stmt->bindParam(':id', $data['id_instructor'], PDO::PARAM_INT);
                $stmt->execute();
            }
        }

        // ✅ Llamar al modelo con datos garantizados válidos
        $res = $trimestral->crear($data);
        echo json_encode($res);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    break;

    case 'eliminar':
        $id = $_GET['id'] ?? null;
        $res = $trimestral->eliminar($id);
        echo json_encode($res);
        break;

    default:
        echo json_encode(['error' => 'Acción no reconocida']);
        break;
}
