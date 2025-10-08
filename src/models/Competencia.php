<?php
class Competencia {
    private $conn;
    private $table = "competencias";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function listar() {
        $sql = "SELECT * FROM $this->table";
        return $this->conn->query($sql);
    }

    public function obtenerPorId($id) {
        $sql = "SELECT * FROM $this->table WHERE id_competencia = $id";
        return $this->conn->query($sql);
    }

    public function crear($descripcion) {
        $sql = "INSERT INTO $this->table (descripcion) VALUES ('$descripcion')";
        return $this->conn->query($sql);
    }

    public function actualizar($id, $descripcion) {
        $sql = "UPDATE $this->table SET descripcion = '$descripcion' WHERE id_competencia = $id";
        return $this->conn->query($sql);
    }

    public function eliminar($id) {
        $sql = "DELETE FROM $this->table WHERE id_competencia = $id";
        return $this->conn->query($sql);
    }
}
?>
