<?php
class Trimestralizacion {
    private $conn;
    private $table = "trimestralizacion";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Listar todos los registros
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

    // Obtener un registro por su ID
    public function obtenerPorId($id_trimestral) {
        try {
            $sql = "SELECT * FROM " . $this->table . " WHERE id_trimestral = :id_trimestral";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_trimestral', $id_trimestral, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    // Crear nuevo registro
    public function crear($id_horario) {
        try {
            $sql = "INSERT INTO " . $this->table . " (id_horario) VALUES (:id_horario)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_horario', $id_horario, PDO::PARAM_INT);
            $stmt->execute();
            return [
                "mensaje" => "Trimestralización creada exitosamente.",
                "id_trimestral" => $this->conn->lastInsertId()
            ];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    // Eliminar registro
    public function eliminar($id_trimestral) {
        try {
            $sql = "DELETE FROM " . $this->table . " WHERE id_trimestral = :id_trimestral";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_trimestral', $id_trimestral, PDO::PARAM_INT);
            $stmt->execute();
            return ["mensaje" => "Trimestralización eliminada correctamente."];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }
}
?>
