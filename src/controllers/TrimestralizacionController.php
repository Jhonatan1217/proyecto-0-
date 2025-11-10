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

/**
 * Helper: intentar resolver id_area a partir de:
 *  - el POST/GET 'area' si viene
 *  - o la tabla zonas buscando por id_zona. Si hay varias filas para id_zona y no viene 'area', devolvemos null y el
 *    flujo llamante deberá exigir el envío del 'area'.
 */
function resolveAreaForZona(PDO $conn, $id_zona, $provided_area = null) {
    $id_zona = intval($id_zona);
    if ($id_zona <= 0) return null;

    if ($provided_area !== null && $provided_area !== '') {
        return intval($provided_area);
    }

    // Buscar cuántas filas hay con ese id_zona
    $s = $conn->prepare("SELECT id_area FROM zonas WHERE id_zona = :id_zona");
    $s->execute([':id_zona' => $id_zona]);
    $rows = $s->fetchAll(PDO::FETCH_ASSOC);

    if (count($rows) === 0) {
        return null; // zona inexistente
    } elseif (count($rows) === 1) {
        return intval($rows[0]['id_area']); // zona única -> devolvemos su área
    } else {
        // Ambigüedad: hay varias zonas con el mismo id_zona (distintas áreas)
        return null;
    }
}

