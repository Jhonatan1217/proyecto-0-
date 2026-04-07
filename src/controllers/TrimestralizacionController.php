<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Trimestralizacion.php';

if (!isset($conn)) {
    echo json_encode(['status' => 'error', 'mensaje' => 'No se pudo establecer conexión con la base de datos']);
    exit;
}

$trimestral = new Trimestralizacion($conn);
$accion = $_GET['accion'] ?? $_POST['accion'] ?? null;

if (!$accion) {
    echo json_encode(['status' => 'error', 'mensaje' => 'Debe especificar la acción en la URL (?accion=...)']);
    exit;
}

/**
 * Helper para resolver id_area a partir de id_zona cuando no viene en la petición.
 */
function resolveAreaForZona(PDO $conn, $id_zona, $provided_area = null) {
    $id_zona = intval($id_zona);
    if ($id_zona <= 0) return null;

    if ($provided_area !== null && $provided_area !== '') {
        return intval($provided_area);
    }

    $s = $conn->prepare("SELECT id_area FROM zonas WHERE id_zona = :id_zona");
    $s->execute([':id_zona' => $id_zona]);
    $rows = $s->fetchAll(PDO::FETCH_ASSOC);

    if (count($rows) === 1) {
        return intval($rows[0]['id_area']);
    }

    return null; // inexistente o ambigüedad
}

function resolveInstructorId(PDO $conn, $instructorInput) {
    $raw = trim((string)($instructorInput ?? ''));
    if ($raw === '') return null;

    if (ctype_digit($raw)) {
        return intval($raw);
    }

    try {
        $s = $conn->prepare("SELECT id_instructor FROM instructores WHERE nombre_instructor = :nom LIMIT 1");
        $s->execute([':nom' => $raw]);
        $r = $s->fetch(PDO::FETCH_ASSOC);
        if ($r && isset($r['id_instructor'])) {
            return intval($r['id_instructor']);
        }
    } catch (Throwable $e) {
    }

    try {
        $s = $conn->prepare("SELECT id_usuario FROM usuarios WHERE cargo = 'INSTRUCTOR' AND nombre_completo = :nom AND COALESCE(es_sistema, 0) = 0 LIMIT 1");
        $s->execute([':nom' => $raw]);
        $r = $s->fetch(PDO::FETCH_ASSOC);
        if ($r && isset($r['id_usuario'])) {
            return intval($r['id_usuario']);
        }
    } catch (Throwable $e) {
    }

    return null;
}

