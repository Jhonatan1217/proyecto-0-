<?php

// Configuración base
error_reporting(E_ALL);
ini_set('display_errors', 1);
define('ACCESO_PERMITIDO', true);
define('BASE_PATH', dirname(__DIR__)); 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel principal</title>
</head>
<body>
<header>
    <?php require_once BASE_PATH . '/includes/header.php'; ?>
</header>

    <?php require_once BASE_PATH . '/includes/main.php'; ?>
    
<footer>
    <?php require_once BASE_PATH . '/includes/footer.php' ?>
</footer>

</body>
</html>
