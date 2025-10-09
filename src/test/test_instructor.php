<?php
// Importanciones
include_once '../helpers/InstructorHelper.php';
include_once '../../config/database.php';

$data = getInstructores($conn); // Extraccion de datos a la BD
// Imprimir informacion en pantalla
echo "<h3>Listado de Instructores</h3><pre>";
print_r($data);
echo "</pre>";
?>
