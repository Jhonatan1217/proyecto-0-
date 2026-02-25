<?php

// INICIO DE SESIÓN

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

//  OBTENER PÁGINA ACTUAL

$page = $_GET['page'] ?? 'landing';
$page = basename($page);

$paginas_publicas = ['login', 'landing'];

// LOGOUT
if ($page === 'logout') {
    $_SESSION = [];
    session_unset();
    session_destroy();
    header("Location: index.php?page=login");
    exit;
}

// SI YA ESTÁ LOGUEADO Y QUIERE IR A LOGIN O LANDING
if (isset($_SESSION['usuario_id']) && in_array($page, ['login', 'landing'])) {
    header("Location: index.php?page=register_tables");
    exit;
}

// SI NO ESTÁ LOGUEADO Y QUIERE IR A PRIVADA
if (!isset($_SESSION['usuario_id']) && !in_array($page, $paginas_publicas)) {
    header("Location: index.php?page=login");
    exit;
}


// CONSTANTES BASE

define('BASE_PATH', __DIR__);

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$script_dir = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
define('BASE_URL', $protocol . $host . $script_dir);

// Helper autenticación
require_once BASE_PATH . '/src/helpers/AuthHelper.php';

// CARGAR VISTA

$file = BASE_PATH . "/src/views/$page.php";

if (!file_exists($file)) {
    $file = BASE_PATH . "/src/views/landing.php";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Proyecto Z</title>
    <link rel="icon" type="image/png" href="./src/assets/img/logoSena.png">
    <link rel="stylesheet" href="./public/css/output.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/fonts.css">
    <script src="<?= BASE_URL ?>src/assets/js/sweetalert2.all.min.js"></script>
</head>

<body class="flex flex-col min-h-screen font-sans bg-white text-gray-900">

<?php if (isset($_SESSION['usuario_id'])): ?>
    <?php require BASE_PATH . '/src/includes/header-private.php'; ?>
<?php else: ?>
    <?php require BASE_PATH . '/src/includes/header-public.php'; ?>
<?php endif; ?>

<main class="flex-grow">
    <?php include $file; ?>
</main>

<footer>
    <?php require BASE_PATH . '/src/includes/footer.php'; ?>
</footer>

</body>
</html>