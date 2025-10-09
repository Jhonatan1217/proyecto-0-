<?php
function getFichas($conn) { // Funcion para extraer la informacion de la tabla fichas
    try {
        $sql = "SELECT * FROM fichas"; // Seleccion de tabla para extraer datos de la BD
        $stmt = $conn->prepare($sql); // Conexion
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Error al obtener fichas: " . $e->getMessage()); // Mensaje de Error
    }
}
?>
