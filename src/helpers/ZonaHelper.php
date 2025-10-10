<?php
function getZonas($conn) { // Funciona para extraer la informacion de la tabla zonas
    try {
        $sql = "SELECT * FROM zonas"; // Imprimir la tabla zonas
        $stmt = $conn->prepare($sql); // Conexion
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Error al obtener zonas: " . $e->getMessage()); // Mensaje de error
    }
}
?>
