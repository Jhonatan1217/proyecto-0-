<?php

$page = $_GET['page'] ?? 'landing';
$page = basename($page);

$cargo = $_SESSION['cargo'] ?? null;

/* BLOQUEO POR ROL */
if ($cargo === 'INSTRUCTOR' && $page === 'academicos' && ($_GET['tab'] ?? '') === 'upload') {
    header("Location: index.php?page=register_tables");
    exit;
}

// Ruta absoluta a la vista
$file = BASE_PATH . "/src/views/$page.php";

if (file_exists($file)) {
    include $file;
} else {
    echo "<p style='color:red;text-align:center;padding:2rem;'>
            La página solicitada <strong>$page</strong> no existe.
          </p>";
}