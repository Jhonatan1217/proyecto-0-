<?php
function getInstructores($conn) {
    try {
        $sql = "SELECT id_instructor, nombre_instructor, apellido_instructor, tipo_instructor FROM instructores";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Error al obtener instructores: " . $e->getMessage());
    }
}
?>
