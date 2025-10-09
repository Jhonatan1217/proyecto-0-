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

        // 🔹 Capturar datos del formulario (ajustado al front)
        $data = [
            'dia'           => $_POST['dia_semana'] ?? null,
            'hora_inicio'   => $_POST['hora_inicio'] ?? null,
            'hora_fin'      => $_POST['hora_fin'] ?? null,
            'id_zona'       => $GET['zona'] ?? null,
            'numero_ficha'  => $_POST['numero_ficha'] ?? null,
            'nivel_ficha'   => $_POST['nivel_ficha'] ?? 'tecnico',
            'id_instructor' => $_POST['id_instructor'] ?? null
        ];

        try {
            // 🔸 Validar datos base
            if (empty($data['dia']) || empty($data['hora_inicio']) || empty($data['hora_fin'])) {
                throw new Exception("Faltan campos obligatorios del horario (día u horas).");
            }

            if (empty($data['numero_ficha'])) {
                throw new Exception("Debe ingresar el número de ficha.");
            }

            // 🔸 Buscar o crear ficha
            $stmt = $conn->prepare("SELECT id_ficha FROM fichas WHERE numero_ficha = :num");
            $stmt->bindParam(':num', $data['numero_ficha'], PDO::PARAM_INT);
            $stmt->execute();
            $ficha = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($ficha) {
                $data['id_ficha'] = (int)$ficha['id_ficha'];
            } else {
                $stmt = $conn->prepare("INSERT INTO fichas (numero_ficha, nivel_ficha) VALUES (:num, :nivel)");
                $stmt->bindParam(':num', $data['numero_ficha'], PDO::PARAM_INT);
                $stmt->bindParam(':nivel', $data['nivel_ficha'], PDO::PARAM_STR);
                $stmt->execute();
                $data['id_ficha'] = (int)$conn->lastInsertId();
            }

            // 🔸 Validar zona
            if (empty($data['id_zona'])) {
                throw new Exception("Debe seleccionar una zona válida.");
            }

            // 🔸 Verificar o crear instructor
            if (empty($data['id_instructor'])) {
                $stmt = $conn->prepare("INSERT INTO instructores (nombre_instructor, apellido_instructor, tipo_instructor)
                                        VALUES ('Desconocido', '', 'TECNICO')");
                $stmt->execute();
                $data['id_instructor'] = (int)$conn->lastInsertId();
            } else {
                $stmt = $conn->prepare("SELECT COUNT(*) FROM instructores WHERE id_instructor = :id");
                $stmt->bindParam(':id', $data['id_instructor'], PDO::PARAM_INT);
                $stmt->execute();
                if (!$stmt->fetchColumn()) {
                    throw new Exception("El instructor no existe.");
                }
            }

            // 🔸 Insertar horario con el ID de ficha correcto (no el número de ficha)
            $stmt = $conn->prepare("
                INSERT INTO horarios (dia, hora_inicio, hora_fin, id_zona, id_ficha, id_instructor)
                VALUES (:dia, :hora_inicio, :hora_fin, :id_zona, :id_ficha, :id_instructor)
            ");

            $stmt->bindParam(':dia', $data['dia']);
            $stmt->bindParam(':hora_inicio', $data['hora_inicio']);
            $stmt->bindParam(':hora_fin', $data['hora_fin']);
            $stmt->bindParam(':id_zona', $data['id_zona'], PDO::PARAM_INT);

            // ⚠️ Clave: forzamos a usar el ID autoincremental real
            $idFichaReal = (int)$data['id_ficha'];
            $stmt->bindParam(':id_ficha', $idFichaReal, PDO::PARAM_INT);

            $stmt->bindParam(':id_instructor', $data['id_instructor'], PDO::PARAM_INT);
            $stmt->execute();

            $id_horario = (int)$conn->lastInsertId();

            // 🔸 Crear la trimestralización
            $res = $trimestral->crear($id_horario);

            echo json_encode([
                'success' => true,
                'mensaje' => 'Horario, ficha e instructor registrados correctamente.',
                'id_ficha' => $idFichaReal,
                'id_horario' => $id_horario,
                'id_trimestral' => $res['id_trimestral'] ?? null
            ]);

        } catch (PDOException $e) {
            echo json_encode(['error' => 'Error SQL: ' . $e->getMessage()]);
        } catch (Exception $e) {
            echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
        }
        break;

    case 'eliminar':
        $res = $trimestral->eliminar();
        echo json_encode($res);
        break;

    default:
        echo json_encode(['error' => 'Acción no reconocida']);
        break;
}
?>
