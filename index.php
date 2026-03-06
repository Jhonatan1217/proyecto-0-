<?php

/* =========================
   INICIO DE SESIÓN
========================= */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* =========================
   HEADERS ANTI-CACHE
========================= */

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

/* =========================
   CONFIGURACIÓN ERRORES
========================= */

error_reporting(E_ALL);
ini_set('display_errors', 1);

/* =========================
   OBTENER PÁGINA ACTUAL
========================= */

$page = $_GET['page'] ?? 'landing';
$page = basename($page);

/* =========================
   DEFINICIÓN DE RUTAS
========================= */

// <<< MODIFICADO >>> Se agrega 'register_tables' a las páginas públicas
$public_pages = ['landing', 'login', 'register_tables']; 
$private_default = 'register_tables';

/* =========================
   ENDPOINT AJAX PARA VALIDAR SESIÓN
========================= */

if ($page === 'session_check') {
    echo isset($_SESSION['usuario_id']) ? "1" : "0";
    exit;
}

/* =========================
   LOGOUT
========================= */

if ($page === 'logout') {
    $_SESSION = [];
    session_unset();
    session_destroy();

    header("Location: index.php?page=login");
    exit;
}

/* =========================
   CONTROL DE ACCESO
========================= */

// 1. Usuario NO autenticado intentando acceder a una página que NO es pública
if (!isset($_SESSION['usuario_id']) && !in_array($page, $public_pages)) {
    header("Location: index.php?page=login");
    exit;
}

// 2. <<< MODIFICADO >>> 
// Si está logueado e intenta ir a login o landing, lo mandamos a la tabla privada.
// Se quita 'register_tables' de esta validación para que pueda navegarla estando logueado.
if (isset($_SESSION['usuario_id']) && ($page === 'landing' || $page === 'login')) {
    header("Location: index.php?page=$private_default");
    exit;
}

// Redirección por página eliminada
if ($page === 'gestion_perfil') {
    header("Location: index.php?page=$private_default");
    exit;
}

/* =========================
   CONSTANTES BASE
========================= */

define('BASE_PATH', __DIR__);

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$script_dir = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);

define('BASE_URL', $protocol . $host . $script_dir);

/* =========================
   CARGA DE VISTA
========================= */

$file = BASE_PATH . "/src/views/$page.php";

// Si el archivo no existe físicamente, cargamos landing por defecto
if (!file_exists($file)) {
    $file = BASE_PATH . "/src/views/landing.php";
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proyecto Z</title>
    <link rel="icon" type="image/png" href="./src/assets/img/logoSena.png">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/output.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/fonts.css">
    <script src="<?= BASE_URL ?>src/assets/js/sweetalert2.all.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
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

<?php if (isset($_SESSION['usuario_id'])): ?>
<script>
window.addEventListener("pageshow", function () {
    fetch("index.php?page=session_check", { cache: "no-store" })
        .then(response => response.text())
        .then(data => {
            if (data.trim() !== "1") {
                window.location.replace("index.php?page=login");
            }
        });
});
</script>
<?php endif; ?>

</body>
</html>