<?php
class Trimestre {
    private $conn;
    private $table = "trimestre";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function listar() {
        $sql = "SELECT * FROM {$this->table}";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt;
    }

    public function crear($numero_trimestre, $estado) {
        $sql = "INSERT INTO {$this->table} (numero_trimestre, estado) VALUES (:numero_trimestre, :estado)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':numero_trimestre', $numero_trimestre);
        $stmt->bindParam(':estado', $estado);
        return $stmt->execute();
    }

    public function obtenerPorId($numero_trimestre) {
        $sql = "SELECT * FROM {$this->table} WHERE numero_trimestre = :numero_trimestre";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':numero_trimestre', $numero_trimestre);
        $stmt->execute();
        return $stmt;
    }

    public function actualizar($numero_trimestre, $estado) {
        $sql = "UPDATE {$this->table} SET estado = :estado WHERE numero_trimestre = :numero_trimestre";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':numero_trimestre', $numero_trimestre);
        return $stmt->execute();
    }

    public function eliminar($numero_trimestre) {
        $sql = "DELETE FROM {$this->table} WHERE numero_trimestre = :numero_trimestre";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':numero_trimestre', $numero_trimestre);
        return $stmt->execute();
    }
}
?>
