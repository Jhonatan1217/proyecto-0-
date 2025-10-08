<?php
class Horario {
    private $conn;
    private $table = "horarios";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function listar() {
        $sql = "SELECT * FROM $this->table";
        return $this->conn->query($sql);
    }

    public function crear($dia, $inicio, $fin, $id_zona, $id_ficha, $id_instructor) {
        $sql = "INSERT INTO $this->table (dia, hora_inicio, hora_fin, id_zona, id_ficha, id_instructor)
                VALUES ('$dia', '$inicio', '$fin', $id_zona, $id_ficha, $id_instructor)";
        return $this->conn->query($sql);
    }

    public function actualizar($id, $dia, $inicio, $fin, $id_zona, $id_ficha, $id_instructor) {
        $sql = "UPDATE $this->table 
                SET dia='$dia', hora_inicio='$inicio', hora_fin='$fin', id_zona=$id_zona, 
                    id_ficha=$id_ficha, id_instructor=$id_instructor 
                WHERE id_horario=$id";
        return $this->conn->query($sql);
    }

    public function eliminar($id) {
        $sql = "DELETE FROM $this->table WHERE id_horario=$id";
        return $this->conn->query($sql);
    }
}
?>
