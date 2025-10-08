<?php
include_once '../helpers/TrimestralizacionHelper.php';
include_once '../../config/database.php';

$data = getTrimestralizaciones($conn);

echo "<h3>Listado de Trimestralizaciones</h3><pre>";
print_r($data);
echo "</pre>";
?>
