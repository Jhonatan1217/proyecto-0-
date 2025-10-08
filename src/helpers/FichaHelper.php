<?php
function getFichas($conn) {
    try {
        $sql = "SELECT * FROM fichas";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Error al obtener fichas: " . $e->getMessage());
    }
}
?>
