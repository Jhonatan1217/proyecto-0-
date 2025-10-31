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

    // Obtener una competencia por su ID
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
    public function crear($nombre_competencia, $descripcion) {
        try {
            $sql = "INSERT INTO " . $this->table . " (nombre_competencia, descripcion)
                    VALUES (:nombre_competencia, :descripcion)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':nombre_competencia', $nombre_competencia);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->execute();
            return ["mensaje" => "Competencia creada exitosamente."];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    // Actualizar una competencia existente
    public function actualizar($id_competencia, $nombre_competencia, $descripcion) {
        try {
            $sql = "UPDATE " . $this->table . " 
                    SET nombre_competencia = :nombre_competencia, 
                        descripcion = :descripcion
                    WHERE id_competencia = :id_competencia";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_competencia', $id_competencia);
            $stmt->bindParam(':nombre_competencia', $nombre_competencia);
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

    // Cambiar el estado (activo / inactivo)
    public function cambiarEstado($id_competencia, $nuevoEstado) {
        try {
            if ($nuevoEstado != 0 && $nuevoEstado != 1) {
                throw new Exception("El estado debe ser 1 (activo) o 0 (inactivo).");
            }

            $sql = "UPDATE " . $this->table . " 
                    SET estado = :estado 
                    WHERE id_competencia = :id_competencia";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':estado', $nuevoEstado);
            $stmt->bindParam(':id_competencia', $id_competencia);
            $stmt->execute();
            return ["mensaje" => "Estado de competencia actualizado correctamente."];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    // Listar solo competencias activas
    public function listarActivas() {
        try {
            $sql = "SELECT * FROM " . $this->table . " WHERE estado = 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }
}
?>
