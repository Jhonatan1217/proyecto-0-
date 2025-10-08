<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers/HorarioHelper.php';

global $conn;

$idInstructor = 1; // Ejemplo
$resultado = getHorarioInstructor($conn, $idInstructor);

header('Content-Type: application/json');
echo json_encode($resultado, JSON_PRETTY_PRINT);
?>
