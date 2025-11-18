<?php
$host = 'localhost';
$dbname = 'proyecto_0';
$user = 'root';
$pass = ''; 

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode([
        "status" => "error",
        "mensaje" => "Error al conectar con la base de datos: " . $e->getMessage()
    ]);
    exit;
}
?>
    