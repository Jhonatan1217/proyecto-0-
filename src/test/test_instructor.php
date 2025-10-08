<?php
include_once '../helpers/InstructorHelper.php';
include_once '../../config/database.php';

$data = getInstructores($conn);

echo "<h3>Listado de Instructores</h3><pre>";
print_r($data);
echo "</pre>";
?>
