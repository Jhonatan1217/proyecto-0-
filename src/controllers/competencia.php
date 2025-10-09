<?php
include("database.php");
// INSERTAR
if (isset($_POST['guardar'])) {
    $descripcion = $_POST['descripcion'];
    $tipo = $_POST['tipo'];
    $nombre = $_POST['nombre_competencia'];

    $sql = "INSERT INTO competencias (descripcion, tipo, nombre_competencia)
            VALUES ('$descripcion', '$tipo', '$nombre')";
    mysqli_query($conexion, $sql);
    echo "Competencia guardada correctamente";
}

// MOSTRAR
$resultado = mysqli_query($conexion, "SELECT * FROM competencias");
    echo "<h3>Lista de Competencias</h3>";
    while ($fila = mysqli_fetch_assoc($resultado)) {
        echo $fila['id'] . " - " . $fila['nombre_competencia'] . " - " . $fila['tipo'] . "<br>";
}
?>