<?php
include_once '../config/conexion.php';
include_once '../models/Zona.php';

$zona = new Zona($conn);
$accion = $_GET['accion'];

switch ($accion) {
    case 'listar':
        $res = $zona->listar();
        echo json_encode($res->fetch_all(MYSQLI_ASSOC));
        break;

    case 'obtener':
        $res = $zona->obtenerPorId($_GET['id']);
        echo json_encode($res->fetch_assoc());
        break;

    case 'crear':
        $zona->crear();
        echo json_encode(['mensaje' => 'Zona creada']);
        break;

    case 'eliminar':
        $zona->eliminar($_POST['id']);
        echo json_encode(['mensaje' => 'Zona eliminada']);
        break;
}
?>
