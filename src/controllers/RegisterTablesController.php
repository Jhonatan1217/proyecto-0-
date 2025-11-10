<?php
require_once "../../config/database.php";
require_once "../models/Trimestralizacion.php";

header('Content-Type: application/json');

// Instanciar la clase
$trimestralizacion = new Trimestralizacion($conn);

// Detectar la acción
$accion = $_GET['action'] ?? $_POST['action'] ?? null;

if (!$accion) {
    echo json_encode(["status" => "error", "mensaje" => "No se especificó ninguna acción."]);
    exit;
}

// Enrutador simple
switch ($accion) {
    case 'listar':
        echo json_encode($trimestralizacion->listar());
        break;

    case 'obtener':
        $id = $_GET['id'] ?? null;
        echo json_encode($trimestralizacion->obtenerPorId($id));
        break;

    case 'crear':
        $id_horario = $_POST['id_horario'] ?? null;
        echo json_encode($trimestralizacion->crear($id_horario));
        break;

    // case 'vaciar_db':
    //     echo json_encode($trimestralizacion->eliminar());
    //     break;

    default:
        echo json_encode(["status" => "error", "mensaje" => "Acción no válida: $accion"]);
        break;
}