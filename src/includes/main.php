<?php
// Página solicitada (por defecto 'home')
$page = $_GET['page'] ?? 'home';

// Ruta de la vista
$file = __DIR__ . "/../views/$page.php";

// Cargar vista o mostrar mensaje de error
if (file_exists($file)) {
    include $file;
} else {
    echo "<p style='color:red; text-align:center; padding:2rem;'>
            La página solicitada <strong>$page</strong> no existe.
          </p>";
}
?>
