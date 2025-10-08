<?php
function getZonas($conn) {
    try {
        $sql = "SELECT * FROM zonas";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Error al obtener zonas: " . $e->getMessage());
    }
}
?>
