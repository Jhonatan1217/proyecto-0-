<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('BASE_PATH', __DIR__);

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$script_dir = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
define('BASE_URL', $protocol . $host . $script_dir);

require_once BASE_PATH . '/src/helpers/AuthHelper.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Proyecto Z</title>
    <link rel="icon" type="image/png" href="./src/assets/img/logoSena.png">
    <link rel="stylesheet" href="./public/css/output.css">
</head>

<body class="flex flex-col min-h-screen font-sans bg-white text-gray-900">

    <?php if (isAuthenticated()): ?>

        <!-- HEADER COMPLETO -->
        <?php require BASE_PATH . '/src/includes/header-private.php'; ?>

    <?php else: ?>

        <!-- SOLO HEADER VISUAL -->
        <?php require BASE_PATH . '/src/includes/header-public.php'; ?>

    <?php endif; ?>


    <main class="flex-grow">
        <?php require BASE_PATH . '/src/includes/main.php'; ?>
    </main>

    <footer>
        <?php require BASE_PATH . '/src/includes/footer.php'; ?>
    </footer>

</body>
</html>