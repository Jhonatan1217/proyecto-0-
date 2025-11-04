<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
define('ACCESO_PERMITIDO', true);

// Ruta base del proyecto
define('BASE_PATH', __DIR__);

// Base URL dinámica
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$script_dir = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
define('BASE_URL', $protocol . $host . $script_dir);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Proyecto 0</title>
    <link rel="icon" type="image/png" href="./src/assets/img/logoSena.png">
    <link rel="stylesheet" href="./public/css/output.css">
</head>
<body class="flex flex-col min-h-screen font-sans bg-white text-gray-900">
    <header>
        <?php require_once BASE_PATH . '/src/includes/header.php'; ?>
    </header>

    <main class="flex-grow">
        <?php require_once BASE_PATH . '/src/includes/main.php'; ?>
    </main>

    <footer>
        <?php require_once BASE_PATH . '/src/includes/footer.php'; ?>
    </footer>
</body>
</html>
