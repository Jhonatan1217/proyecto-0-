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

try {
  switch ($accion) {

    // ============================================================
    // LISTAR (con filtros opcionales id_programa / id_competencia)
    // ============================================================
    case 'listar':
      // Si viene un id puntual, respeta tu comportamiento original
      if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $stmt = $conn->prepare("
          SELECT r.id_rae, r.descripcion, r.estado, 
                 c.id_competencia, c.nombre_competencia, c.id_programa,
                 p.nombre_programa
          FROM raes r
          LEFT JOIN competencias c ON r.id_competencia = c.id_competencia
          LEFT JOIN programas p    ON c.id_programa     = p.id_programa
          WHERE r.id_rae = ?
        ");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($data ?: []);
        break;
      }

      // Filtros opcionales
      $id_programa    = ($_GET['id_programa'] ?? '') !== 'all' ? trim($_GET['id_programa'] ?? '') : '';
      $id_competencia = ($_GET['id_competencia'] ?? '') !== 'all' ? trim($_GET['id_competencia'] ?? '') : '';

      $sql = "
        SELECT r.id_rae, r.descripcion, r.estado,
               c.id_competencia, c.nombre_competencia, c.id_programa,
               p.nombre_programa
        FROM raes r
        LEFT JOIN competencias c ON r.id_competencia = c.id_competencia
        LEFT JOIN programas p    ON c.id_programa     = p.id_programa
        WHERE 1=1
      ";
      $params = [];

      if ($id_programa !== '') {
        $sql .= " AND c.id_programa = :id_programa";
        $params[':id_programa'] = $id_programa;
      }
      if ($id_competencia !== '') {
        $sql .= " AND r.id_competencia = :id_competencia";
        $params[':id_competencia'] = $id_competencia;
      }

      $sql .= " ORDER BY p.id_programa, c.id_competencia, r.id_rae";

      $stmt = $conn->prepare($sql);
      foreach ($params as $k => $v) $stmt->bindValue($k, $v);
      $stmt->execute();
      echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
      break;

    // ============================================================
    // CREAR UNA NUEVA RAE
    // (ahora requiere id_rae, que es tu "código")
    // ============================================================
    case 'crear':
      if (empty($_GET['id_rae']) || empty($_GET['descripcion']) || empty($_GET['id_competencia'])) {
        echo json_encode(['error' => 'Faltan campos requeridos (id_rae, descripcion, id_competencia)']);
        exit;
      }

      $id_rae        = trim($_GET['id_rae']);
      $descripcion   = trim($_GET['descripcion']);
      $id_competencia= intval($_GET['id_competencia']);

      // Validar duplicado por id_rae
      $check = $conn->prepare("SELECT COUNT(*) FROM raes WHERE id_rae = ?");
      $check->execute([$id_rae]);
      if ($check->fetchColumn() > 0) {
        echo json_encode(['error' => 'El id_rae ya existe']);
        exit;
      }

      // También puedes mantener tu validación por descripción+competencia
      $check2 = $conn->prepare("SELECT COUNT(*) FROM raes WHERE descripcion = ? AND id_competencia = ?");
      $check2->execute([$descripcion, $id_competencia]);
      if ($check2->fetchColumn() > 0) {
        echo json_encode(['error' => 'Esta RAE ya existe en esa competencia']);
        exit;
      }

      $stmt = $conn->prepare("INSERT INTO raes (id_rae, descripcion, id_competencia, estado) VALUES (?, ?, ?, 1)");
      $ok = $stmt->execute([$id_rae, $descripcion, $id_competencia]);

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

      $id            = trim($_GET['id_rae']); // puede ser string si id_rae no es numérico
      $descripcion   = trim($_GET['descripcion']);
      $id_competencia= intval($_GET['id_competencia']);

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

      $id = trim($_GET['id_rae']);
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

    // ============================================================
    // UTILIDADES PARA LA UI (filtros y combos)
    // ============================================================
    case 'programas':
      $stmt = $conn->query("SELECT id_programa, nombre_programa FROM programas ORDER BY id_programa");
      echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
      break;

    case 'competenciasPorPrograma':
      if (empty($_GET['id_programa'])) {
        echo json_encode(['error' => 'id_programa es obligatorio']);
        exit;
      }
      $idp = trim($_GET['id_programa']);
      $stmt = $conn->prepare("SELECT id_competencia, nombre_competencia FROM competencias WHERE id_programa = ? ORDER BY id_competencia");
      $stmt->execute([$idp]);
      echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
      break;

    default:
      echo json_encode(['error' => 'Acción no válida']);
      break;
  }
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['error' => $e->getMessage()]);
}
