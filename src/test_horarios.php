<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers/HorarioHelper.php';

// Verificar conexión
if (!isset($conn) || !$conn) {
    die("Error: No hay conexión a la base de datos.");
}

$idFicha = 3172293; // ejemplo
$horario = getHorarioFicha($conn, $idFicha);

header('Content-Type: application/json');
echo json_encode($horario, JSON_PRETTY_PRINT);
?>
