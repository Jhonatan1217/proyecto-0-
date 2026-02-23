<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Programa.php';

if (!isset($conn)) {
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit;
}

$programaModel = new Programa($conn);
$accion = $_GET['accion'] ?? '';

function limpiar($v) {
    return trim($v ?? '');
}

// LISTAR PROGRAMAS
if ($accion === 'listar') {
    $result = $programaModel->listar();
    echo json_encode($result ?: []);
    exit;
}

// LISTAR INSTRUCTORES DE PROGRAMA
if ($accion === 'listarInstructores') {
    $id_programa = $_GET['id_programa'] ?? '';
    if (!$id_programa) {
        echo json_encode(['error' => 'ID de programa faltante']);
        exit;
    }
    $result = $programaModel->listarInstructores($id_programa);
    echo json_encode($result ?: []);
    exit;
}

// LISTAR TODOS LOS INSTRUCTORES
if ($accion === 'listarTodosInstructores') {
    $result = $programaModel->listarTodosInstructores();
    echo json_encode($result ?: []);
    exit;
}

// CREAR PROGRAMA
if ($accion === 'crear') {
    $json = json_decode(file_get_contents('php://input'), true);
    
    $id_programa = limpiar($json['id_programa'] ?? '');
    $nombre_programa = limpiar($json['nombre_programa'] ?? '');
    $descripcion = limpiar($json['descripcion'] ?? '');
    $duracion = limpiar($json['duracion'] ?? '');
    $nivel_formacion = limpiar($json['nivel_formacion'] ?? '');

    if (!$id_programa || !$nombre_programa) {
        echo json_encode(['error' => 'Código y nombre son obligatorios']);
        exit;
    }

    if (!$nivel_formacion) {
        echo json_encode(['error' => 'Debe seleccionar un tipo de programa']);
        exit;
    }

    $result = $programaModel->crear($id_programa, $nombre_programa, $descripcion, $duracion, $nivel_formacion);
    echo json_encode($result);
    exit;
}

// ACTUALIZAR PROGRAMA
if ($accion === 'actualizar') {
    $json = json_decode(file_get_contents('php://input'), true);

    $id_programa_actual = limpiar($json['id_programa'] ?? '');
    $nuevo_id_programa = limpiar($json['nuevo_id_programa'] ?? $id_programa_actual);
    $nombre_programa = limpiar($json['nombre_programa'] ?? '');
    $descripcion = limpiar($json['descripcion'] ?? '');
    $duracion = limpiar($json['duracion'] ?? '');
    $nivel_formacion = limpiar($json['nivel_formacion'] ?? '');

    if (!$id_programa_actual || !$nombre_programa) {
        echo json_encode(['error' => 'Datos insuficientes para actualizar']);
        exit;
    }

    if (!$nivel_formacion) {
        echo json_encode(['error' => 'Debe seleccionar un tipo de programa']);
        exit;
    }

    $result = $programaModel->actualizar($id_programa_actual, $nuevo_id_programa, $nombre_programa, $descripcion, $duracion, $nivel_formacion);
    echo json_encode($result);
    exit;
}

// ASIGNAR INSTRUCTORES
if ($accion === 'asignarInstructores') {
    $json = json_decode(file_get_contents('php://input'), true);
    
    $id_programa = limpiar($json['id_programa'] ?? '');
    $instructores = $json['instructores'] ?? [];

    if (!$id_programa) {
        echo json_encode(['error' => 'ID de programa faltante']);
        exit;
    }

    $result = $programaModel->asignarInstructores($id_programa, $instructores);
    echo json_encode($result);
    exit;
}

// CAMBIAR ESTADO
if ($accion === 'cambiarEstado') {
    $json = json_decode(file_get_contents('php://input'), true);
    
    $id_programa = limpiar($json['id_programa'] ?? '');
    $estado = $json['estado'] ?? null;

    if (!$id_programa || $estado === null) {
        echo json_encode(['error' => 'Datos insuficientes']);
        exit;
    }

    $result = $programaModel->cambiarEstado($id_programa, $estado);
    echo json_encode($result);
    exit;
}

echo json_encode(['error' => 'Acción no válida']);
exit;
?>