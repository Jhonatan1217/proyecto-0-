<?php
class Instructor {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function obtenerInstructores() {
        $sql = "SELECT * FROM instructores";
        return mysqli_query($this->conexion, $sql);
    }

    public function crearInstructor($nombre_instructor, $apellido_instructor, $tipo_instructor) {
        $sql = "INSERT INTO instructores (nombre_instructor, apellido_instructor, tipo_instructor)
                VALUES ('$nombre_instructor', '$apellido_instructor', '$tipo_instructor')";
        mysqli_query($this->conexion, $sql);
    }

    public function actualizarInstructor($id, $nombre_instructor, $apellido_instructor, $tipo_instructor) {
        $sql = "UPDATE instructores
                SET nombre_instructor = '$nombre_instructor', apellido_instructor = '$apellido_instructor', tipo_instructor = '$tipo_instructor'
                WHERE id = $id";
        return mysqli_query($this->conexion, $sql);
    }
}
?>
