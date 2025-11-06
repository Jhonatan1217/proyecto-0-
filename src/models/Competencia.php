<?php
class Competencia {
    private $conn;
    private $table = "competencias";

    public function __construct($db) {
        $this->conn = $db;
    }

    // ===============================
    // ETL: Buscar o crear competencia
    // ===============================
    public function buscarOcrear($id_programa, $codigo, $nombre) {

        // 1. Buscar si ya existe
        $sql = "SELECT id_competencia FROM " . $this->table . " 
                WHERE codigo_competencia = :codigo AND id_programa = :id_programa";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':codigo', $codigo);
        $stmt->bindParam(':id_programa', $id_programa);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) return $data["id_competencia"];

        // 2. Insertar si no existe
        $sql = "INSERT INTO " . $this->table . " (id_programa, codigo_competencia, nombre_competencia, estado)
                VALUES (:id_programa, :codigo, :nombre, 1)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_programa', $id_programa);
        $stmt->bindParam(':codigo', $codigo);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->execute();

        return $this->conn->lastInsertId();
    }

    // ===============================
    // ETL: Obtener por código
    // ===============================
    public function obtenerPorCodigo($codigo) {
        $sql = "SELECT * FROM " . $this->table . " WHERE codigo_competencia = :codigo";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':codigo', $codigo);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ===============================
    // CRUD
    // ===============================

    public function listar() {
        try {
            $sql = "SELECT * FROM " . $this->table;
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    public function obtenerPorId($id_competencia) {
        try {
            $sql = "SELECT * FROM " . $this->table . " WHERE id_competencia = :id_competencia";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_competencia', $id_competencia);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    public function crear($nombre_competencia, $descripcion) {
        try {
            $sql = "INSERT INTO " . $this->table . " (nombre_competencia, descripcion)
                    VALUES (:nombre_competencia, :descripcion)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':nombre_competencia', $nombre_competencia);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->execute();
            return ["mensaje" => "Competencia creada exitosamente."];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    public function actualizar($id_competencia, $nombre_competencia, $descripcion) {
        try {
            $sql = "UPDATE " . $this->table . " 
                    SET nombre_competencia = :nombre_competencia, 
                        descripcion = :descripcion
                    WHERE id_competencia = :id_competencia";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_competencia', $id_competencia);
            $stmt->bindParam(':nombre_competencia', $nombre_competencia);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->execute();
            return ["mensaje" => "Competencia actualizada correctamente."];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    public function eliminar($id_competencia) {
        try {
            $sql = "DELETE FROM " . $this->table . " WHERE id_competencia = :id_competencia";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_competencia', $id_competencia);
            $stmt->execute();
            return ["mensaje" => "Competencia eliminada exitosamente."];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    public function cambiarEstado($id_competencia, $nuevoEstado) {
        try {
            $sql = "UPDATE " . $this->table . " 
                    SET estado = :estado 
                    WHERE id_competencia = :id_competencia";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':estado', $nuevoEstado);
            $stmt->bindParam(':id_competencia', $id_competencia);
            $stmt->execute();
            return ["mensaje" => "Estado actualizado correctamente."];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }
}
