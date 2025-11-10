<?php
// ===== Encabezados JSON =====
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('html_errors', 0);
error_reporting(E_ALL);

// Errores -> JSON unificados
set_error_handler(function($severity, $message, $file, $line) {
  if (!(error_reporting() & $severity)) return;
  http_response_code(500);
  echo json_encode(['error'=>'PHPError','message'=>$message,'file'=>$file,'line'=>$line], JSON_UNESCAPED_UNICODE);
  exit;
});
set_exception_handler(function($ex){
  http_response_code(500);
  echo json_encode([
    'error'=>'Exception',
    'message'=>$ex->getMessage(),
    'file'=>$ex->getFile(),
    'line'=>$ex->getLine()
  ], JSON_UNESCAPED_UNICODE);
  exit;
});

// ===== Dependencias =====
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Competencia.php';

// ===== Conexión =====
if (!isset($conn)) { echo json_encode(['error'=>'No se pudo establecer conexión con la base de datos']); exit; }
$competencia = new Competencia($conn);

// ===== Utils =====
function read_json_body(): array {
  $raw = file_get_contents('php://input');
  if (!$raw) return [];
  $data = json_decode($raw, true);
  return is_array($data) ? $data : [];
}
function ok($payload){ echo json_encode($payload, JSON_UNESCAPED_UNICODE); exit; }
function fail($msg, $code=400, $extra=[]){ http_response_code($code); echo json_encode(array_merge(['error'=>$msg], $extra), JSON_UNESCAPED_UNICODE); exit; }

/**
 * Verifica si existe una columna en una tabla (para updates seguros)
 */
function table_has_column(PDO $conn, string $table, string $col): bool {
  $stmt = $conn->prepare("SHOW COLUMNS FROM `$table` LIKE :c");
  $stmt->execute([':c' => $col]);
  return (bool)$stmt->fetch();
}

// ===== Acción =====
$accion = $_GET['accion'] ?? null;
if (!$accion) fail('Debe especificar la acción en la URL, por ejemplo: ?accion=listar');

// ===== Router =====
switch ($accion) {
// Listar todas las competencias
  case 'listar': {
    ok($competencia->listar()); // Obtener todas las competencias
    break;
  }
// Obtener competencia por ID
  case 'obtener': {
    // acepta ?id_competencia=... ó ?id=...
    $id = $_GET['id_competencia'] ?? $_GET['id'] ?? null; // ID vía GET
    if (!$id) fail('Debe enviar el parámetro id_competencia'); // Verificar si se proporcionó ID
    ok($competencia->obtenerPorId($id)); // Obtener competencia por ID
    break;
  }
// Crear nueva competencia
  case 'crear': {
    $data = read_json_body() + $_POST; // Decodificar JSON o usar POST
// Validar campos obligatorios
    $id_competencia     = $data['id_competencia']     ?? null; // código manual
    $id_programa        = $data['id_programa']        ?? null; // FK obligatoria
    $nombre_competencia = $data['nombre_competencia'] ?? null;
    $descripcion        = $data['descripcion']        ?? null;
// Validaciones
    if (!$id_competencia || trim($id_competencia) === '') { // código obligatorio
      fail('Debe enviar id_competencia (código manual).');
    }
    if (!$id_programa || trim($id_programa) === '') {
      fail('Debe enviar id_programa (FK obligatoria).');
    }
    if (!$nombre_competencia || trim($nombre_competencia) === '' || !$descripcion || trim($descripcion) === '') {
      fail('Debe enviar nombre_competencia y descripcion válidos.');
    }
// Crear competencia
    ok($competencia->crear(
      $id_competencia,
      $id_programa,
      trim($nombre_competencia), 
      trim($descripcion)
    ));
    break;
  }
// Actualizar competencia
  case 'actualizar': {
    // Edición flexible + permite cambiar el código
    $data = read_json_body() + $_POST;

    $id_original = $data['id_competencia'] ?? null; // id actual en BD (obligatorio)
    if (!$id_original) fail('Debe enviar id_competencia (id actual) para actualizar.');

    // Si el usuario cambió el código, vendrá como nuevo_id_competencia o codigo_competencia
    $nuevo_id = trim($data['nuevo_id_competencia'] ?? $data['codigo_competencia'] ?? '');

    // Campos opcionales en edición (partial update)
    $nombre       = array_key_exists('nombre_competencia', $data) ? trim((string)$data['nombre_competencia']) : null;
    $descripcion  = array_key_exists('descripcion', $data)        ? trim((string)$data['descripcion'])        : null;
    $id_programa  = array_key_exists('id_programa', $data)        ? $data['id_programa']                       : null;
     // Construir consulta dinámica
    $sets   = [];
    $params = [':id_original' => $id_original];
    // Cambiar PK 
    if ($nuevo_id !== '' && $nuevo_id !== $id_original) {
      // Actualiza PK y, si existe, también codigo_competencia
      $sets[]              = 'id_competencia = :nuevo_id';
      $params[':nuevo_id'] = $nuevo_id;

      if (table_has_column($conn, 'competencias', 'codigo_competencia')) {
        $sets[] = 'codigo_competencia = :nuevo_id';
      }
    } // Cambios opcionales
    if ($nombre !== null) {
      $sets[]            = 'nombre_competencia = :nombre';
      $params[':nombre'] = $nombre;
    }
    if ($descripcion !== null) {
      $sets[]                 = 'descripcion = :descripcion';
      $params[':descripcion'] = $descripcion;
    }
    if ($id_programa !== null && $id_programa !== '') {
      $sets[]                 = 'id_programa = :id_programa';
      $params[':id_programa'] = $id_programa;
    }

    if (!$sets) ok(['ok' => true, 'noop' => true]); // nada que actualizar
    // Ejecutar actualización
    try {
      $sql  = 'UPDATE competencias SET '.implode(', ', $sets).' WHERE id_competencia = :id_original';
      $stmt = $conn->prepare($sql);
      $stmt->execute($params);
      ok(['ok' => true, 'id' => $params[':nuevo_id'] ?? $id_original]);
    } catch (PDOException $e) {
      // Devuelve mensaje real (FKs, columna inexistente, etc.)
      fail('DB_ERROR', 500, ['message' => $e->getMessage()]);
    }
    break;
  }
  // Inhabilitar competencia (cambiar estado)
  case 'inhabilitar': {
    $data = read_json_body() + $_POST; // Decodificar JSON o usar POST

    $id_competencia = $data['id_competencia'] ?? null; // ID vía JSON o POST
    $estado = isset($data['estado']) ? (int)$data['estado'] : null; // Estado vía JSON o POST
    // Validaciones
    if (!$id_competencia || !isset($estado)) {
      fail('Debe enviar id_competencia y estado (0 o 1).');
    }

    ok($competencia->cambiarEstado($id_competencia, $estado));
    break;
  }
  // Eliminar competencia (deshabilitado)
  case 'eliminar': {
    fail('La eliminación está deshabilitada. Use la acción inhabilitar.');
    break;
  }

  
  default: { // Fin switch
    fail('Acción no válida', 404);
  }
}
