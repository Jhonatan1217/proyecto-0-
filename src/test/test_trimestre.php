<?php
include_once __DIR__ . '/../../config/database.php';

if (isset($conn) && $conn instanceof PDO) {
    echo " Conexión establecida correctamente con la base de datos.";
} else {
    echo " Error: No se pudo establecer conexión con la base de datos.";
}
?>
