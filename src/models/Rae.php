<?php
class Rae {
    private $conn;
    private $table = "raes";

    public function __construct($db) {
        $this->conn = $db;
    }

    // ===============================
    // ETL: Buscar o crear RAE
    // ===============================
    public function buscarOcrear($id_competencia, $codigo, $descripcion) {

        // 1. Buscar si ya existe
        $sql = "SELECT id_rae FROM " . $this->table . "
                WHERE codigo_rae = :codigo AND id_competencia = :id_competencia";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':codigo', $codigo);
        $stmt->bindParam(':id_competencia', $id_competencia);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) return $data["id_rae"];

        // 2. Crear si no existe
        $sql = "INSERT INTO " . $this->table . " (id_competencia, codigo_rae, descripcion, estado)
                VALUES (:id_competencia, :codigo, :descripcion, 1)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_competencia', $id_competencia);
        $stmt->bindParam(':codigo', $codigo);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->execute();

        return $this->conn->lastInsertId();
    }

    // Obtener RAE por código
    public function obtenerPorCodigo($codigo) {
        $sql = "SELECT * FROM " . $this->table . " WHERE codigo_rae = :codigo";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':codigo', $codigo);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Listar todos los RAEs
    public function listar() {
        $sql = "SELECT * FROM " . $this->table;
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener un RAE por su ID
    public function obtenerPorId($id) {
        $sql = "SELECT * FROM " . $this->table . " WHERE id_rae = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Crear RAE
    public function crear($descripcion, $id_competencia) {
        $sql = "INSERT INTO " . $this->table . " (descripcion, id_competencia)
                VALUES (:descripcion, :id_competencia)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':id_competencia', $id_competencia);
        $stmt->execute();
    }

    // Actualizar RAE
    public function actualizar($id, $descripcion, $id_competencia) {
        $sql = "UPDATE " . $this->table . " 
                SET descripcion = :descripcion, id_competencia = :id_competencia
                WHERE id_rae = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':id_competencia', $id_competencia);
        $stmt->execute();
    }

    // Eliminar RAE
    public function eliminar($id) {
        $sql = "DELETE FROM " . $this->table . " WHERE id_rae = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }

    // Cambiar estado
    public function cambiarEstado($id, $nuevoEstado) {
        $sql = "UPDATE " . $this->table . " SET estado = :estado WHERE id_rae = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':estado', $nuevoEstado);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }

    // Listar RAES con competencia asociada
    public function listarConCompetencia() {
        $sql = "SELECT r.id_rae, r.codigo_rae, r.descripcion, r.estado, 
                       c.nombre_competencia, c.codigo_competencia
                FROM raes r
                LEFT JOIN competencias c ON r.id_competencia = c.id_competencia";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
