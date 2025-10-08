<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
define('ACCESO_PERMITIDO', true);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel principal</title>
</head>
<body>
<header>
    <?php require_once __DIR__ . '/src/includes/header.php'; ?>
</header>

    <?php require_once __DIR__ . '/src/includes/main.php'; ?>
    
<footer>
    <?php require_once __DIR__ . '/src/includes/footer.php'; ?>
</footer>

</body>
</html>
