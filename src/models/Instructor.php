<?php
// Clase Instructor para manejar operaciones CRUD sobre la tabla 'instructores'
class Instructor {
    // Conexión a la base de datos
    private $conn;
    // Nombre de la tabla
    private $table = "instructores";

    // Constructor que recibe la conexión a la base de datos
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
        $sql = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Crear un nuevo instructor
    public function crear($nombre, $apellido, $tipo) {
        $sql = "INSERT INTO " . $this->table . " (nombre, apellido, tipo_instructor)
                VALUES (:nombre, :apellido, :tipo)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':apellido', $apellido);
        $stmt->bindParam(':tipo', $tipo);
        $stmt->execute();
    }

    // Actualizar un instructor existente
    public function actualizar($id, $nombre, $apellido, $tipo) {
        $sql = "UPDATE " . $this->table . " 
                SET nombre = :nombre, apellido = :apellido, tipo_instructor = :tipo
                WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':apellido', $apellido);
        $stmt->bindParam(':tipo', $tipo);
        $stmt->execute();
    }

    // Eliminar un instructor por su ID
    public function eliminar($id) {
        $sql = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }

    //Cambiar el estado (activo/inactivo)
    public function cambiarEstado($id, $nuevoEstado) {
        // Aseguramos que el estado sea 1 (activo) o 0 (inactivo)
        if ($nuevoEstado != 1 && $nuevoEstado != 0) {
            throw new Exception("El estado debe ser 1 (activo) o 0 (inactivo).");
        }

        $sql = "UPDATE " . $this->table . " SET estado = :estado WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':estado', $nuevoEstado);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }
}
?>