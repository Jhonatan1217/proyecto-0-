<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Evitar cache al usar botón atrás
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

// Verificar autenticación
if (!isset($_SESSION['usuario_id'])) {
    header("Location: /index.php?page=login");
    exit;
}