<?php
// Habilitar reporte de errores para desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// --- Dependencias ---
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config/database.php'; 

// ============================================================
// COMPATIBILIDAD GET / POST / JSON (mejor opción sin tocar base)
// ============================================================
$__RAW = file_get_contents('php://input'); 
$__JSON = json_decode($__RAW, true);
function inreq($k) {
  global $__JSON;
  return $_POST[$k] ?? $_GET[$k] ?? ($__JSON[$k] ?? null);
}

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
        // Ejecutar consulta
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($data ?: []);
        break;
      }

      // Filtros opcionales
      $id_programa    = (inreq('id_programa') ?? '') !== 'all' ? trim((string) inreq('id_programa')) : '';
      $id_competencia = (inreq('id_competencia') ?? '') !== 'all' ? trim((string) inreq('id_competencia')) : '';
      // Construir consulta dinámica
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
      // Agregar condiciones según filtros
      if ($id_programa !== '') {
        $sql .= " AND c.id_programa = :id_programa";
        $params[':id_programa'] = $id_programa;
      }
      if ($id_competencia !== '') {
        $sql .= " AND r.id_competencia = :id_competencia";
        $params[':id_competencia'] = $id_competencia;
      }
      // Ordenar resultados
      $sql .= " ORDER BY p.id_programa, c.id_competencia, r.id_rae";
      // Preparar y ejecutar
      $stmt = $conn->prepare($sql);
      foreach ($params as $k => $v) $stmt->bindValue($k, $v);
      $stmt->execute();
      echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
      break;

    // ============================================================
    // CREAR UNA NUEVA RAE
    // ============================================================
    case 'crear':
      // Leer datos
      $id_rae         = trim((string) inreq('id_rae'));
      $descripcion    = trim((string) inreq('descripcion'));
      $id_competencia = intval(inreq('id_competencia'));
      // Validar datos
      if ($id_rae === '' || $descripcion === '' || !$id_competencia) {
        echo json_encode(['error' => 'Faltan campos requeridos (id_rae, descripcion, id_competencia)']);
        exit;
      }

      // Validar duplicado por id_rae
      $check = $conn->prepare("SELECT COUNT(*) FROM raes WHERE id_rae = ?");
      $check->execute([$id_rae]);
      if ($check->fetchColumn() > 0) { 
        echo json_encode(['error' => 'El id_rae ya existe']);
        exit;
      }

      // Validación por descripción+competencia
      $check2 = $conn->prepare("SELECT COUNT(*) FROM raes WHERE descripcion = ? AND id_competencia = ?");
      $check2->execute([$descripcion, $id_competencia]); // evitar duplicados
      if ($check2->fetchColumn() > 0) { 
        echo json_encode(['error' => 'Esta RAE ya existe en esa competencia']);
        exit;
      }
      // Insertar nueva RAE
      $stmt = $conn->prepare("INSERT INTO raes (id_rae, descripcion, id_competencia, estado) VALUES (?, ?, ?, 1)");
      $ok = $stmt->execute([$id_rae, $descripcion, $id_competencia]);
      // Resultado
      echo json_encode(['success' => $ok, 'message' => $ok ? 'RAE creada correctamente' : 'Error al crear la RAE']);
      break;

   // ============================================================
// ACTUALIZAR UNA RAE EXISTENTE
// ============================================================
case 'actualizar':
  // Leer datos
  $id_rae_actual   = trim((string) inreq('id_rae'));
  $nuevo_id_rae    = trim((string) inreq('nuevo_id_rae') ?: $id_rae_actual);
  $descripcion     = trim((string) inreq('descripcion'));
  $id_competencia  = intval(inreq('id_competencia'));
  // Validar datos
  if ($id_rae_actual === '' || $descripcion === '' || !$id_competencia) {
    echo json_encode(['error' => 'Faltan datos']);
    exit;
  }
  // Iniciar transacción
  try {
    $conn->beginTransaction();

    // Si el código cambió, validamos que no exista
    if ($nuevo_id_rae !== $id_rae_actual) {
      $chk = $conn->prepare("SELECT 1 FROM raes WHERE id_rae = ?");
      $chk->execute([$nuevo_id_rae]); // Verificar si ya existe
      if ($chk->fetchColumn()) {
        $conn->rollBack();
        echo json_encode(['error' => 'Ya existe una RAE con el nuevo código']);
        exit;
      }
    }

    // Validar duplicado por descripción+competencia
    $check = $conn->prepare("SELECT COUNT(*) FROM raes WHERE descripcion = ? AND id_competencia = ? AND id_rae != ?");
    $check->execute([$descripcion, $id_competencia, $id_rae_actual]);
    if ($check->fetchColumn() > 0) { // evitar duplicados
      $conn->rollBack();
      echo json_encode(['error' => 'Ya existe una RAE igual en esa competencia']);
      exit;
    }

    // Actualizar, incluyendo el id_rae si cambió
    $stmt = $conn->prepare("
      UPDATE raes
         SET id_rae = ?, descripcion = ?, id_competencia = ?
       WHERE id_rae = ?
    ");
    $ok = $stmt->execute([$nuevo_id_rae, $descripcion, $id_competencia, $id_rae_actual]); // Ejecutar actualización

    $conn->commit(); // confirmar cambios
    echo json_encode([
      'success' => $ok,
      'message' => $ok ? 'RAE actualizada correctamente' : 'Error al actualizar la RAE'
    ]);
  } catch (Throwable $e) { // Error en la actualización
    if ($conn->inTransaction()) $conn->rollBack();
    echo json_encode(['error' => 'Error al actualizar: ' . $e->getMessage()]);
  }
  break;


    // ============================================================
    // INHABILITAR / ACTIVAR RAE
    // ============================================================
    case 'inhabilitar':
      // Leer datos
      $id_rae = trim((string) inreq('id_rae'));
      $estado = intval(inreq('estado'));
      // Validar datos
      if ($id_rae === '' || ($estado !== 0 && $estado !== 1)) {
        echo json_encode(['error' => 'Faltan parámetros']);
        exit;
      }
      // Actualizar estado
      $stmt = $conn->prepare("UPDATE raes SET estado = ? WHERE id_rae = ?");
      $ok = $stmt->execute([$estado, $id_rae]);
      // Resultado
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
      // Listar programas
      $stmt = $conn->query("SELECT id_programa, nombre_programa FROM programas ORDER BY id_programa");
      echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
      break;

    case 'competenciasPorPrograma':
      // Listar competencias según programa
      $idp = trim((string) inreq('id_programa'));
      if ($idp === '') {
        echo json_encode(['error' => 'id_programa es obligatorio']);
        exit;
      }
      // Obtener competencias
      $stmt = $conn->prepare("SELECT id_competencia, nombre_competencia FROM competencias WHERE id_programa = ? ORDER BY id_competencia");
      $stmt->execute([$idp]); // Ejecutar consulta
      echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
      break;

    default:
      echo json_encode(['error' => 'Acción no válida']);
      break;
  } 
} catch (Throwable $e) { // Error general
  http_response_code(500);
  echo json_encode(['error' => $e->getMessage()]);
}
