<?php
function getTrimestralizaciones($conn) { // Funcion para extraer la informacion de la tabla Trimestralizaciones
    try {
        // Imprimir informacion de la tabla
        $sql = "SELECT t.id_trimestral, h.dia, h.hora_inicio, h.hora_fin,
                    CONCAT(i.nombre_instructor, ' ', i.apellido_instructor) AS instructor
                FROM trimestralizacion t
                INNER JOIN horarios h ON t.id_horario = h.id_horario
                INNER JOIN instructores i ON h.id_instructor = i.id_instructor";
        $stmt = $conn->prepare($sql); // Conexion
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Error al obtener trimestralizaciones: " . $e->getMessage()); // Mensaje de error
    }
}
?>
