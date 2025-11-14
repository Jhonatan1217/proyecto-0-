<?php
$host = 'localhost';
$dbname = 'proyecto_0';
$user = 'root';
<<<<<<< HEAD
$pass = ''; //contraseña de la base de datos
=======
$pass = '123456'; //contraseña de la base de datos
>>>>>>> 7d71f0f87eb472701a86af3b04589c8ed2eaedb6

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
    