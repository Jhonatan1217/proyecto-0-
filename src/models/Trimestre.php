<?php
// Clase Trimestre para manejar operaciones CRUD sobre la tabla 'trimestre'
class Trimestre {
    private $conn;
    private $table = "trimestre";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Funcion para listar todos los trimestres
    public function listar() {
        $sql = "SELECT * FROM {$this->table}";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt;
    }

    // Funcion para crear un nuevo trimestre
    public function crear($numero_trimestre, $estado) {
        $sql = "INSERT INTO {$this->table} (numero_trimestre, estado) VALUES (:numero_trimestre, :estado)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':numero_trimestre', $numero_trimestre);
        $stmt->bindParam(':estado', $estado);
        return $stmt->execute();
    }

    // Funcion para obtener un trimestre por su ID
    public function obtenerPorId($numero_trimestre) {
        $sql = "SELECT * FROM {$this->table} WHERE numero_trimestre = :numero_trimestre";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':numero_trimestre', $numero_trimestre);
        $stmt->execute();
        return $stmt;
    }

    // Funcion para actualizar un trimestre existente
    public function actualizar($numero_trimestre, $estado) {
        $sql = "UPDATE {$this->table} SET estado = :estado WHERE numero_trimestre = :numero_trimestre";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':numero_trimestre', $numero_trimestre);
        return $stmt->execute();
    }

    // Funcion para editar un trimestre existente
    public function editar($numero_trimestre, $nuevo_numero) {
    $sql = "UPDATE {$this->table} 
            SET numero_trimestre = :nuevo_numero 
            WHERE numero_trimestre = :numero_trimestre";
    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':nuevo_numero', $nuevo_numero);
    $stmt->bindParam(':numero_trimestre', $numero_trimestre);
    return $stmt->execute();
    }

    // Funcion para verificar si un trimestre existe
    public function existe($numero_trimestre) {
    $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE numero_trimestre = :numero_trimestre";
    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':numero_trimestre', $numero_trimestre);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row['total'] > 0;
}

    // Funcion para suspender un trimestre
    public function eliminar($numero_trimestre) {
        $sql = "UPDATE {$this->table} SET estado = 0 WHERE numero_trimestre = :numero_trimestre";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':numero_trimestre', $numero_trimestre);
        return $stmt->execute();
    }

    // Funcion para reactivar un trimestre
    public function reactivar($numero_trimestre) {
        $sql = "UPDATE {$this->table} SET estado = 1 WHERE numero_trimestre = :numero_trimestre";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':numero_trimestre', $numero_trimestre);
        return $stmt->execute();
    }
}
?>