switch ($accion) {

    // ============================================================
    // LISTAR POR ZONA (AHORA EXIGE id_area RESOLUBLE)
    // ============================================================
    case 'listar':
        $id_zona = $_GET['id_zona'] ?? null;
        $id_area_supplied = $_GET['id_area'] ?? null;

        if (!$id_zona) {
            echo json_encode([]);
            exit;
        }

        // Intentamos resolver id_area: si hay ambigüedad, devolvemos error para que el frontend envíe id_area.
        $resolved_area = resolveAreaForZona($conn, $id_zona, $id_area_supplied);

        if ($resolved_area === null) {
            // Si el frontend suministró id_area y no existe la pareja, devolvemos vacío/ error
            if (!empty($id_area_supplied)) {
                echo json_encode([]);
                exit;
            }
            // Si no se proporcionó, aclaramos que es necesario
            echo json_encode(['status' => 'error', 'mensaje' => 'Ambigüedad en zona: debe proporcionar id_area junto con id_zona']);
            exit;
        }

        try {
            $stmt = $conn->prepare("
                SELECT 
                    h.id_horario,
                    h.dia,
                    h.hora_inicio,
                    h.hora_fin,
                    h.id_zona,
                    h.id_area,
                    h.numero_trimestre,
                    h.estado,
                    f.numero_ficha,
                    f.nivel_ficha,
                    i.nombre_instructor,
                    i.tipo_instructor,
                    c.id_competencia,
                    c.nombre_competencia,
                    c.descripcion
                FROM horarios h
                LEFT JOIN fichas f ON h.id_ficha = f.id_ficha
                LEFT JOIN instructores i ON h.id_instructor = i.id_instructor
                LEFT JOIN competencias c ON h.id_competencia = c.id_competencia
                WHERE h.id_zona = :id_zona
                  AND h.id_area = :id_area
                  AND h.estado = 1
                ORDER BY FIELD(UPPER(h.dia), 'LUNES','MARTES','MIERCOLES','JUEVES','VIERNES','SABADO'), h.hora_inicio
            ");
            $stmt->execute([
                ':id_zona' => intval($id_zona),
                ':id_area' => intval($resolved_area)
            ]);
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

        // Recoger y normalizar datos
        $dia               = strtoupper(trim($_POST['dia_semana'] ?? ''));
        $hora_inicio_raw   = trim($_POST['hora_inicio'] ?? '');
        $hora_fin_raw      = trim($_POST['hora_fin'] ?? '');
        $id_zona_raw       = $_POST['zona'] ?? null;
        $id_area_post      = $_POST['area'] ?? null; // <- ahora puede venir desde el frontend
        $numero_ficha      = trim($_POST['numero_ficha'] ?? '');
        $nivel_ficha       = trim($_POST['nivel_ficha'] ?? '');
        $nombre_instructor = trim($_POST['nombre_instructor'] ?? '');
        $tipo_instructor   = trim($_POST['tipo_instructor'] ?? '');
        $descripcion       = trim($_POST['descripcion'] ?? '');

        $id_zona = intval($id_zona_raw);

        // Validaciones básicas
        if (empty($dia) || empty($hora_inicio_raw) || empty($hora_fin_raw)) {
            echo json_encode(['status' => 'error', 'mensaje' => 'Día, hora inicio y hora fin son obligatorios.']);
            exit;
        }
        if ($id_zona <= 0) {
            echo json_encode(['status' => 'error', 'mensaje' => 'Debe seleccionar una zona válida.']);
            exit;
        }
        if (empty($numero_ficha)) {
            echo json_encode(['status' => 'error', 'mensaje' => 'Número de ficha obligatorio.']);
            exit;
        }

        $horaInicio = date("H:i:s", strtotime($hora_inicio_raw));
        $horaFin    = date("H:i:s", strtotime($hora_fin_raw));

        if ($horaFin <= $horaInicio) {
            echo json_encode(['status' => 'error', 'mensaje' => 'La hora fin debe ser mayor que la hora inicio.']);
            exit;
        }

        try {
            $conn->beginTransaction();

            // Resolver/validar id_area usando helper
            $resolved_area = resolveAreaForZona($conn, $id_zona, $id_area_post);

            if ($resolved_area === null) {
                // Si hubo ambigüedad (varias zonas con mismo id_zona) o zona inexistente -> requerir id_area explícita
                $conn->rollBack();
                echo json_encode(['status' => 'error', 'mensaje' => 'Ambigüedad en zona: envía también id_area (ejemplo: area=2).']);
                exit;
            }
            $id_area = intval($resolved_area);

            // Usar numero_trimestre enviado por POST si existe, si no obtener el activo
            if (!empty($_POST['numero_trimestre'])) {
                $numero_trimestre = intval($_POST['numero_trimestre']);
            } else {
                $stmtTrim = $conn->prepare("SELECT numero_trimestre FROM trimestre WHERE estado = 1 LIMIT 1");
                $stmtTrim->execute();
                $numero_trimestre = $stmtTrim->fetchColumn();
                $numero_trimestre = $numero_trimestre !== false ? intval($numero_trimestre) : null;
            }

            // 1) Verificar cruce con horarios ACTIVOS en la misma zona/área/día
            $stmtCruce = $conn->prepare("
                SELECT COUNT(*) AS cnt FROM horarios
                WHERE id_zona = :id_zona
                  AND id_area = :id_area
                  AND dia = :dia
                  AND estado = 1
                  AND NOT (hora_fin <= :hora_inicio OR hora_inicio >= :hora_fin)
            ");
            $stmtCruce->execute([
                ':id_zona' => $id_zona,
                ':id_area' => $id_area,
                ':dia' => $dia,
                ':hora_inicio' => $horaInicio,
                ':hora_fin' => $horaFin
            ]);
            if ($stmtCruce->fetchColumn() > 0) {
                $conn->rollBack();
                echo json_encode(['status' => 'error', 'mensaje' => 'Ya existe un horario activo que se cruza con el rango seleccionado en esta zona y área.']);
                exit;
            }

            // 2) Buscar horario exacto (mismo zona+area, día, hora_inicio, hora_fin)
            $stmtExist = $conn->prepare("
                SELECT * FROM horarios
                WHERE id_zona = :id_zona
                  AND id_area = :id_area
                  AND dia = :dia
                  AND hora_inicio = :hora_inicio
                  AND hora_fin = :hora_fin
                LIMIT 1
            ");
            $stmtExist->execute([
                ':id_zona' => $id_zona,
                ':id_area' => $id_area,
                ':dia' => $dia,
                ':hora_inicio' => $horaInicio,
                ':hora_fin' => $horaFin
            ]);
            $horarioExist = $stmtExist->fetch(PDO::FETCH_ASSOC);

            // Funciones auxiliares para obtener/crear ids
            $getOrCreateFicha = function($numero, $nivel) use ($conn) {
                $s = $conn->prepare("SELECT id_ficha FROM fichas WHERE numero_ficha = :num LIMIT 1");
                $s->execute([':num' => $numero]);
                $r = $s->fetch(PDO::FETCH_ASSOC);
                if ($r) return $r['id_ficha'];
                $ins = $conn->prepare("INSERT INTO fichas (numero_ficha, nivel_ficha) VALUES (:num, :nivel)");
                $ins->execute([':num' => $numero, ':nivel' => $nivel]);
                return $conn->lastInsertId();
            };
            $getOrCreateInstructor = function($nombre) use ($conn) {
                if (empty($nombre)) return null;
                $s = $conn->prepare("SELECT id_instructor, tipo_instructor FROM instructores WHERE nombre_instructor = :nom LIMIT 1");
                $s->execute([':nom' => $nombre]);
                $r = $s->fetch(PDO::FETCH_ASSOC);
                if ($r) return $r['id_instructor'];
                $ins = $conn->prepare("INSERT INTO instructores (nombre_instructor, tipo_instructor) VALUES (:nom, 'TECNICO')");
                $ins->execute([':nom' => $nombre]);
                return $conn->lastInsertId();
            };
            // Obtener o crear competencia; ahora acepta id_programa opcional para guardarlo cuando se crea.
            $getOrCreateCompetencia = function($desc, $id_programa = null) use ($conn) {
                if (empty($desc)) return null;
                $s = $conn->prepare("SELECT id_competencia FROM competencias WHERE descripcion = :desc LIMIT 1");
                $s->execute([':desc' => $desc]);
                $r = $s->fetch(PDO::FETCH_ASSOC);
                if ($r) return $r['id_competencia'];

                // Insertar incluyendo id_programa (puede ser NULL)
                $ins = $conn->prepare("INSERT INTO competencias (descripcion, id_programa) VALUES (:desc, :id_programa)");
                $ins->execute([':desc' => $desc, ':id_programa' => $id_programa]);
                return $conn->lastInsertId();
            };

            // Obtener/crear ids relacionados
            $id_ficha = $getOrCreateFicha($numero_ficha, $nivel_ficha);
            $id_instructor = $nombre_instructor !== '' ? $getOrCreateInstructor($nombre_instructor) : null;

            // Leer id_programa e id_rae enviados por el formulario (si vienen)
            $id_programa_post = isset($_POST['id_programa']) && $_POST['id_programa'] !== '' ? intval($_POST['id_programa']) : null;
            $id_rae_post = isset($_POST['id_rae']) && $_POST['id_rae'] !== '' ? intval($_POST['id_rae']) : null;

            // Priorizar id_competencia enviado por el formulario. Si no viene, usar descripcion para buscar/crear
            // pasando id_programa como información adicional al crear la competencia.
            $id_comp_post = isset($_POST['id_competencia']) ? intval($_POST['id_competencia']) : 0;
            if ($id_comp_post > 0) {
                $id_competencia = $id_comp_post;
            } else {
                $id_competencia = $descripcion !== '' ? $getOrCreateCompetencia($descripcion, $id_programa_post) : null;
            }

            if ($horarioExist) {
                // Si existe y está activo -> rechazo
                if (intval($horarioExist['estado']) === 1) {
                    $conn->rollBack();
                    echo json_encode(['status' => 'error', 'mensaje' => 'Ya existe un horario idéntico activo en esta zona y área.']);
                    exit;
                }

                // Reactivar horario inactivo y actualizar relaciones
                $upd = $conn->prepare("                    
                    UPDATE horarios
                    SET estado = 1,
                        id_zona = :id_zona,
                        id_area = :id_area,
                        numero_trimestre = :numero_trimestre,
                        id_ficha = :id_ficha,
                        id_instructor = :id_instructor,
                        id_competencia = :id_competencia,
                        id_programa = :id_programa,
                        id_rae = :id_rae
                    WHERE id_horario = :id_horario
                ");
                $upd->execute([
                    ':id_zona' => $id_zona,
                    ':id_area' => $id_area,
                    ':numero_trimestre' => $numero_trimestre,
                    ':id_ficha' => $id_ficha,
                    ':id_instructor' => $id_instructor,
                    ':id_competencia' => $id_competencia,
                    ':id_programa' => $id_programa_post,
                    ':id_rae' => $id_rae_post,
                    ':id_horario' => $horarioExist['id_horario']
                ]);

                // Asegurar existencia en trimestralizacion
                $sChk = $conn->prepare("SELECT id_trimestral FROM trimestralizacion WHERE id_horario = :id_horario LIMIT 1");
                $sChk->execute([':id_horario' => $horarioExist['id_horario']]);
                if (!$sChk->fetch()) {
                    $insT = $conn->prepare("INSERT INTO trimestralizacion (id_horario) VALUES (:id_horario)");
                    $insT->execute([':id_horario' => $horarioExist['id_horario']]);
                }

                $conn->commit();
                echo json_encode(['status' => 'success', 'mensaje' => 'Horario reactivado correctamente.', 'id_horario' => $horarioExist['id_horario']]);
                exit;
            }

            // No existe: crear horario nuevo
            $insHorario = $conn->prepare("                
                INSERT INTO horarios (id_zona, id_area, dia, hora_inicio, hora_fin, id_ficha, id_instructor, id_competencia, numero_trimestre, estado, id_programa, id_rae)
                VALUES (:id_zona, :id_area, :dia, :hora_inicio, :hora_fin, :id_ficha, :id_instructor, :id_competencia, :numero_trimestre, 1, :id_programa, :id_rae)
            ");
            $insHorario->execute([
                ':id_zona' => $id_zona,
                ':id_area' => $id_area,
                ':dia' => $dia,
                ':hora_inicio' => $horaInicio,
                ':hora_fin' => $horaFin,
                ':id_ficha' => $id_ficha,
                ':id_instructor' => $id_instructor,
                ':id_competencia' => $id_competencia,
                ':numero_trimestre' => $numero_trimestre,
                ':id_programa' => $id_programa_post,
                ':id_rae' => $id_rae_post
            ]);
            $newHorarioId = $conn->lastInsertId();

            // Crear entrada en trimestralizacion
            $insT = $conn->prepare("INSERT INTO trimestralizacion (id_horario) VALUES (:id_horario)");
            $insT->execute([':id_horario' => $newHorarioId]);

            $conn->commit();
            echo json_encode(['status' => 'success', 'mensaje' => 'Trimestralización creada correctamente.', 'id_horario' => $newHorarioId]);
            exit;

        } catch (PDOException $e) {
            if ($conn->inTransaction()) $conn->rollBack();
            echo json_encode(['status' => 'error', 'mensaje' => 'Error en creación: ' . $e->getMessage()]);
            exit;
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


                // Actualizar ficha (número y nivel)
                if (!empty($r['numero_ficha']) || !empty($r['nivel_ficha'])) {
                    $stmtFicha = $conn->prepare("
                        UPDATE fichas f
                        INNER JOIN horarios h ON f.id_ficha = h.id_ficha
                        SET 
                            f.numero_ficha = COALESCE(:numero_ficha, f.numero_ficha),
                            f.nivel_ficha = COALESCE(:nivel_ficha, f.nivel_ficha)
                        WHERE h.id_horario = :id_horario
                    ");
                    $stmtFicha->execute([
                        ':numero_ficha' => $r['numero_ficha'] ?? null,
                        ':nivel_ficha' => $r['nivel_ficha'] ?? null,
                        ':id_horario' => $r['id_horario']
                    ]);
                }

                // Actualizar instructor (ID)
                if (!empty($r['id_instructor'])) {
                    $stmtInst = $conn->prepare("
                        UPDATE horarios
                        SET id_instructor = :id_instructor
                        WHERE id_horario = :id_horario
                    ");
                    $stmtInst->execute([
                        ':id_instructor' => $r['id_instructor'],
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
    // ELIMINAR TRIMESTRALIZACIÓN POR ZONA+AREA
    // ============================================================
    case 'eliminar':
        $id_zona = $_GET['id_zona'] ?? null;
        $id_area_supplied = $_GET['id_area'] ?? null;

        if (!$id_zona) {
            echo json_encode(['status' => 'error', 'mensaje' => 'Debe indicar la zona a eliminar']);
            exit;
        }

        // Resolver area
        $resolved_area = resolveAreaForZona($conn, $id_zona, $id_area_supplied);
        if ($resolved_area === null) {
            echo json_encode(['status' => 'error', 'mensaje' => 'Ambigüedad en zona: envíe id_area para eliminar.']);
            exit;
        }
        $id_area = intval($resolved_area);

        try {
            $stmtDel = $conn->prepare("UPDATE horarios SET estado = 0 WHERE id_zona = :id_zona AND id_area = :id_area");
            $stmtDel->execute([':id_zona' => $id_zona, ':id_area' => $id_area]);

            echo json_encode(['status' => 'success', 'mensaje' => 'Trimestralización eliminada correctamente.']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'mensaje' => 'Error al eliminar: ' . $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'mensaje' => 'Acción no reconocida']);
        break;
}
?>
