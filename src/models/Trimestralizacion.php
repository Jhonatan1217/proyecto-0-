<?php
class Trimestralizacion {
    private $conn;
    private $table = "trimestralizacion";

    public function __construct($db) {
        $this->conn = $db;
    }

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

    public function obtenerPorId($id_trimestral) {
        try {
            $sql = "SELECT * FROM " . $this->table . " WHERE id_trimestral = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":id", $id_trimestral, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    public function crear($data) {
        try {
            // Ajusta los nombres de las columnas según tu tabla
            $sql = "INSERT INTO " . $this->table . " (columna1, columna2, columna3)
                    VALUES (:columna1, :columna2, :columna3)";
            $stmt = $this->conn->prepare($sql);

            $stmt->bindParam(":columna1", $data['columna1']);
            $stmt->bindParam(":columna2", $data['columna2']);
            $stmt->bindParam(":columna3", $data['columna3']);

            $stmt->execute();

            return [
                "success" => true,
                "message" => "Trimestralización creada correctamente",
                "id_insertado" => $this->conn->lastInsertId()
            ];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    public function actualizar($id, $data) {
        try {
            $sql = "UPDATE " . $this->table . " 
                    SET columna1 = :columna1, columna2 = :columna2, columna3 = :columna3
                    WHERE id_trimestral = :id";
            $stmt = $this->conn->prepare($sql);

            $stmt->bindParam(":columna1", $data['columna1']);
            $stmt->bindParam(":columna2", $data['columna2']);
            $stmt->bindParam(":columna3", $data['columna3']);
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);

            $stmt->execute();

            return ["success" => true, "message" => "Registro actualizado correctamente"];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    public function eliminar($id) {
        try {
            $sql = "DELETE FROM " . $this->table . " WHERE id_trimestral = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            return ["success" => true, "message" => "Registro eliminado correctamente"];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }
}
