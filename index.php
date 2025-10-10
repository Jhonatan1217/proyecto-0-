<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
define('ACCESO_PERMITIDO', true);

define('BASE_PATH',__DIR__);

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$script_dir = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
define('BASE_URL', $protocol . $host . $script_dir);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel principal</title>
</head>
<body>
<header>
    <?php require_once BASE_PATH . '/src/includes/header.php'; ?>
</header>

    <?php require_once BASE_PATH . '/src/includes/main.php'; ?>
    
<footer>
    <?php require_once BASE_PATH . '/src/includes/footer.php'; ?>
</footer>

</body>
</html>
