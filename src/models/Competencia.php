<?php
class Competencia {
    private $conn;
    private $table = "competencias";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Listar todas las competencias
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

    // Obtener una competencia por ID
    public function obtenerPorId($id_competencia) {
        try {
            $sql = "SELECT * FROM " . $this->table . " WHERE id_competencia = :id_competencia";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_competencia', $id_competencia);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    // Crear una nueva competencia
    public function crear($descripcion) {
        try {
            $sql = "INSERT INTO " . $this->table . " (descripcion) VALUES (:descripcion)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->execute();
            return ["mensaje" => "Competencia creada exitosamente."];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    // Actualizar una competencia existente
    public function actualizar($id_competencia, $descripcion) {
        try {
            $sql = "UPDATE " . $this->table . " 
                    SET descripcion = :descripcion 
                    WHERE id_competencia = :id_competencia";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_competencia', $id_competencia);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->execute();
            return ["mensaje" => "Competencia actualizada correctamente."];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    // Eliminar una competencia
    public function eliminar($id_competencia) {
        try {
            $sql = "DELETE FROM " . $this->table . " WHERE id_competencia = :id_competencia";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_competencia', $id_competencia);
            $stmt->execute();
            return ["mensaje" => "Competencia eliminada exitosamente."];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }
}
?>
