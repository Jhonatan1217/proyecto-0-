<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);


include_once '../config/database.php';
include_once '../models/Competencia.php';

$competencia = new Competencia($conn);
$accion = $_GET['accion'];

switch ($accion) {
    case 'listar':
        $res = $competencia->listar();
        echo json_encode($res->fetch_all(MYSQLI_ASSOC));
        break;

    case 'obtener':
        $res = $competencia->obtenerPorId($_GET['id']);
        echo json_encode($res->fetch_assoc());
        break;

    case 'crear':
        $competencia->crear($_POST['descripcion']);
        echo json_encode(['mensaje' => 'Competencia creada']);
        break;

    case 'actualizar':
        $competencia->actualizar($_POST['id'], $_POST['descripcion']);
        echo json_encode(['mensaje' => 'Competencia actualizada']);
        break;

    case 'eliminar':
        $competencia->eliminar($_POST['id']);
        echo json_encode(['mensaje' => 'Competencia eliminada']);
        break;
}
?>
