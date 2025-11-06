<?php
class Programa {
    private $conn;
    private $table = "programas";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Listar todos los programas
    public function listar() {
    try {
        $sql = "SELECT id_programa, nombre_programa 
                FROM " . $this->table . "
                WHERE estado = 1
                ORDER BY nombre_programa ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return ["error" => $e->getMessage()];
    }
}



    // Obtener un programa por su ID
    public function obtenerPorId($id_programa) {
        try {
            $sql = "SELECT * FROM " . $this->table . " WHERE id_programa = :id_programa";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_programa', $id_programa);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    // Crear un nuevo programa
    public function crear($nombre_programa, $descripcion, $duracion) {
        try {
            $sql = "INSERT INTO " . $this->table . " (nombre_programa, descripcion, duracion)
                    VALUES (:nombre_programa, :descripcion, :duracion)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':nombre_programa', $nombre_programa);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':duracion', $duracion);
            $stmt->execute();
            return ["mensaje" => "Programa creado exitosamente."];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    // Actualizar un programa existente
    public function actualizar($id_programa, $nombre_programa, $descripcion, $duracion) {
        try {
            $sql = "UPDATE " . $this->table . " 
                    SET nombre_programa = :nombre_programa,
                        descripcion = :descripcion,
                        duracion = :duracion
                    WHERE id_programa = :id_programa";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_programa', $id_programa);
            $stmt->bindParam(':nombre_programa', $nombre_programa);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':duracion', $duracion);
            $stmt->execute();
            return ["mensaje" => "Programa actualizado correctamente."];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    // Eliminar un programa
    public function eliminar($id_programa) {
        try {
            $sql = "DELETE FROM " . $this->table . " WHERE id_programa = :id_programa";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_programa', $id_programa);
            $stmt->execute();
            return ["mensaje" => "Programa eliminado exitosamente."];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }
}
?>
