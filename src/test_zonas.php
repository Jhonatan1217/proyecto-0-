<?php
require_once __DIR__ . '/helpers/ZonaHelper.php'; 

$zonas = getZonas();
header('Content-Type: application/json');
echo json_encode($zonas, JSON_PRETTY_PRINT);
?>
