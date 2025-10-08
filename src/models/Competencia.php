<?php
class Competencia {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function obtenerCompetencias() {
        $sql = "SELECT * FROM competencias";
        $resultado = mysqli_query($this->conexion, $sql);
        return $resultado;
    }

    public function crearCompetencia($descripcion, $tipo, $nombre_competencia) {
        $sql = "INSERT INTO competencias (descripcion, tipo, nombre_competencia)
                VALUES ('$descripcion', '$tipo', '$nombre_competencia')";
        mysqli_query($this->conexion, $sql);
    }
}
?>
