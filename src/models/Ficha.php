<?php
class Ficha {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function obtenerFichas() {
        $sql = "SELECT * FROM fichas";
        return mysqli_query($this->conexion, $sql);
    }

    public function crearFicha($nivel_formativo, $nombre_programa) {
        $sql = "INSERT INTO fichas (nivel_formativo, nombre_programa)
                VALUES ('$nivel_formativo', '$nombre_programa')";
        mysqli_query($this->conexion, $sql);
    }
}
?>
