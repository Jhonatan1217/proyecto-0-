<?php
include_once '../config/conexion.php';
include_once '../models/Horario.php';

$horario = new Horario($conn);
$accion = $_GET['accion'];

switch ($accion) {
    case 'listar':
        $res = $horario->listar();
        echo json_encode($res->fetch_all(MYSQLI_ASSOC));
        break;

    case 'obtener':
        $res = $horario->obtenerPorId($_GET['id']);
        echo json_encode($res->fetch_assoc());
        break;

    case 'crear':
        $horario->crear($_POST['dia'], $_POST['hora_inicio'], $_POST['hora_fin'], $_POST['id_zona'], $_POST['id_ficha'], $_POST['id_instructor']);
        echo json_encode(['mensaje' => 'Horario creado']);
        break;

    case 'actualizar':
        $horario->actualizar($_POST['id'], $_POST['dia'], $_POST['hora_inicio'], $_POST['hora_fin'], $_POST['id_zona'], $_POST['id_ficha'], $_POST['id_instructor']);
        echo json_encode(['mensaje' => 'Horario actualizado']);
        break;

    case 'eliminar':
        $horario->eliminar($_POST['id']);
        echo json_encode(['mensaje' => 'Horario eliminado']);
        break;
}
?>