function hasColumn(PDO $conn, $table, $column) {
    static $cache = [];
    $key = strtolower($table . '.' . $column);
    if (array_key_exists($key, $cache)) {
        return $cache[$key];
    }

    try {
        $stmt = $conn->prepare("SHOW COLUMNS FROM `{$table}` LIKE :col");
        $stmt->execute([':col' => $column]);
        $cache[$key] = (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        $cache[$key] = false;
    }

    return $cache[$key];
}

function hasTable(PDO $conn, $table) {
    static $cache = [];
    $key = strtolower($table);
    if (array_key_exists($key, $cache)) {
        return $cache[$key];
    }

    try {
        $stmt = $conn->prepare("SHOW TABLES LIKE :tbl");
        $stmt->execute([':tbl' => $table]);
        $cache[$key] = (bool)$stmt->fetch(PDO::FETCH_NUM);
    } catch (Throwable $e) {
        $cache[$key] = false;
    }

    return $cache[$key];
}

function resolveTableName(PDO $conn, array $candidates) {
    foreach ($candidates as $tbl) {
        if (hasTable($conn, $tbl)) {
            return $tbl;
        }
    }
    return null;
}

function normalizeContractLabel($raw) {
    $value = strtoupper(trim((string)($raw ?? '')));
    if ($value === 'PLANTA') return 'Planta';
    if ($value === 'CONTRATISTA') return 'Contratista';
    if ($value === '') return 'Contratista';
    return ucfirst(strtolower($value));
}

function getInstructorMaxHours($contractLabel) {
    return strtoupper(trim((string)$contractLabel)) === 'PLANTA' ? 32 : 40;
}

function normalizeNivelFichaLabel($raw) {
    $value = strtoupper(trim((string)($raw ?? '')));
    if ($value === 'TECNICO' || $value === 'TÉCNICO') return 'Técnico';
    if ($value === 'TECNOLOGO' || $value === 'TECNÓLOGO') return 'Tecnólogo';
    if ($value === '') return 'Sin nivel';
    return ucfirst(strtolower($value));
}

function buildPlaceholders(array $values, string $prefix) {
    $params = [];
    $placeholders = [];
    foreach (array_values($values) as $index => $value) {
        $key = ':' . $prefix . $index;
        $placeholders[] = $key;
        $params[$key] = $value;
    }
    return [$placeholders, $params];
}

function fetchInstructorHourSummaries(PDO $conn, string $tablaHorario, ?array $ids = null) {
    $rows = [];
    $params = [];

    if (hasTable($conn, 'instructores')) {
        $sql = "
            SELECT 
                i.id_instructor AS id_instructor,
                i.nombre_instructor AS nombre_instructor,
                COALESCE(NULLIF(i.tipo_contrato, ''), NULLIF(i.tipo_instructor, ''), 'CONTRATISTA') AS tipo_contrato
            FROM instructores i
            WHERE COALESCE(i.estado, 1) = 1
        ";

        if ($ids && count($ids) > 0) {
            [$placeholders, $paramsIds] = buildPlaceholders($ids, 'inst');
            $sql .= " AND i.id_instructor IN (" . implode(',', $placeholders) . ")";
            $params += $paramsIds;
        }

        $sql .= " ORDER BY i.nombre_instructor ASC";
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $sql = "
            SELECT 
                u.id_usuario AS id_instructor,
                u.nombre_completo AS nombre_instructor,
                COALESCE(NULLIF(u.tipo_contrato, ''), NULLIF(u.tipo_instructor, ''), 'CONTRATISTA') AS tipo_contrato
            FROM usuarios u
            WHERE UPPER(COALESCE(u.cargo, '')) = 'INSTRUCTOR'
              AND COALESCE(u.estado, 1) = 1
              AND COALESCE(u.es_sistema, 0) = 0
        ";

        if ($ids && count($ids) > 0) {
            [$placeholders, $paramsIds] = buildPlaceholders($ids, 'usr');
            $sql .= " AND u.id_usuario IN (" . implode(',', $placeholders) . ")";
            $params += $paramsIds;
        }

        $sql .= " ORDER BY u.nombre_completo ASC";
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    $sqlHours = "
        SELECT id_instructor, ROUND(SUM(TIME_TO_SEC(TIMEDIFF(hora_fin, hora_inicio)) / 3600), 2) AS horas_actuales
        FROM {$tablaHorario}
        WHERE estado = 1
          AND id_instructor IS NOT NULL
    ";
    $paramsHours = [];

    if ($ids && count($ids) > 0) {
        [$placeholders, $paramsIds] = buildPlaceholders($ids, 'idh');
        $sqlHours .= " AND id_instructor IN (" . implode(',', $placeholders) . ")";
        $paramsHours += $paramsIds;
    }

    $sqlHours .= " GROUP BY id_instructor";
    $stmtHours = $conn->prepare($sqlHours);
    $stmtHours->execute($paramsHours);
    $hourMap = [];
    foreach ($stmtHours->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $hourMap[(string)$row['id_instructor']] = (float)$row['horas_actuales'];
    }

    $result = [];
    foreach ($rows as $row) {
        $contract = normalizeContractLabel($row['tipo_contrato'] ?? 'CONTRATISTA');
        $maxHours = getInstructorMaxHours($contract);
        $actual = $hourMap[(string)$row['id_instructor']] ?? 0.0;
        $result[] = [
            'id_instructor' => (int)$row['id_instructor'],
            'nombre_instructor' => $row['nombre_instructor'] ?? 'Sin nombre',
            'tipo_contrato' => $contract,
            'horas_actuales' => round($actual, 2),
            'horas_maximas' => $maxHours,
            'excedente' => round($maxHours - $actual, 2),
        ];
    }

    return $result;
}

function fetchGroupHourSummaries(PDO $conn, string $tablaHorario, ?array $ids = null) {
    $params = [];
    $nivelFichaExpr = hasColumn($conn, 'fichas', 'nivel_ficha') ? "NULLIF(TRIM(f.nivel_ficha), '')" : "NULL";
    $joinPrograma = hasColumn($conn, 'fichas', 'id_programa') ? 'LEFT JOIN programas p ON p.id_programa = f.id_programa' : '';
    $nivelProgramaExpr = $joinPrograma ? "NULLIF(TRIM(p.nivel_formacion), '')" : "NULL";
    $nivelSelect = "COALESCE({$nivelFichaExpr}, {$nivelProgramaExpr}, '') AS nivel_ficha";
    $sql = "
        SELECT f.id_ficha, f.numero_ficha, {$nivelSelect}
        FROM fichas f
        {$joinPrograma}
        WHERE f.numero_ficha IS NOT NULL
          AND f.numero_ficha <> ''
    ";

    if ($ids && count($ids) > 0) {
        [$placeholders, $paramsIds] = buildPlaceholders($ids, 'grp');
        $sql .= " AND f.id_ficha IN (" . implode(',', $placeholders) . ")";
        $params += $paramsIds;
    }

    $sql .= " ORDER BY f.numero_ficha ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sqlHours = "
        SELECT id_ficha, ROUND(SUM(TIME_TO_SEC(TIMEDIFF(hora_fin, hora_inicio)) / 3600), 2) AS horas_actuales
        FROM {$tablaHorario}
        WHERE estado = 1
          AND id_ficha IS NOT NULL
    ";
    $paramsHours = [];

    if ($ids && count($ids) > 0) {
        [$placeholders, $paramsIds] = buildPlaceholders($ids, 'grph');
        $sqlHours .= " AND id_ficha IN (" . implode(',', $placeholders) . ")";
        $paramsHours += $paramsIds;
    }

    $sqlHours .= " GROUP BY id_ficha";
    $stmtHours = $conn->prepare($sqlHours);
    $stmtHours->execute($paramsHours);
    $hourMap = [];
    foreach ($stmtHours->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $hourMap[(string)$row['id_ficha']] = (float)$row['horas_actuales'];
    }

    $result = [];
    foreach ($rows as $row) {
        $actual = $hourMap[(string)$row['id_ficha']] ?? 0.0;
        $maxHours = 30;
        $result[] = [
            'id_ficha' => (int)$row['id_ficha'],
            'id_grupo' => (string)$row['numero_ficha'],
            'nivel_grupo' => normalizeNivelFichaLabel($row['nivel_ficha'] ?? ''),
            'horas_actuales' => round($actual, 2),
            'horas_maximas' => $maxHours,
            'excedente' => round($maxHours - $actual, 2),
        ];
    }

    return $result;
}

function extractExceededWarnings(array $instructores, array $grupos) {
    $warningInstructores = array_values(array_filter($instructores, function ($item) {
        return isset($item['horas_actuales'], $item['horas_maximas']) && (float)$item['horas_actuales'] > (float)$item['horas_maximas'];
    }));

    $warningGrupos = array_values(array_filter($grupos, function ($item) {
        return isset($item['horas_actuales'], $item['horas_maximas']) && (float)$item['horas_actuales'] > (float)$item['horas_maximas'];
    }));

    return [
        'instructores' => $warningInstructores,
        'grupos' => $warningGrupos,
    ];
}

$tablaHorario = resolveTableName($conn, ['horarios', 'horario']);
if (!$tablaHorario) {
    echo json_encode(['status' => 'error', 'mensaje' => 'No existe la tabla de horarios (horario/horarios) en la base de datos']);
    exit;
}

switch ($accion) {

    case 'resumenHoras':
        try {
            $instructores = fetchInstructorHourSummaries($conn, $tablaHorario);
            $grupos = fetchGroupHourSummaries($conn, $tablaHorario);

            echo json_encode([
                'status' => 'success',
                'data' => [
                    'instructores' => $instructores,
                    'grupos' => $grupos,
                ]
            ]);
            exit;
        } catch (Throwable $e) {
            echo json_encode(['status' => 'error', 'mensaje' => 'Error al calcular resumen de horas: ' . $e->getMessage()]);
            exit;
        }


// ============================================================
// LISTAR POR GRUPO (Virtual / Mixto)
// ============================================================
case 'listarPorGrupo':

    $numero_ficha = trim((string)($_GET['numero_ficha'] ?? ''));

    if (!$numero_ficha) {
        echo json_encode(['status' => 'error', 'mensaje' => 'Falta numero_ficha']);
        exit;
    }

    try {
        $joinInstructores = "LEFT JOIN instructores i ON h.id_instructor = i.id_instructor";
        $selectInstructores = "i.id_instructor, i.nombre_instructor, i.tipo_instructor";

        if (!hasTable($conn, 'instructores')) {
            $joinInstructores = "LEFT JOIN usuarios i ON h.id_instructor = i.id_usuario";
            $selectInstructores = "i.id_usuario AS id_instructor, i.nombre_completo AS nombre_instructor, i.tipo_instructor";
        }

        $sql = "
            SELECT 
                h.id_horario,
                h.modalidad,
                h.dia,
                h.hora_inicio,
                h.hora_fin,
                h.id_zona,
                h.id_area,
                h.numero_trimestre,
                h.id_programa,
                h.id_competencia,
                f.numero_ficha,
                p.nombre_programa,
                {$selectInstructores},
                c.nombre_competencia,
                r.id_rae,
                r.descripcion AS descripcion_rae,
                h.estado
            FROM {$tablaHorario} h
            LEFT JOIN fichas f ON h.id_ficha = f.id_ficha
            LEFT JOIN programas p ON h.id_programa = p.id_programa
            {$joinInstructores}
            LEFT JOIN competencias c ON h.id_competencia = c.id_competencia
            LEFT JOIN raes r ON FIND_IN_SET(r.id_rae, h.id_rae)
                        WHERE f.numero_ficha LIKE :numero_ficha
              AND h.estado = 1
              AND h.modalidad IN ('VIRTUAL','MIXTO')
            ORDER BY 
                FIELD(UPPER(h.dia),'LUNES','MARTES','MIERCOLES','JUEVES','VIERNES','SABADO'),
                h.hora_inicio
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':numero_ficha', $numero_ficha . '%');
        $stmt->execute();

        $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'success',
            'data' => $registros ?: []
        ]);
        exit;

    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'mensaje' => 'Error al listar por grupo: ' . $e->getMessage()]);
        exit;
    }

    // ============================================================
    // LISTAR POR ZONA (+ opcional AREA)
    // ============================================================
    case 'listar':
        $id_zona = $_GET['id_zona'] ?? null;
        $id_area_supplied = $_GET['id_area'] ?? null;
        $modalidad = $_GET['modalidad'] ?? 'PRESENCIAL';

        if (!$id_zona) {
            echo json_encode(['status' => 'error', 'mensaje' => 'Falta id_zona']);
            exit;
        }

        $resolved_area = resolveAreaForZona($conn, $id_zona, $id_area_supplied);
        if ($resolved_area === null && empty($id_area_supplied)) {
            echo json_encode(['status' => 'error', 'mensaje' => 'Ambigüedad en zona: envía también id_area']);
            exit;
        }

        try {
            $hasNivelFicha = hasColumn($conn, 'fichas', 'nivel_ficha');
            // Nivel mostrado: primero ficha; si vacío, el del programa (como en gestión de grupos).
            $nivelFichaSelect = $hasNivelFicha
                ? "COALESCE(NULLIF(TRIM(f.nivel_ficha), ''), NULLIF(TRIM(p.nivel_formacion), '')) AS nivel_ficha"
                : "NULLIF(TRIM(p.nivel_formacion), '') AS nivel_ficha";
            $joinInstructores = "LEFT JOIN instructores i ON h.id_instructor = i.id_instructor";
            $selectInstructores = "i.id_instructor, i.nombre_instructor, i.tipo_instructor";

            if (!hasTable($conn, 'instructores')) {
                $joinInstructores = "LEFT JOIN usuarios i ON h.id_instructor = i.id_usuario";
                $selectInstructores = "i.id_usuario AS id_instructor, i.nombre_completo AS nombre_instructor, i.tipo_instructor";
            }

            $sql = "
                SELECT 
                    h.id_horario,
                    h.dia,
                    h.hora_inicio,
                    h.hora_fin,
                    h.id_zona,
                    h.id_area,
                    h.numero_trimestre,
                    h.estado,
                    h.id_rae AS raes_horario,
                    f.numero_ficha,
                    {$nivelFichaSelect},
                    p.id_programa,
                    p.nombre_programa,
                    {$selectInstructores},
                    c.id_competencia,
                    c.nombre_competencia,
                    r.id_rae,
                                        r.descripcion AS descripcion_rae
                FROM {$tablaHorario} h
                LEFT JOIN fichas f ON h.id_ficha = f.id_ficha
                LEFT JOIN programas p ON h.id_programa = p.id_programa
                {$joinInstructores}
                LEFT JOIN competencias c ON h.id_competencia = c.id_competencia
                LEFT JOIN raes r ON FIND_IN_SET(r.id_rae, h.id_rae)
                WHERE h.id_zona = :id_zona
                                    AND UPPER(h.modalidad) = :modalidad
            ";

            if (!empty($id_area_supplied)) {
                $sql .= " AND h.id_area = :id_area";
            }

            $sql .= "
                ORDER BY 
                    FIELD(UPPER(h.dia), 'LUNES','MARTES','MIERCOLES','JUEVES','VIERNES','SABADO'),
                    h.hora_inicio
            ";

            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':id_zona', intval($id_zona), PDO::PARAM_INT);
            $stmt->bindValue(':modalidad', strtoupper($modalidad));

            if (!empty($id_area_supplied)) {
                $stmt->bindValue(':id_area', intval($id_area_supplied), PDO::PARAM_INT);
            }

            $stmt->execute();
            $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'status' => 'success',
                'data' => $registros ?: []
            ]);
            exit;

        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'mensaje' => 'Error al listar: ' . $e->getMessage()]);
            exit;
        }

    // ============================================================
    // OBTENER POR ID
    // ============================================================
    case 'obtener':
        $id = $_GET['id'] ?? null;
        $data = $trimestral->obtenerPorId($id);
        echo json_encode(['status' => 'success', 'data' => $data ? [$data] : []]);
        exit;

    // ============================================================
    // CREAR NUEVA TRIMESTRALIZACIÓN
    // ============================================================
    case 'crear':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'mensaje' => 'Método no permitido']);
            exit;
        }

        $modalidad_raw     = trim($_POST['modalidad'] ?? 'PRESENCIAL');
        $modalidad         = strtoupper($modalidad_raw);
        if ($modalidad === 'MIXTA') {
            $modalidad = 'MIXTO';
        }

        $dia               = strtoupper(trim($_POST['dia_semana'] ?? ''));
        $hora_inicio_raw   = trim($_POST['hora_inicio'] ?? '');
        $hora_fin_raw      = trim($_POST['hora_fin'] ?? '');
        $id_zona_raw       = $_POST['zona'] ?? null;
        $id_area_post      = $_POST['area'] ?? null;
        $numero_ficha      = trim($_POST['numero_ficha'] ?? '');
        $nivel_ficha       = trim($_POST['nivel_ficha'] ?? '');
        $instructor_input  = trim($_POST['nombre_instructor'] ?? '');
        $id_competencia    = isset($_POST['id_competencia']) && $_POST['id_competencia'] !== '' ? intval($_POST['id_competencia']) : null;
        $numero_trimestre  = isset($_POST['numero_trimestre']) && $_POST['numero_trimestre'] !== '' ? intval($_POST['numero_trimestre']) : null;
        $id_programa       = isset($_POST['id_programa']) && $_POST['id_programa'] !== '' ? intval($_POST['id_programa']) : null;
        $id_rae_raw        = trim($_POST['id_rae'] ?? '');

        $id_zona = ($id_zona_raw !== null && $id_zona_raw !== '') ? intval($id_zona_raw) : null;

        if (empty($dia) || empty($hora_inicio_raw) || empty($hora_fin_raw)) {
            echo json_encode(['status' => 'error', 'mensaje' => 'Día, hora inicio y hora fin son obligatorios.']);
            exit;
        }
        if ($modalidad === "PRESENCIAL" && (!$id_zona || $id_zona <= 0)) {
            echo json_encode(['status' => 'error', 'mensaje' => 'Debe seleccionar una zona válida.']);
            exit;
        }
        if (empty($numero_ficha)) {
            echo json_encode(['status' => 'error', 'mensaje' => 'Número de ficha obligatorio.']);
            exit;
        }
        if (empty($instructor_input)) {
            echo json_encode(['status' => 'error', 'mensaje' => 'Debe seleccionar un instructor.']);
            exit;
        }
        if (empty($id_competencia)) {
            echo json_encode(['status' => 'error', 'mensaje' => 'Debe seleccionar una competencia.']);
            exit;
        }
        if (empty($id_rae_raw)) {
            echo json_encode(['status' => 'error', 'mensaje' => 'Debe seleccionar al menos una RAE.']);
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

            $id_area = null;

            if ($modalidad === 'PRESENCIAL') {
                $id_area = resolveAreaForZona($conn, $id_zona, $id_area_post);
                if ($id_area === null) {
                    $conn->rollBack();
                    echo json_encode(['status' => 'error', 'mensaje' => 'Ambigüedad en zona: envía también id_area.']);
                    exit;
                }
            } else {
                if ($id_zona && $id_zona > 0) {
                    $id_area = resolveAreaForZona($conn, $id_zona, $id_area_post);
                }

                if (($id_zona === null || $id_zona <= 0) || $id_area === null) {
                    try {
                        $fallbackZona = $conn->query("SELECT id_zona, id_area FROM zonas WHERE estado = 1 ORDER BY id_zona ASC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
                        if ($fallbackZona) {
                            if ($id_zona === null || $id_zona <= 0) {
                                $id_zona = intval($fallbackZona['id_zona']);
                            }
                            if ($id_area === null) {
                                $id_area = isset($fallbackZona['id_area']) ? intval($fallbackZona['id_area']) : null;
                            }
                        }
                    } catch (Throwable $e) {
                    }
                }
            }

            if (!$numero_trimestre) {
                $stmtTrim = $conn->prepare("SELECT numero_trimestre FROM trimestre WHERE estado = 1 LIMIT 1");
                $stmtTrim->execute();
                $numero_trimestre = $stmtTrim->fetchColumn();
                $numero_trimestre = $numero_trimestre !== false ? intval($numero_trimestre) : null;
            }

            $id_instructor = resolveInstructorId($conn, $instructor_input);
            if (!$id_instructor) {
                $conn->rollBack();
                echo json_encode(['status' => 'error', 'mensaje' => 'No se encontró el instructor seleccionado.']);
                exit;
            }

            $hasNivelFicha = hasColumn($conn, 'fichas', 'nivel_ficha');

            $stmtFicha = $conn->prepare("SELECT id_ficha FROM fichas WHERE numero_ficha = :num LIMIT 1");
            $stmtFicha->execute([':num' => $numero_ficha]);
            $rowFicha = $stmtFicha->fetch(PDO::FETCH_ASSOC);

            if ($rowFicha) {
                $id_ficha = intval($rowFicha['id_ficha']);
                if ($hasNivelFicha && $nivel_ficha !== '') {
                    try {
                        $updFicha = $conn->prepare("UPDATE fichas SET nivel_ficha = :nivel WHERE id_ficha = :id_ficha");
                        $updFicha->execute([':nivel' => $nivel_ficha, ':id_ficha' => $id_ficha]);
                    } catch (Throwable $e) {
                    }
                }
            } else {
                if ($hasNivelFicha) {
                    $insFicha = $conn->prepare("INSERT INTO fichas (numero_ficha, nivel_ficha) VALUES (:num, :nivel)");
                    $insFicha->execute([':num' => $numero_ficha, ':nivel' => $nivel_ficha]);
                } else {
                    $insFicha = $conn->prepare("INSERT INTO fichas (numero_ficha) VALUES (:num)");
                    $insFicha->execute([':num' => $numero_ficha]);
                }
                $id_ficha = intval($conn->lastInsertId());
            }

            $idsRae = array_filter(array_map('trim', explode(',', $id_rae_raw)));
            $id_rae = implode(',', $idsRae);

            if ($modalidad === 'PRESENCIAL') {
                $stmtCruce = $conn->prepare(" 
                    SELECT COUNT(*) AS cnt FROM {$tablaHorario}
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
            } else {
                $stmtCruce = $conn->prepare(" 
                    SELECT COUNT(*) AS cnt FROM {$tablaHorario}
                    WHERE id_instructor = :id_instructor
                      AND dia = :dia
                      AND estado = 1
                      AND NOT (hora_fin <= :hora_inicio OR hora_inicio >= :hora_fin)
                ");
                $stmtCruce->execute([
                    ':id_instructor' => $id_instructor,
                    ':dia' => $dia,
                    ':hora_inicio' => $horaInicio,
                    ':hora_fin' => $horaFin
                ]);
            }

            if ($stmtCruce->fetchColumn() > 0) {
                $conn->rollBack();
                $mensajeCruce = $modalidad === 'PRESENCIAL'
                    ? 'Ya existe un horario activo que se cruza con el rango seleccionado en esta zona y área.'
                    : 'El instructor ya tiene un horario activo que se cruza en ese rango.';
                echo json_encode(['status' => 'error', 'mensaje' => $mensajeCruce]);
                exit;
            }

            $insHorario = $conn->prepare("
                INSERT INTO {$tablaHorario} (id_zona, id_area, modalidad, dia, hora_inicio, hora_fin, id_ficha, id_instructor, id_competencia, numero_trimestre, estado, id_programa, id_rae)
                VALUES (:id_zona, :id_area, :modalidad, :dia, :hora_inicio, :hora_fin, :id_ficha, :id_instructor, :id_competencia, :numero_trimestre, 1, :id_programa, :id_rae)
            ");
            $insHorario->execute([
                ':id_zona' => $id_zona,
                ':id_area' => $id_area,
                ':modalidad' => $modalidad,
                ':dia' => $dia,
                ':hora_inicio' => $horaInicio,
                ':hora_fin' => $horaFin,
                ':id_ficha' => $id_ficha,
                ':id_instructor' => $id_instructor,
                ':id_competencia' => $id_competencia,
                ':numero_trimestre' => $numero_trimestre,
                ':id_programa' => $id_programa,
                ':id_rae' => $id_rae
            ]);
            $newHorarioId = intval($conn->lastInsertId());

            $insT = $conn->prepare("INSERT INTO trimestralizacion (id_horario) VALUES (:id_horario)");
            $insT->execute([':id_horario' => $newHorarioId]);

            $warningPayload = extractExceededWarnings(
                fetchInstructorHourSummaries($conn, $tablaHorario, [$id_instructor]),
                fetchGroupHourSummaries($conn, $tablaHorario, [$id_ficha])
            );

            $conn->commit();
            echo json_encode([
                'status' => 'success',
                'mensaje' => 'Trimestralización creada correctamente.',
                'id_horario' => $newHorarioId,
                'warnings' => $warningPayload
            ]);
            exit;

        } catch (PDOException $e) {
            if ($conn->inTransaction()) $conn->rollBack();
            echo json_encode(['status' => 'error', 'mensaje' => 'Error en creación: ' . $e->getMessage()]);
            exit;
        }

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
            $affectedHorarioIds = [];

            $hasNivelFicha = hasColumn($conn, 'fichas', 'nivel_ficha');

            foreach ($registros as $r) {
                if (empty($r['id_horario'])) continue;
                $affectedHorarioIds[] = (int)$r['id_horario'];

                if (!empty($r['numero_ficha']) || !empty($r['nivel_ficha'])) {
                    if ($hasNivelFicha) {
                        $stmtFicha = $conn->prepare("
                            UPDATE fichas f
                            INNER JOIN {$tablaHorario} h ON f.id_ficha = h.id_ficha
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
                    } else {
                        $stmtFicha = $conn->prepare("
                            UPDATE fichas f
                            INNER JOIN {$tablaHorario} h ON f.id_ficha = h.id_ficha
                            SET f.numero_ficha = COALESCE(:numero_ficha, f.numero_ficha)
                            WHERE h.id_horario = :id_horario
                        ");
                        $stmtFicha->execute([
                            ':numero_ficha' => $r['numero_ficha'] ?? null,
                            ':id_horario' => $r['id_horario']
                        ]);
                    }
                }

                if (!empty($r['id_instructor'])) {
                    $stmtInst = $conn->prepare("
                        UPDATE {$tablaHorario}
                        SET id_instructor = :id_instructor
                        WHERE id_horario = :id_horario
                    ");
                    $stmtInst->execute([
                        ':id_instructor' => $r['id_instructor'],
                        ':id_horario' => $r['id_horario']
                    ]);
                }

                if (!empty($r['id_competencia'])) {
                    $stmtComp = $conn->prepare("
                        UPDATE {$tablaHorario}
                        SET id_competencia = :id_competencia
                        WHERE id_horario = :id_horario
                    ");
                    $stmtComp->execute([
                        ':id_competencia' => $r['id_competencia'],
                        ':id_horario' => $r['id_horario']
                    ]);
                }

                if (!empty($r['dia']) || !empty($r['hora_inicio']) || !empty($r['hora_fin'])) {
                    $stmtHoras = $conn->prepare("
                        UPDATE {$tablaHorario}
                        SET 
                            dia = COALESCE(:dia, dia),
                            hora_inicio = COALESCE(:hora_inicio, hora_inicio),
                            hora_fin = COALESCE(:hora_fin, hora_fin)
                        WHERE id_horario = :id_horario
                    ");
                    $stmtHoras->execute([
                        ':dia' => $r['dia'] ?? null,
                        ':hora_inicio' => $r['hora_inicio'] ?? null,
                        ':hora_fin' => $r['hora_fin'] ?? null,
                        ':id_horario' => $r['id_horario']
                    ]);
                }

                if (isset($r['raes'])) {
                    $raeString = is_array($r['raes'])
                        ? implode(",", array_map('trim', $r['raes']))
                        : trim((string)$r['raes']);

                    $stmtRae = $conn->prepare("
                        UPDATE {$tablaHorario}
                        SET id_rae = :id_rae
                        WHERE id_horario = :id_horario
                    ");
                    $stmtRae->execute([
                        ':id_rae' => $raeString,
                        ':id_horario' => $r['id_horario']
                    ]);
                }

                $actualizados++;
            }

            $affectedInstructorIds = [];
            $affectedFichaIds = [];
            if (!empty($affectedHorarioIds)) {
                [$placeholders, $paramsIds] = buildPlaceholders(array_values(array_unique($affectedHorarioIds)), 'hor');
                $stmtAffected = $conn->prepare("SELECT id_instructor, id_ficha FROM {$tablaHorario} WHERE id_horario IN (" . implode(',', $placeholders) . ")");
                $stmtAffected->execute($paramsIds);
                foreach ($stmtAffected->fetchAll(PDO::FETCH_ASSOC) as $row) {
                    if (!empty($row['id_instructor'])) $affectedInstructorIds[] = (int)$row['id_instructor'];
                    if (!empty($row['id_ficha'])) $affectedFichaIds[] = (int)$row['id_ficha'];
                }
            }

            $warningPayload = extractExceededWarnings(
                fetchInstructorHourSummaries($conn, $tablaHorario, array_values(array_unique($affectedInstructorIds))),
                fetchGroupHourSummaries($conn, $tablaHorario, array_values(array_unique($affectedFichaIds)))
            );

            $conn->commit();
            echo json_encode([
                'success' => true,
                'message' => "$actualizados registros actualizados correctamente.",
                'warnings' => $warningPayload
            ]);
        } catch (PDOException $e) {
            $conn->rollBack();
            echo json_encode(['success' => false, 'error' => 'Error SQL: ' . $e->getMessage()]);
        }
        exit;

    // ============================================================
    // ELIMINAR POR ZONA + AREA (MARCAR INACTIVO)
    // ============================================================
    case 'eliminar':
        $id_zona = $_GET['id_zona'] ?? null;
        $id_area_supplied = $_GET['id_area'] ?? null;

        if (!$id_zona) {
            echo json_encode(['status' => 'error', 'mensaje' => 'Debe indicar la zona a eliminar']);
            exit;
        }

        $resolved_area = resolveAreaForZona($conn, $id_zona, $id_area_supplied);
        if ($resolved_area === null) {
            echo json_encode(['status' => 'error', 'mensaje' => 'Ambigüedad en zona: envíe id_area']);
            exit;
        }

        try {
            $stmt = $conn->prepare("UPDATE {$tablaHorario} SET estado = 0 WHERE id_zona = :id_zona AND id_area = :id_area");
            $stmt->execute([
                ':id_zona' => intval($id_zona),
                ':id_area' => intval($resolved_area)
            ]);

            echo json_encode(['status' => 'success', 'mensaje' => 'Trimestralización eliminada correctamente.']);
            exit;

        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'mensaje' => 'Error al eliminar: ' . $e->getMessage()]);
            exit;
        }

    default:
        echo json_encode(['status' => 'error', 'mensaje' => 'Acción no reconocida']);
        exit;
}