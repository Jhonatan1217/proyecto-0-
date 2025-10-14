<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Trimestralizacion.php';

if (!isset($conn)) {
    echo json_encode(['status' => 'error', 'mensaje' => 'No se pudo establecer conexión con la base de datos']);
    exit;
}

$trimestral = new Trimestralizacion($conn);
$accion = $_GET['accion'] ?? null;

if (!$accion) {
    echo json_encode(['status' => 'error', 'mensaje' => 'Debe especificar la acción en la URL, por ejemplo: ?accion=listar']);
    exit;
}

switch ($accion) {

    // ============================================================
    // LISTAR POR ZONA
    // ============================================================
    case 'listar':
        $id_zona = $_GET['id_zona'] ?? null;
        if (!$id_zona) {
            echo json_encode([]);
            exit;
        }

        try {
            $stmt = $conn->prepare("
                SELECT h.id_horario,
                       h.dia,
                       h.hora_inicio,
                       h.hora_fin,
                       f.numero_ficha,
                       i.nombre_instructor,
                       i.tipo_instructor,
                       c.descripcion
                FROM horarios h
                LEFT JOIN fichas f ON h.id_ficha = f.id_ficha
                LEFT JOIN instructores i ON h.id_instructor = i.id_instructor
                LEFT JOIN competencias c ON h.id_competencia = c.id_competencia
                WHERE h.id_zona = :id_zona
                ORDER BY 
                  CASE h.dia
                    WHEN 'LUNES' THEN 1
                    WHEN 'MARTES' THEN 2
                    WHEN 'MIERCOLES' THEN 3
                    WHEN 'JUEVES' THEN 4
                    WHEN 'VIERNES' THEN 5
                    WHEN 'SABADO' THEN 6
                    ELSE 7
                  END,
                  h.hora_inicio
            ");
            $stmt->bindParam(':id_zona', $id_zona, PDO::PARAM_INT);
            $stmt->execute();
            $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($registros);

        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'mensaje' => 'Error al obtener registros: ' . $e->getMessage()]);
        }
        break;

    // ============================================================
    // OBTENER POR ID
    // ============================================================
    case 'obtener':
        $id = $_GET['id'] ?? null;
        echo json_encode($trimestral->obtenerPorId($id));
        break;

    // ============================================================
    // CREAR NUEVA TRIMESTRALIZACIÓN
    // ============================================================
    case 'crear':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'mensaje' => 'Método no permitido']);
            exit;
        }

        $data = [
            'dia'               => strtoupper($_POST['dia_semana'] ?? ''), // convertir a MAYÚSCULAS
            'hora_inicio'       => $_POST['hora_inicio'] ?? null,
            'hora_fin'          => $_POST['hora_fin'] ?? null,
            'id_zona'           => preg_replace('/\D/', '', $_POST['zona'] ?? ''), // extraer solo número
            'numero_ficha'      => $_POST['numero_ficha'] ?? null,
            'nivel_ficha'       => $_POST['nivel_ficha'] ?? null,
            'nombre_instructor' => $_POST['nombre_instructor'] ?? null,
            'tipo_instructor'   => $_POST['tipo_instructor'] ?? null,
            'descripcion'       => $_POST['descripcion'] ?? null,
        ];

        try {
            // ----------------------------
            // Validaciones básicas
            // ----------------------------
            if (empty($data['dia']) || empty($data['hora_inicio']) || empty($data['hora_fin'])) {
                throw new Exception("Faltan campos obligatorios del horario (día u horas).");
            }

            if (empty($data['numero_ficha'])) {
                throw new Exception("Debe ingresar el número de ficha.");
            }

            if (empty($data['id_zona'])) {
                throw new Exception("Debe seleccionar una zona válida.");
            }

            // Validar coherencia de horas
            $horaInicio = date("H:i:s", strtotime($data['hora_inicio']));
            $horaFin = date("H:i:s", strtotime($data['hora_fin']));

            if ($horaFin <= $horaInicio) {
                throw new Exception("La hora de finalización debe ser mayor que la hora de inicio.");
            }

            // =====================================================
            // VALIDAR QUE NO EXISTA CRUCE DE HORARIOS EN LA MISMA ZONA Y DÍA
            // =====================================================
            $consultaCruce = $conn->prepare("
                SELECT id_horario
                FROM horarios
                WHERE dia = :dia
                  AND id_zona = :id_zona
                  AND (
                        (hora_inicio < :hora_fin)
                    AND (hora_fin > :hora_inicio)
                  )
                LIMIT 1
            ");
            $consultaCruce->execute([
                ':dia' => $data['dia'],
                ':id_zona' => $data['id_zona'],
                ':hora_inicio' => $horaInicio,
                ':hora_fin' => $horaFin
            ]);

            if ($consultaCruce->fetch()) {
                throw new Exception("Este horario ya se encuentra ocupado.");
            }

            // ----------------------------
            // Buscar o crear ficha
            // ----------------------------
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

            // ----------------------------
            // Crear instructor
            // ----------------------------
            $stmt = $conn->prepare("INSERT INTO instructores (nombre_instructor, tipo_instructor)
                                    VALUES (:nombre, :tipo)");
            $stmt->bindParam(':nombre', $data['nombre_instructor']);
            $stmt->bindParam(':tipo', $data['tipo_instructor']);
            $stmt->execute();
            $data['id_instructor'] = (int)$conn->lastInsertId();

            // ----------------------------
            // Crear competencia
            // ----------------------------
            $stmt = $conn->prepare("INSERT INTO competencias (descripcion) VALUES (:descripcion)");
            $stmt->bindParam(':descripcion', $data['descripcion']);
            $stmt->execute();
            $data['id_competencia'] = (int)$conn->lastInsertId();

            // ----------------------------
            // Crear horario (asociado a zona específica)
            // ----------------------------
            $stmt = $conn->prepare("
                INSERT INTO horarios (dia, hora_inicio, hora_fin, id_zona, id_ficha, id_instructor, id_competencia)
                VALUES (:dia, :hora_inicio, :hora_fin, :id_zona, :id_ficha, :id_instructor, :id_competencia)
            ");
            $stmt->bindParam(':dia', $data['dia']);
            $stmt->bindParam(':hora_inicio', $horaInicio);
            $stmt->bindParam(':hora_fin', $horaFin);
            $stmt->bindParam(':id_zona', $data['id_zona'], PDO::PARAM_INT);
            $stmt->bindParam(':id_ficha', $data['id_ficha'], PDO::PARAM_INT);
            $stmt->bindParam(':id_instructor', $data['id_instructor'], PDO::PARAM_INT);
            $stmt->bindParam(':id_competencia', $data['id_competencia'], PDO::PARAM_INT);
            $stmt->execute();

            $id_horario = (int)$conn->lastInsertId();

            // ----------------------------
            // Crear registro trimestralización
            // ----------------------------
            $res = $trimestral->crear($id_horario);
            echo json_encode($res);

        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'mensaje' => $e->getMessage()]);
        }
        break;

    // ============================================================
    // ACTUALIZAR VARIOS REGISTROS DESDE JSON (USADO POR JS)
    // ============================================================
    case 'actualizar':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Método no permitido']);
            exit;
        }

        $input = file_get_contents('php://input');
        $registros = json_decode($input, true);

        if (!$registros || !is_array($registros)) {
            echo json_encode(['success' => false, 'error' => 'Formato de datos no válido']);
            exit;
        }

        try {
            $conn->beginTransaction();
            $actualizados = 0;

            foreach ($registros as $r) {
                if (empty($r['id_horario'])) continue;

                // Actualizar ficha
                if (!empty($r['numero_ficha'])) {
                    $stmtFicha = $conn->prepare("
                        UPDATE fichas f
                        INNER JOIN horarios h ON f.id_ficha = h.id_ficha
                        SET f.numero_ficha = :numero_ficha
                        WHERE h.id_horario = :id_horario
                    ");
                    $stmtFicha->execute([
                        ':numero_ficha' => $r['numero_ficha'],
                        ':id_horario' => $r['id_horario']
                    ]);
                }

                // Actualizar instructor
                // ✅ Actualizar solo el nombre del instructor (NO el tipo)
                if (!empty($r['nombre_instructor'])) {
                    $stmtInst = $conn->prepare("
                        UPDATE instructores i
                        INNER JOIN horarios h ON i.id_instructor = h.id_instructor
                        SET i.nombre_instructor = :nombre_instructor
                        WHERE h.id_horario = :id_horario
                    ");
                    $stmtInst->execute([
                        ':nombre_instructor' => $r['nombre_instructor'],
                        ':id_horario' => $r['id_horario']
                    ]);
                }


                // Actualizar competencia
                if (!empty($r['descripcion'])) {
                    $stmtComp = $conn->prepare("
                        UPDATE competencias c
                        INNER JOIN horarios h ON c.id_competencia = h.id_competencia
                        SET c.descripcion = :descripcion
                        WHERE h.id_horario = :id_horario
                    ");
                    $stmtComp->execute([
                        ':descripcion' => $r['descripcion'],
                        ':id_horario' => $r['id_horario']
                    ]); 
                }

                $actualizados++;
            }

            $conn->commit();
            echo json_encode(['success' => true, 'message' => "$actualizados registros actualizados correctamente."]);
        } catch (PDOException $e) { 
            $conn->rollBack();
            echo json_encode(['success' => false, 'error' => 'Error SQL: ' . $e->getMessage()]);
        }
        break;

    // ============================================================
    // ELIMINAR TRIMESTRALIZACIÓN POR ZONA
    // ============================================================
    case 'eliminar':
        $id_zona = $_GET['id_zona'] ?? null;
        if (!$id_zona) {
            echo json_encode(['status' => 'error', 'mensaje' => 'Debe indicar la zona a eliminar']);
            exit;
        }

        $res = $trimestral->eliminarPorZona($id_zona);
        echo json_encode($res);
        break;

    default:
        echo json_encode(['status' => 'error', 'mensaje' => 'Acción no reconocida']);
        break;
}
?>
