<?php
function getTrimestralizaciones($conn) {
    try {
        $sql = "SELECT t.id_trimestral, h.dia, h.hora_inicio, h.hora_fin,
                    CONCAT(i.nombre_instructor, ' ', i.apellido_instructor) AS instructor
                FROM trimestralizacion t
                INNER JOIN horarios h ON t.id_horario = h.id_horario
                INNER JOIN instructores i ON h.id_instructor = i.id_instructor";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Error al obtener trimestralizaciones: " . $e->getMessage());
    }
}
?>
