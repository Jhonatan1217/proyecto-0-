<?php

$page = $_GET['page'] ?? 'landing';
$page = basename($page);

// Ruta absoluta a la vista
$file = BASE_PATH . "/src/views/$page.php";

// Si existe la vista → cargarla
if (file_exists($file)) {
    include $file;
} else {
    echo "<p style='color:red; text-align:center; padding:2rem;'>
            La página solicitada <strong>$page</strong> no existe.
          </p>";
}