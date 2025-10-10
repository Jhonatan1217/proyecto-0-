<?php
function getCompetencias($conn) { // Funcion para extraer la informacion de la tabla competencias
    try {
        $sql = "SELECT * FROM competencias"; // Seleccion de tabla para extraer datos de la BD
        $stmt = $conn->prepare($sql); // Conexion
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Error al obtener competencias: " . $e->getMessage()); // Mensaje de error
    }
}
?>
