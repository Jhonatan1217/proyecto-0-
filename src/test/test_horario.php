<?php
// Importaciones
include_once '../helpers/HorarioHelper.php';
include_once '../../config/database.php';

$data = getHorarios($conn); // Extraccion de datos de la BD
// Imprimir informacion en pantalla
echo "<h3>Listado de Horarios</h3><pre>";
print_r($data);
echo "</pre>";
?>
