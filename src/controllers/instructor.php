<?php
class Instructor {
    private $conn;
    private $table = "instructores";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function listar() {
        $sql = "SELECT * FROM $this->table";
        return $this->conn->query($sql);
    }

    public function crear($nombre, $apellido, $tipo) {
        $sql = "INSERT INTO $this->table (nombre_instructor, apellido_instructor, tipo_instructor)
                VALUES ('$nombre', '$apellido', '$tipo')";
        return $this->conn->query($sql);
    }

    public function actualizar($id, $nombre, $apellido, $tipo) {
        $sql = "UPDATE $this->table 
                SET nombre_instructor='$nombre', apellido_instructor='$apellido', tipo_instructor='$tipo'
                WHERE id_instructor=$id";
        return $this->conn->query($sql);
    }

    public function eliminar($id) {
        $sql = "DELETE FROM $this->table WHERE id_instructor=$id";
        return $this->conn->query($sql);
    }
}
?>
