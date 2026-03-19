<?php
// Clase Area para manejar operaciones CRUD sobre la tabla 'area'
class Area {
    private $conn;
    private $table = "area"; // Cambiado de "areas" a "area"

    public function __construct($db) {
        $this->conn = $db;
    }

    // Funcion para listar todas las areas
    public function listar() {
        try {
            $sql = "SELECT * FROM " . $this->table . " ORDER BY nombre_area ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en listar áreas: " . $e->getMessage());
            return [];
        }
    }

    // Funcion para obtener una area por su ID
    public function obtenerPorId($id_area) {
        try {
            $sql = "SELECT * FROM " . $this->table . " WHERE id_area = :id_area";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_area', $id_area, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtener área por ID: " . $e->getMessage());
            return null;
        }
    }

    // Funcion para crear una nueva area
    public function crear($nombre_area) {
        try {
            // Verificar si ya existe un área con ese nombre
            $sql_check = "SELECT COUNT(*) FROM " . $this->table . " WHERE nombre_area = :nombre_area";
            $stmt_check = $this->conn->prepare($sql_check);
            $stmt_check->bindParam(':nombre_area', $nombre_area);
            $stmt_check->execute();
            
            if ($stmt_check->fetchColumn() > 0) {
                throw new Exception("Ya existe un área con ese nombre");
            }

            $sql = "INSERT INTO " . $this->table . " (nombre_area, estado) VALUES (:nombre_area, 1)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':nombre_area', $nombre_area);
            
            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            } else {
                $error = $stmt->errorInfo();
                throw new Exception("Error al crear el área: " . $error[2]);
            }
        } catch (PDOException $e) {
            error_log("Error en crear área: " . $e->getMessage());
            throw new Exception("Error en la base de datos al crear el área");
        }
    }

    // Funcion para actualizar una area existente
    public function actualizar($id_area, $nombre_area) {
        try {
            // Verificar si ya existe otra área con ese nombre
            $sql_check = "SELECT COUNT(*) FROM " . $this->table . " 
                         WHERE nombre_area = :nombre_area AND id_area != :id_area";
            $stmt_check = $this->conn->prepare($sql_check);
            $stmt_check->bindParam(':nombre_area', $nombre_area);
            $stmt_check->bindParam(':id_area', $id_area, PDO::PARAM_INT);
            $stmt_check->execute();
            
            if ($stmt_check->fetchColumn() > 0) {
                throw new Exception("Ya existe otra área con ese nombre");
            }

            $sql = "UPDATE " . $this->table . " 
                    SET nombre_area = :nombre_area
                    WHERE id_area = :id_area";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_area', $id_area, PDO::PARAM_INT);
            $stmt->bindParam(':nombre_area', $nombre_area);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en actualizar área: " . $e->getMessage());
            throw new Exception("Error al actualizar el área");
        }
    }

    // Funcion para eliminar una area por su ID
    public function eliminar($id_area) {
        try {
            // Verificar si el área tiene zonas relacionadas
            $sql_check = "SELECT COUNT(*) FROM zonas WHERE id_area = :id_area";
            $stmt_check = $this->conn->prepare($sql_check);
            $stmt_check->bindParam(':id_area', $id_area, PDO::PARAM_INT);
            $stmt_check->execute();
            
            if ($stmt_check->fetchColumn() > 0) {
                throw new Exception("No se puede eliminar el área porque tiene zonas asociadas");
            }

            // Verificar si hay instructores usando esta área
            $sql_check2 = "SELECT COUNT(*) FROM usuarios WHERE id_area = :id_area";
            $stmt_check2 = $this->conn->prepare($sql_check2);
            $stmt_check2->bindParam(':id_area', $id_area, PDO::PARAM_INT);
            $stmt_check2->execute();
            
            if ($stmt_check2->fetchColumn() > 0) {
                throw new Exception("No se puede eliminar el área porque tiene instructores asociados");
            }

            $sql = "DELETE FROM " . $this->table . " WHERE id_area = :id_area";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_area', $id_area, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en eliminar área: " . $e->getMessage());
            throw new Exception("Error al eliminar el área");
        }
    }

    // Funcion para cambiar el estado de una area (activo/inactivo)
    public function cambiarEstado($id_area, $nuevoEstado) {
        try {
            if ($nuevoEstado != 1 && $nuevoEstado != 0) {
                throw new Exception("El estado debe ser 1 (activo) o 0 (inactivo).");
            }
            
            $sql = "UPDATE " . $this->table . " 
                    SET estado = :estado 
                    WHERE id_area = :id_area";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':estado', $nuevoEstado, PDO::PARAM_INT);
            $stmt->bindParam(':id_area', $id_area, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en cambiar estado de área: " . $e->getMessage());
            throw new Exception("Error al cambiar el estado del área");
        }
    }
}
?>