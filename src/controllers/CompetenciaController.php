<?php
// ===== Encabezados JSON =====
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('html_errors', 0);
error_reporting(E_ALL);

// Errores -> JSON
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

// ===== Util =====
function read_json_body(): array {
  $raw = file_get_contents('php://input');
  if (!$raw) return [];
  $data = json_decode($raw, true);
  return is_array($data) ? $data : [];
}
function ok($payload){ echo json_encode($payload, JSON_UNESCAPED_UNICODE); exit; }
function fail($msg, $code=400, $extra=[]){ http_response_code($code); echo json_encode(array_merge(['error'=>$msg], $extra), JSON_UNESCAPED_UNICODE); exit; }

// ===== Acción =====
$accion = $_GET['accion'] ?? null;
if (!$accion) fail('Debe especificar la acción en la URL, por ejemplo: ?accion=listar');

// ===== Router =====
switch ($accion) {
  case 'listar': {
    ok($competencia->listar());
  }

  case 'obtener': {
    $id = $_GET['id_competencia'] ?? null;
    if (!$id) fail('Debe enviar el parámetro id_competencia');
    ok($competencia->obtenerPorId($id));
  }

  case 'crear': {
    $data = read_json_body() + $_POST;

    $id_competencia     = $data['id_competencia']     ?? null; // código manual
    $id_programa        = $data['id_programa']        ?? null; // FK obligatoria
    $nombre_competencia = $data['nombre_competencia'] ?? null;
    $descripcion        = $data['descripcion']        ?? null;

    if (!$id_competencia || trim($id_competencia) === '') {
      fail('Debe enviar id_competencia (código manual).');
    }
    if (!$id_programa || trim($id_programa) === '') {
      fail('Debe enviar id_programa (FK obligatoria).');
    }
    if (!$nombre_competencia || trim($nombre_competencia) === '' || !$descripcion || trim($descripcion) === '') {
      fail('Debe enviar nombre_competencia y descripcion válidos.');
    }

    ok($competencia->crear(
      $id_competencia,
      $id_programa,
      trim($nombre_competencia),
      trim($descripcion)
    ));
  }

  case 'actualizar': {
    $data = read_json_body() + $_POST;

    $id_competencia     = $data['id_competencia']     ?? null;
    $nombre_competencia = $data['nombre_competencia'] ?? null;
    $descripcion        = $data['descripcion']        ?? null;
    $id_programa        = $data['id_programa']        ?? null; // opcional para update

    if (!$id_competencia || !$nombre_competencia || trim($nombre_competencia) === '' || !$descripcion || trim($descripcion) === '') {
      fail('Debe enviar id_competencia, nombre_competencia y descripcion válidos.');
    }

    ok($competencia->actualizar(
      $id_competencia,
      trim($nombre_competencia),
      trim($descripcion),
      $id_programa !== null && $id_programa !== '' ? $id_programa : null
    ));
  }

  case 'inhabilitar': {
    $data = read_json_body() + $_POST;

    $id_competencia = $data['id_competencia'] ?? null;
    $estado = isset($data['estado']) ? (int)$data['estado'] : null;

    if (!$id_competencia || !isset($estado)) {
      fail('Debe enviar id_competencia y estado (0 o 1).');
    }

    ok($competencia->cambiarEstado($id_competencia, $estado));
  }

  case 'eliminar': {
    fail('La eliminación está deshabilitada. Use la acción inhabilitar.');
  }

  default: {
    fail('Acción no válida', 404);
  }
}
