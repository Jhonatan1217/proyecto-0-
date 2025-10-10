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

        //  Capturar los datos del formulario correctamente
        $data = [
            'dia'           => $_POST['dia_semana'] ?? null,
            'hora_inicio'   => $_POST['hora_inicio'] ?? null,
            'hora_fin'      => $_POST['hora_fin'] ?? null,
            'id_zona'       => $_POST['zona'] ?? null, // ← aquí estaba el error
            'numero_ficha'  => $_POST['numero_ficha'] ?? null,
            'nivel_ficha'   => $_POST['nivel_ficha'] ?? null,
            'nombre_instructor' => $_POST['nombre_instructor'] ?? null,
            'tipo_instructor'   => $_POST['tipo_instructor'] ?? null,
            'descripcion'   => $_POST['descripcion'] ?? null,
        ];

        try {
            // 🔸 Validar campos obligatorios
            if (empty($data['dia']) || empty($data['hora_inicio']) || empty($data['hora_fin'])) {
                throw new Exception("Faltan campos obligatorios del horario (día u horas).");
            }

            if (empty($data['numero_ficha'])) {
                throw new Exception("Debe ingresar el número de ficha.");
            }

            if (empty($data['id_zona'])) {
                throw new Exception("Debe seleccionar una zona válida.");
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

            // 🔸 Crear instructor
                $stmt = $conn->prepare("INSERT INTO instructores (nombre_instructor, tipo_instructor)
                VALUES (:nombre, :tipo)");
                $stmt->bindParam(':nombre', $data['nombre_instructor']);
                $stmt->bindParam(':tipo', $data['tipo_instructor']);
                $stmt->execute();
                $data['id_instructor'] = (int)$conn->lastInsertId();

                // 🔸 Crear competencia (guardar descripción)
                $stmt = $conn->prepare("INSERT INTO competencias (descripcion) VALUES (:descripcion)");
                $stmt->bindParam(':descripcion', $data['descripcion']);
                $stmt->execute();
                $data['id_competencia'] = (int)$conn->lastInsertId();

                // 🔸 Insertar horario con datos válidos (incluyendo competencia)
                $stmt = $conn->prepare("
                INSERT INTO horarios (dia, hora_inicio, hora_fin, id_zona, id_ficha, id_instructor, id_competencia)
                VALUES (:dia, :hora_inicio, :hora_fin, :id_zona, :id_ficha, :id_instructor, :id_competencia)
                ");
                $stmt->bindParam(':dia', $data['dia']);
                $stmt->bindParam(':hora_inicio', $data['hora_inicio']);
                $stmt->bindParam(':hora_fin', $data['hora_fin']);
                $stmt->bindParam(':id_zona', $data['id_zona'], PDO::PARAM_INT);
                $stmt->bindParam(':id_ficha', $data['id_ficha'], PDO::PARAM_INT);
                $stmt->bindParam(':id_instructor', $data['id_instructor'], PDO::PARAM_INT);
                $stmt->bindParam(':id_competencia', $data['id_competencia'], PDO::PARAM_INT);
                $stmt->execute();

                $id_horario = (int)$conn->lastInsertId();


            // 🔸 Crear la trimestralización
            $res = $trimestral->crear($id_horario);

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

        case 'actualizar':
            try {
                $id_horario = isset($data['id_horario']) ? (int)$data['id_horario'] : 0;
                if ($id_horario <= 0) {
                    echo json_encode(['error' => 'ID de horario no válido']);
                    break;
                }
        
                // Consultar las relaciones del horario
                $stmt = $conn->prepare("
                    SELECT id_ficha, id_instructor, id_competencia 
                    FROM horarios 
                    WHERE id_horario = :id
                ");
                $stmt->bindParam(':id', $id_horario, PDO::PARAM_INT);
                $stmt->execute();
                $rel = $stmt->fetch(PDO::FETCH_ASSOC);
        
                if (!$rel) {
                    echo json_encode(['error' => 'Horario no encontrado']);
                    break;
                }
        
                // Actualizar ficha (solo número o nivel, según tu estructura)
                if (isset($data['numero_ficha']) || isset($data['nivel_ficha'])) {
                    $sqlFicha = "UPDATE fichas 
                                 SET numero_ficha = :numero_ficha, 
                                     nivel_ficha = :nivel_ficha 
                                 WHERE id_ficha = :id_ficha";
                    $stmt = $conn->prepare($sqlFicha);
                    $stmt->bindParam(':numero_ficha', $data['numero_ficha']);
                    $stmt->bindParam(':nivel_ficha', $data['nivel_ficha']);
                    $stmt->bindParam(':id_ficha', $rel['id_ficha'], PDO::PARAM_INT);
                    $stmt->execute();
                }
        
                // Actualizar instructor
                if (isset($data['nombre_instructor']) || isset($data['tipo_instructor'])) {
                    $sqlInstructor = "UPDATE instructores 
                                      SET nombre_instructor = :nombre_instructor, 
                                          tipo_instructor = :tipo_instructor 
                                      WHERE id_instructor = :id_instructor";
                    $stmt = $conn->prepare($sqlInstructor);
                    $stmt->bindParam(':nombre_instructor', $data['nombre_instructor']);
                    $stmt->bindParam(':tipo_instructor', $data['tipo_instructor']);
                    $stmt->bindParam(':id_instructor', $rel['id_instructor'], PDO::PARAM_INT);
                    $stmt->execute();
                }
        
                // Actualizar competencia
                if (isset($data['descripcion'])) {
                    $sqlComp = "UPDATE competencias 
                                SET descripcion = :descripcion 
                                WHERE id_competencia = :id_competencia";
                    $stmt = $conn->prepare($sqlComp);
                    $stmt->bindParam(':descripcion', $data['descripcion']);
                    $stmt->bindParam(':id_competencia', $rel['id_competencia'], PDO::PARAM_INT);
                    $stmt->execute();
                }
        
                echo json_encode(['success' => 'Actualización completada correctamente']);
            } catch (PDOException $e) {
                echo json_encode(['error' => 'Error SQL: ' . $e->getMessage()]);
            } catch (Exception $e) {
                echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
            }
            break;
        

    default:
        echo json_encode(['error' => 'Acción no reconocida']);
        break;
        //quitar este comentario
}
?>
