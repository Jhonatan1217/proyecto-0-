<?php

$pagina = $_GET['page'] ?? 'src/views/landing';

/* Seguridad básica */
$pagina = str_replace(['..', './'], '', $pagina);

/* Construimos la ruta */
$ruta = BASE_PATH . '/' . $pagina . '.php';

/* Si existe el archivo lo cargamos */
if (file_exists($ruta)) {
    require $ruta;
} else {
    require BASE_PATH . '/src/views/404.php';
}