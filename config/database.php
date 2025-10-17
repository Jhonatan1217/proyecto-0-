<?php
$host = 'localhost';
$dbname = 'proyecto_0';
$user = 'root';
$pass = ''; //contraseña de la base de datos (Es la de tu base de datos)

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // No imprimir nada aquí: el controlador se encargará de responder en JSON
} catch (PDOException $e) {
    // En caso de error, devolver un JSON de error y detener ejecución
    header('Content-Type: application/json');
    echo json_encode([
        "status" => "error",
        "mensaje" => "Error al conectar con la base de datos: " . $e->getMessage()
    ]);
    exit;
}
?>
