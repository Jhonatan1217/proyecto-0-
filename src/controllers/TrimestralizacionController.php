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

switch ($accion) {

    // ============================================================
    // LISTAR POR ZONA (+ opcional AREA)
    // ============================================================
    case 'listar':
        $id_zona = $_GET['id_zona'] ?? null;
        $id_area_supplied = $_GET['id_area'] ?? null;

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
                    f.nivel_ficha,
                    p.id_programa,
                    p.nombre_programa,
                    i.id_instructor,
                    i.nombre_instructor,
                    i.tipo_instructor,
                    c.id_competencia,
                    c.nombre_competencia,
                    r.id_rae,
                    r.descripcion AS descripcion_rae
                FROM horarios h
                LEFT JOIN fichas f ON h.id_ficha = f.id_ficha
                LEFT JOIN programas p ON h.id_programa = p.id_programa
                LEFT JOIN instructores i ON h.id_instructor = i.id_instructor
                LEFT JOIN competencias c ON h.id_competencia = c.id_competencia
                LEFT JOIN raes r ON FIND_IN_SET(r.id_rae, h.id_rae)
                WHERE h.id_zona = :id_zona
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
            $stmt = $conn->prepare("UPDATE horarios SET estado = 0 WHERE id_zona = :id_zona AND id_area = :id_area");
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