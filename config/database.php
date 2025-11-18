<?php
// DATOS DE CONEXIÓN EN BYETHOST
$host   = 'sql213.byethost31.com';           // MySQL Host Name
$dbname = 'b31_404288824_proyecto_0';        // MySQL DB Name
$user   = 'b31_404288824';                   // MySQL User Name
$pass   = 'canelA2006.';    // la misma con la que entras al VistaPanel

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode([
        "status"  => "error",
        "mensaje" => "Error al conectar con la base de datos: " . $e->getMessage()
    ]);
    exit;
}
?>