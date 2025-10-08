<?php
class Horario {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function obtenerHorarios() {
        $sql = "SELECT * FROM horarios";
        return mysqli_query($this->conexion, $sql);
    }

    public function crearHorario($dia, $hora_inicio, $hora_fin) {
        $sql = "INSERT INTO horarios (dia, hora_inicio, hora_fin)
                VALUES ('$dia', '$hora_inicio', '$hora_fin')";
        mysqli_query($this->conexion, $sql);
    }

    public function actualizarHorario($id, $dia, $hora_inicio, $hora_fin) {
        $sql = "UPDATE horarios 
                SET dia = '$dia', hora_inicio = '$hora_inicio', hora_fin = '$hora_fin'
                WHERE id = $id";
        return mysqli_query($this->conexion, $sql);
    }
}
?>
