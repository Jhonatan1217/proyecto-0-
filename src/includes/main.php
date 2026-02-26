<?php
// Página solicitada (por defecto 'landing')
$page = $_GET['page'] ?? 'landing';

// Evitar rutas maliciosas
$page = basename($page);

<<<<<<< HEAD
// Ruta de la vista
$file = __DIR__ . "/../views/$page.php";

// Cargar vista o mostrar mensaje de error
=======
// Ruta absoluta a la vista
$file = BASE_PATH . "/src/views/$page.php";

// Si existe la vista → cargarla
>>>>>>> bb6242926c655bf511d9923b149d0de4cba14a2b
if (file_exists($file)) {
    include $file;
} else {
    echo "<p style='color:red; text-align:center; padding:2rem;'>
            La página solicitada <strong>$page</strong> no existe.
          </p>";
<<<<<<< HEAD
}
?>
=======
}
>>>>>>> bb6242926c655bf511d9923b149d0de4cba14a2b
