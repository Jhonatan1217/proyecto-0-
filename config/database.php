<?php
// DATOS DE CONEXIÓN CON LA BASE DE DATOS
$host   = 'localhost';           // MySQL Host Name
$dbname = 'proyecto_z';        // MySQL DB Name
$user   = 'root';                   // MySQL User Name (SIN TAB)
$pass   = '';                    // tu contraseña del vPanel

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
        