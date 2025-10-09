<?php

error_reporting(E_ALL);
ini_set('display_errors', 1); // Fragmento de codigo para identificar errores
// Importaciones
include_once '../../config/database.php';
include_once '../helpers/CompetenciaHelper.php';

$data = getCompetencias($conn); // Conexion
// Imprimir informacion en pantalla
echo "<h3>Listado de Competencias</h3><pre>";
print_r($data);
echo "</pre>";
?>
