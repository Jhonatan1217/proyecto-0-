<?php
class Ficha {
    private $conn;
    private $table = "fichas";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Listar todas las fichas
    public function listar() {
        try {
            $sql = "SELECT * FROM " . $this->table;
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    // Obtener ficha por ID
    public function obtenerPorId($id_ficha) {
        try {
            $sql = "SELECT * FROM " . $this->table . " WHERE id_ficha = :id_ficha";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_ficha', $id_ficha);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    // Crear una nueva ficha (solo si agregas más columnas)
    public function crear($id_ficha) {
        try {
            $sql = "INSERT INTO " . $this->table . " (id_ficha) VALUES (:id_ficha)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_ficha', $id_ficha);
            $stmt->execute();
            return ["mensaje" => "Ficha creada exitosamente."];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    // Eliminar ficha
    public function eliminar($id_ficha) {
        try {
            $sql = "DELETE FROM " . $this->table . " WHERE id_ficha = :id_ficha";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_ficha', $id_ficha);
            $stmt->execute();
            return ["mensaje" => "Ficha eliminada correctamente."];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }
}
?>
