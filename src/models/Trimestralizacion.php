<?php
class Trimestralizacion {
    private $conn;
    private $table = "trimestralizacion";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function listar() {
        $sql = "SELECT t.*, h.dia, h.hora_inicio, h.hora_fin 
                FROM trimestralizacion t
                INNER JOIN horarios h ON t.id_horario = h.id_horario";
        return $this->conn->query($sql);
    }

    public function obtenerPorId($id) {
        $sql = "SELECT * FROM $this->table WHERE id_trimestral = $id";
        return $this->conn->query($sql);
    }

    public function crear($id_horario) {
        $sql = "INSERT INTO $this->table (id_horario) VALUES ($id_horario)";
        return $this->conn->query($sql);
    }

    public function eliminar($id) {
        $sql = "DELETE FROM $this->table WHERE id_trimestral = $id";
        return $this->conn->query($sql);
    }
}
?>