<?php
// Importaciones
include_once '../helpers/ZonaHelper.php';
include_once '../../config/database.php';

$data = getZonas($conn); // Extraccion de datos a la BD
// Imprimir informacion en pantalla
echo "<h3>Listado de Zonas</h3><pre>";
print_r($data);
echo "</pre>";
?>
