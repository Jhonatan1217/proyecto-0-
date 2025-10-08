<?php

function getCompetencias($conexion) {
    $sql = "SELECT id, nombre_competencia, tipo_competencia FROM competencias";
    $result = $conexion->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getCompetenciasPorInstructor($conexion, $idInstructor) {
    $sql = "SELECT c.id, c.nombre_competencia, c.tipo_competencia
            FROM competencias c
            INNER JOIN instructor_competencia ic ON ic.competencia = c.id
            WHERE ic.instructor = $idInstructor";
    $result = $conexion->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

?>
