<?php
include(__DIR__ . "/../../config/database.php");


// CREAR HORARIO
if (isset($_POST['crear'])) {
    $dia = $_POST['dia'];
    $hora_inicio = $_POST['hora_inicio'];
    $hora_fin = $_POST['hora_fin'];
    $id_instructor = $_POST['id_instructor'];
    $id_ficha = $_POST['id_ficha'];

    try {
        $sql = "INSERT INTO horarios (dia, hora_inicio, hora_fin, id_instructor, id_ficha)
                VALUES (:dia, :hora_inicio, :hora_fin, :id_instructor, :id_ficha)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':dia', $dia);
        $stmt->bindParam(':hora_inicio', $hora_inicio);
        $stmt->bindParam(':hora_fin', $hora_fin);
        $stmt->bindParam(':id_instructor', $id_instructor);
        $stmt->bindParam(':id_ficha', $id_ficha);
        $stmt->execute();

        echo "<script>alert(' Horario creado correctamente');</script>";
    } catch (PDOException $e) {
        echo "<script>alert(' Error en la base de datos: " . $e->getMessage() . "');</script>";
    }
}
?>
