<?php
// Importaciones
include_once '../helpers/FichaHelper.php';
include_once '../../config/database.php';

$data = getFichas($conn); // Extraccion de datos de la BD
// Imprimir informacion en pantalla
echo "<h3>Listado de Fichas</h3><pre>";
print_r($data);
echo "</pre>";
?>
