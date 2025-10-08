<?php
function getCompetencias($conn) {
    try {
        $sql = "SELECT * FROM competencias";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Error al obtener competencias: " . $e->getMessage());
    }
}
?>
