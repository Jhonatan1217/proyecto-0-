<?php
class Zona {
    private $conn;
    private $table = 'zonas';

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Listar todas las zonas
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

    // Obtener una zona por su ID
    public function obtenerPorId($id_zona) {
        try {
            $sql = "SELECT * FROM " . $this->table . " WHERE id_zona = :id_zona";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_zona', $id_zona, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    // Crear una nueva zona
    public function crear($id_area = null, $estado = 1) {
        try {
            $sql = "INSERT INTO " . $this->table . " (id_area, estado) VALUES (:id_area, :estado)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_area', $id_area);
            $stmt->bindParam(':estado', $estado, PDO::PARAM_INT);
            $stmt->execute();

            return [
                "mensaje" => "Zona creada exitosamente.",
                "id_zona" => $this->conn->lastInsertId()
            ];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    // Actualizar una zona (opcional)
    public function actualizar($id_zona, $id_area, $estado) {
        try {
            $sql = "UPDATE " . $this->table . " 
                    SET id_area = :id_area, estado = :estado 
                    WHERE id_zona = :id_zona";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_area', $id_area);
            $stmt->bindParam(':estado', $estado, PDO::PARAM_INT);
            $stmt->bindParam(':id_zona', $id_zona, PDO::PARAM_INT);
            $stmt->execute();

            return ["mensaje" => "Zona actualizada correctamente."];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    // Eliminar una zona
    public function eliminar($id_zona) {
        try {
            $sql = "DELETE FROM " . $this->table . " WHERE id_zona = :id_zona";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_zona', $id_zona, PDO::PARAM_INT);
            $stmt->execute();
            return ["mensaje" => "Zona eliminada correctamente."];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    //Cambiar el estado (activo/inactivo)
    public function cambiarEstado($id_zona, $nuevo_estado) {
        try {
            if ($nuevo_estado != 1 && $nuevo_estado != 0) {
                throw new Exception("El estado debe ser 1 (activo) o 0 (inactivo).");
            }

            $sql = "UPDATE " . $this->table . " 
                    SET estado = :estado 
                    WHERE id_zona = :id_zona";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':estado', $nuevo_estado, PDO::PARAM_INT);
            $stmt->bindParam(':id_zona', $id_zona, PDO::PARAM_INT);
            $stmt->execute();

            return ["mensaje" => "Estado de la zona actualizado correctamente."];
        } catch (Exception $e) {
            return ["error" => $e->getMessage()];
        }
    }
}
?>