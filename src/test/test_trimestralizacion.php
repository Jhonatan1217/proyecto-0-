<?php
// Importaciones
include_once '../helpers/TrimestralizacionHelper.php';
include_once '../../config/database.php';

$data = getTrimestralizaciones($conn); // Extraccion de datos a la BD
// Imprimir informacion en pantalla
echo "<h3>Listado de Trimestralizaciones</h3><pre>";
print_r($data);
echo "</pre>";
?>
