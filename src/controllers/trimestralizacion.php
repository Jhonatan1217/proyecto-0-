<?php
class Trimestralizacion {
    private $conn;
    private $table = "trimestralizacion";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function listar() {
        $sql = "SELECT * FROM $this->table";
        return $this->conn->query($sql);
    }

    public function crear($id_horario) {
        $sql = "INSERT INTO $this->table (id_horario) VALUES ($id_horario)";
        return $this->conn->query($sql);
    }

    public function eliminar($id) {
        $sql = "DELETE FROM $this->table WHERE id_trimestral=$id";
        return $this->conn->query($sql);
    }
}
?>
