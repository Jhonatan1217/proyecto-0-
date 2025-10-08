<?php
class Zona {
    private $conn;
    private $table = "zonas";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function listar() {
        $sql = "SELECT * FROM $this->table";
        return $this->conn->query($sql);
    }

    public function crear() {
        $sql = "INSERT INTO $this->table VALUES (NULL)";
        return $this->conn->query($sql);
    }

    public function eliminar($id) {
        $sql = "DELETE FROM $this->table WHERE id_zona=$id";
        return $this->conn->query($sql);
    }
}
?>
