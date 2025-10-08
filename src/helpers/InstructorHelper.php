<?php

function getInstructores($conexion) {
    $sql = "SELECT id, CONCAT(nombre, ' ', apellido) AS nombre, tipo_instructor 
            FROM instructores";
    $result = $conexion->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getInstructorPorId($conexion, $id) {
    $sql = "SELECT * FROM instructores WHERE id = $id";
    $result = $conexion->query($sql);
    return $result->fetch_assoc();
}

// Valida si el instructor está libre en un rango de horas
function checkDisponibilidad($conexion, $idInstructor, $dia, $horaInicio, $horaFin) {
    $sql = "SELECT * FROM horarios 
            WHERE instructor = $idInstructor 
            AND dia = '$dia' 
            AND (hora_inicio < '$horaFin' AND hora_fin > '$horaInicio')";
    $result = $conexion->query($sql);
    return $result->num_rows == 0; // true = disponible
}

?>
