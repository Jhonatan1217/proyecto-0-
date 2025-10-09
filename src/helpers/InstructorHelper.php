<?php
function getInstructores($conn) { // Funcion para extraer la informacion de la tabla instructores
    try {
        $sql = "SELECT id_instructor, nombre_instructor, apellido_instructor, tipo_instructor FROM instructores"; // Imprimir los datos de la tabla
        $stmt = $conn->prepare($sql); // Conexion
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Error al obtener instructores: " . $e->getMessage()); // Mensaje de error
    }
}
?>
