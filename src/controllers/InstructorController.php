<?php
include_once '../config/conexion.php';
include_once '../models/Instructor.php';

$instructor = new Instructor($conn);
$accion = $_GET['accion'];

switch ($accion) {
    case 'listar':
        $res = $instructor->listar();
        echo json_encode($res->fetch_all(MYSQLI_ASSOC));
        break;

    case 'obtener':
        $res = $instructor->obtenerPorId($_GET['id']);
        echo json_encode($res->fetch_assoc());
        break;

    case 'crear':
        $instructor->crear($_POST['nombre'], $_POST['apellido'], $_POST['tipo']);
        echo json_encode(['mensaje' => 'Instructor creado']);
        break;

    case 'actualizar':
        $instructor->actualizar($_POST['id'], $_POST['nombre'], $_POST['apellido'], $_POST['tipo']);
        echo json_encode(['mensaje' => 'Instructor actualizado']);
        break;

    case 'eliminar':
        $instructor->eliminar($_POST['id']);
        echo json_encode(['mensaje' => 'Instructor eliminado']);
        break;
}
?>
