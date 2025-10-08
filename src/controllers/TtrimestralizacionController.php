<?php
include_once '../config/conexion.php';
include_once '../models/Trimestralizacion.php';

$trimestral = new Trimestralizacion($conn);
$accion = $_GET['accion'];

switch ($accion) {
    case 'listar':
        $res = $trimestral->listar();
        echo json_encode($res->fetch_all(MYSQLI_ASSOC));
        break;

    case 'obtener':
        $res = $trimestral->obtenerPorId($_GET['id']);
        echo json_encode($res->fetch_assoc());
        break;

    case 'crear':
        $trimestral->crear($_POST['id_horario']);
        echo json_encode(['mensaje' => 'Trimestralización creada']);
        break;

    case 'eliminar':
        $trimestral->eliminar($_POST['id']);
        echo json_encode(['mensaje' => 'Trimestralización eliminada']);
        break;
}
?>
