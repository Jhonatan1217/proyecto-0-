<?php
// Clase Rae para manejar operaciones CRUD sobre la tabla 'raes'
class Rae {
    private $conn;
    private $table = "raes";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Funcion para listar todos los RAEs
    public function listar() {
        $sql = "SELECT * FROM " . $this->table;
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Funcion para obtener un RAE por su ID
    public function obtenerPorId($id) {
        $sql = "SELECT * FROM " . $this->table . " WHERE id_rae = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Funcion para crear un nuevo RAE
    public function crear($descripcion, $id_competencia) {
        $sql = "INSERT INTO " . $this->table . " (descripcion, id_competencia)
                VALUES (:descripcion, :id_competencia)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':id_competencia', $id_competencia);
        $stmt->execute();
    }

    // Funcion para actualizar un RAE existente
    public function actualizar($id, $descripcion, $id_competencia) {
        $sql = "UPDATE " . $this->table . " 
                SET descripcion = :descripcion, id_competencia = :id_competencia
                WHERE id_rae = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':id_competencia', $id_competencia);
        $stmt->execute();
    }

    // Funcion para eliminar un RAE
    public function eliminar($id) {
        $sql = "DELETE FROM " . $this->table . " WHERE id_rae = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }

    // Funcion para cambiar el estado de un RAE (activo/inactivo)
    public function cambiarEstado($id, $nuevoEstado) {
        if ($nuevoEstado != 1 && $nuevoEstado != 0) {
            throw new Exception("El estado debe ser 1 (activo) o 0 (inactivo).");
        }

        $sql = "UPDATE " . $this->table . " SET estado = :estado WHERE id_rae = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':estado', $nuevoEstado);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }

    // Funcion para listar RAEs con el nombre de la competencia
    public function listarConCompetencia() {
        $sql = "SELECT r.id_rae, r.descripcion, r.estado, c.nombre_competencia
                FROM raes r
                LEFT JOIN competencias c ON r.id_competencia = c.id_competencia";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
