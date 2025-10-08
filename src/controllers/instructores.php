<?php
include(__DIR__ . "/../../config/database.php"); 

// CREAR INSTRUCTOR
if (isset($_POST['crear'])) {
    $nombre = $_POST['nombre_instructor'];
    $apellido = $_POST['apellido_instructor'];
    $tipo = $_POST['tipo_instructor'];

    try {
        // Solo los campos que existen en tu tabla
        $sql = "INSERT INTO instructores (nombre_instructor, apellido_instructor, tipo_instructor)
                VALUES (:nombre, :apellido, :tipo)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':apellido', $apellido);
        $stmt->bindParam(':tipo', $tipo);
        $stmt->execute();

        echo "<script>alert('✅ Instructor registrado correctamente');</script>";
    } catch (PDOException $e) {
        echo "<script>alert('⚠️ Error en la base de datos: " . $e->getMessage() . "');</script>";
    }
}
?>