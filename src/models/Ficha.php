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

public function actualizarFicha($id, $nivel_formativo, $nombre_programa) {
    $sql = "UPDATE fichas 
            SET nivel_formativo = '$nivel_formativo', nombre_programa = '$nombre_programa'
            WHERE id = $id";
    return mysqli_query($this->conexion, $sql);
}
}
?>
