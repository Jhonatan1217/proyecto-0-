<?php
session_start();

session_unset();
session_destroy();

require_once __DIR__ . '/../../index.php'; // Solo si necesitas BASE_URL
header("Location: " . BASE_URL . "index.php?page=login");
exit;