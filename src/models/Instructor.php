<?php
class Instructor {
    private $conn;
    private $table = "instructores";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Listar todos los instructores
    public function listar() {
        $sql = "SELECT * FROM " . $this->table;
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener un instructor por su ID
    public function obtenerPorId($id) {
        $sql = "SELECT * FROM " . $this->table . " WHERE id_instructor = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Crear un nuevo instructor
    public function crear($nombre, $tipo) {
        $sql = "INSERT INTO " . $this->table . " (nombre_instructor, tipo_instructor)
                VALUES (:nombre, :tipo)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':tipo', $tipo);
        $stmt->execute();
    }

    // Actualizar un instructor existente
    public function actualizar($id, $nombre, $tipo) {
        $sql = "UPDATE " . $this->table . " 
                SET nombre_instructor = :nombre, tipo_instructor = :tipo
                WHERE id_instructor = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':tipo', $tipo);
        $stmt->execute();
    }

    // Eliminar un instructor
    public function eliminar($id) {
        $sql = "DELETE FROM " . $this->table . " WHERE id_instructor = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }

    // Cambiar el estado (activo/inactivo)
    public function cambiarEstado($id, $nuevoEstado) {
        if ($nuevoEstado != 1 && $nuevoEstado != 0) {
            throw new Exception("El estado debe ser 1 (activo) o 0 (inactivo).");
        }

        $sql = "UPDATE " . $this->table . " SET estado = :estado WHERE id_instructor = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':estado', $nuevoEstado);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }
}
?>