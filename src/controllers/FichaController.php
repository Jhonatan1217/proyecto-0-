<?php
include_once '../config/conexion.php';
include_once '../models/Ficha.php';

$ficha = new Ficha($conn);
$accion = $_GET['accion'];

switch ($accion) {
    case 'listar':
        $res = $ficha->listar();
        echo json_encode($res->fetch_all(MYSQLI_ASSOC));
        break;

    case 'obtener':
        $res = $ficha->obtenerPorId($_GET['id']);
        echo json_encode($res->fetch_assoc());
        break;

    case 'crear':
        $ficha->crear();
        echo json_encode(['mensaje' => 'Ficha creada']);
        break;

    case 'eliminar':
        $ficha->eliminar($_POST['id']);
        echo json_encode(['mensaje' => 'Ficha eliminada']);
        break;
}
?>
