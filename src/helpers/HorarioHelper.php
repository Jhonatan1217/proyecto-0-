<?php
function getHorarios($conn) { // Funcion para extraer informacion de la tabla horarios
    try {
        // Imprimir datos de la tabla 
        $sql = "SELECT h.id_horario, h.dia, h.hora_inicio, h.hora_fin,
                    CONCAT(i.nombre_instructor, ' ', i.apellido_instructor) AS instructor,
                    f.id_ficha, z.id_zona
                FROM horarios h
                INNER JOIN instructores i ON h.id_instructor = i.id_instructor
                INNER JOIN fichas f ON h.id_ficha = f.id_ficha
                INNER JOIN zonas z ON h.id_zona = z.id_zona";
        $stmt = $conn->prepare($sql); // Conexion
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Error al obtener horarios: " . $e->getMessage()); // Mensaje de Error
    }
}
?>
