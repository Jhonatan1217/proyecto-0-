<?php
include(__DIR__ . "/../../config/database.php");


if (isset($_POST['crear'])) {
    $nivel = $_POST['nivel_formativo'];
    $nombre = $_POST['nombre_programa'];

    try {
        $sql = "INSERT INTO fichas (nivel_formativo, nombre_programa)
                VALUES (:nivel, :nombre)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':nivel', $nivel, PDO::PARAM_STR);
        $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
        $stmt->execute();

        echo "<script>alert(' Ficha creada correctamente');</script>";
    } catch (PDOException $e) {
        echo "<script>alert(' Error al crear la ficha: " . $e->getMessage() . "');</script>";
    }
}




?>
