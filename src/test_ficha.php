<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers/HorarioHelper.php';

global $conn;

$idFicha = 3172293; // Ejemplo
$resultado = getHorarioFicha($conn, $idFicha);

header('Content-Type: application/json');
echo json_encode($resultado, JSON_PRETTY_PRINT);
?>
