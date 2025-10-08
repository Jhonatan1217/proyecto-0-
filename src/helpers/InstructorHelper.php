<?php

function getInstructores($conn) {
    $sql = "SELECT id, CONCAT(nombre, ' ', apellido) AS nombre, tipo_instructor 
            FROM instructores";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getInstructorPorId($conn, $id) {
    $sql = "SELECT * FROM instructores WHERE id = $id";
    $result = $conn->query($sql);
    return $result->fetch_assoc();
}

// Valida si el instructor está libre en un rango de horas
function checkDisponibilidad($conn, $idInstructor, $dia, $horaInicio, $horaFin) {
    $sql = "SELECT * FROM horarios 
            WHERE instructor = $idInstructor 
            AND dia = '$dia' 
            AND (hora_inicio < '$horaFin' AND hora_fin > '$horaInicio')";
    $result = $conn->query($sql);
    return $result->num_rows == 0; // true = disponible
}

?>
