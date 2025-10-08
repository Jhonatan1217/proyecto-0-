<?php
class Horario {
    private $conn;
    private $table = "horarios";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function listar() {
        $sql = "SELECT h.*, 
                       i.nombre_instructor, 
                       i.apellido_instructor, 
                       z.id_zona, 
                       f.id_ficha
                FROM horarios h
                INNER JOIN instructores i ON h.id_instructor = i.id_instructor
                INNER JOIN zonas z ON h.id_zona = z.id_zona
                INNER JOIN fichas f ON h.id_ficha = f.id_ficha";
        return $this->conn->query($sql);
    }

    public function obtenerPorId($id) {
        $sql = "SELECT * FROM $this->table WHERE id_horario = $id";
        return $this->conn->query($sql);
    }

    public function crear($dia, $hora_inicio, $hora_fin, $id_zona, $id_ficha, $id_instructor) {
        $sql = "INSERT INTO $this->table (dia, hora_inicio, hora_fin, id_zona, id_ficha, id_instructor)
                VALUES ('$dia', '$hora_inicio', '$hora_fin', $id_zona, $id_ficha, $id_instructor)";
        return $this->conn->query($sql);
    }

    public function actualizar($id, $dia, $hora_inicio, $hora_fin, $id_zona, $id_ficha, $id_instructor) {
        $sql = "UPDATE $this->table 
                SET dia='$dia', hora_inicio='$hora_inicio', hora_fin='$hora_fin', 
                    id_zona=$id_zona, id_ficha=$id_ficha, id_instructor=$id_instructor 
                WHERE id_horario=$id";
        return $this->conn->query($sql);
    }

    public function eliminar($id) {
        $sql = "DELETE FROM $this->table WHERE id_horario=$id";
        return $this->conn->query($sql);
    }
}
?>
