<?php

function getFichas($conexion) {
    $sql = "SELECT id, nombre_programa, nivel_formativo 
            FROM fichas 
            WHERE nombre_programa = 'TGO ADSO'";
    $result = $conexion->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getFichaPorId($conexion, $idFicha) {
    $sql = "SELECT * FROM fichas WHERE id = $idFicha";
    $result = $conexion->query($sql);
    return $result->fetch_assoc();
}

?>
