<?php
// Clase Area para manejar operaciones CRUD sobre la tabla 'areas'
class Area {
    private $conn;
    private $table = "areas";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Funcion para listar todas las areas
    public function listar() {
        $sql = "SELECT * FROM " . $this->table;
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Funcion para obtener una area por su ID
    public function obtenerPorId($id_area) {
        $sql = "SELECT * FROM " . $this->table . " WHERE id_area = :id_area";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_area', $id_area);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Funcion para crear una nueva area
    public function crear($nombre_area) {
        $sql = "INSERT INTO " . $this->table . " (nombre_area) VALUES (:nombre_area)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nombre_area', $nombre_area);
        $stmt->execute();
    }

    // Funcion para actualizar una area existente
    public function actualizar($id_area, $nombre_area) {
        $sql = "UPDATE " . $this->table . " 
                SET nombre_area = :nombre_area
                WHERE id_area = :id_area";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_area', $id_area);
        $stmt->bindParam(':nombre_area', $nombre_area);
        $stmt->execute();
    }

    // Funcion para eliminar una area por su ID
    public function eliminar($id_area) {
        $sql = "DELETE FROM " . $this->table . " WHERE id_area = :id_area";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_area', $id_area);
        $stmt->execute();
    }

    // Funcion para cambiar el estado de una area (activo/inactivo)
    public function cambiarEstado($id_area, $nuevoEstado) {
        if ($nuevoEstado != 1 && $nuevoEstado != 0) {
            throw new Exception("El estado debe ser 1 (activo) o 0 (inactivo).");
        }
        
        $sql = "UPDATE " . $this->table . " 
                SET estado = :estado 
                WHERE id_area = :id_area";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':estado', $nuevoEstado);
        $stmt->bindParam(':id_area', $id_area);
        $stmt->execute();
    }
}
?>
