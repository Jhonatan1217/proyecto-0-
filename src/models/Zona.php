<?php
class Zona {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function obtenerZonas() {
        $sql = "SELECT * FROM zonas";
        return mysqli_query($this->conexion, $sql);
    }
}
?>
