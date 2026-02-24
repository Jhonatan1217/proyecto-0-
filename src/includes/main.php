<?php

$page = $_GET['page'] ?? 'landing';
$page = basename($page);

// Páginas públicas (no requieren login)
$paginas_publicas = ['login', 'landing'];

// Si NO está logueado y la página NO es pública → redirigir
if (!isset($_SESSION['usuario_id']) && !in_array($page, $paginas_publicas)) {
    header("Location: index.php?page=login");
    exit;
}

// Ruta de la vista
$file = __DIR__ . "/../views/$page.php";

if (file_exists($file)) {
    include $file;
} else {
    echo "<p style='color:red; text-align:center; padding:2rem;'>
            La página solicitada <strong>$page</strong> no existe.
          </p>";
}
?>