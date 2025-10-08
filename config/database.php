<?php
$host = 'localhost';
$dbname = 'proyecto_0';
$user = 'root';
$pass = 'Samimi2237';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);

    // Validaciones seguun la respuesta
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Si la conexion es exitosa
    echo "Conexión exitosa a la base de datos MySQL.";
} catch (PDOException $e) {
    // Si hay algun problema en la base de datos
    echo "Error al conectar con la base de datos: " . $e->getMessage();
}
?>