<?php
function getHorarioFicha($conexion, $idFicha) {
    // Por ahora ignoramos $idFicha porque la tabla no lo tiene
    $sql = "SELECT dia, hora_inicio, hora_fin FROM horarios";

    $stmt = $conexion->prepare($sql);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);


    
}

function getHorarioInstructor($conexion, $idInstructor) {
    // Ejemplo temporal hasta tener relaciones entre tablas
    $sql = "SELECT dia, hora_inicio, hora_fin FROM horarios";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
