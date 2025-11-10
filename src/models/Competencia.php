<?php
// Clase Competencia para manejar operaciones CRUD sobre la tabla 'Competencias'
class Competencia {
    private $conn;
    private $table = "competencias";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Funcion para listar todas las competencias
    public function listar() {
        try {
            $sql = "SELECT * FROM {$this->table}";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    // Funcion para obtener una competencia por su ID
    public function obtenerPorId($id_competencia) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE id_competencia = :id_competencia";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_competencia', $id_competencia);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    // Funcion para crear una nueva competencia
    public function crear($id_competencia, $id_programa, $nombre_competencia, $descripcion) {
        try {
            $sql = "INSERT INTO {$this->table}
                    (id_competencia, id_programa, nombre_competencia, descripcion, estado)
                    VALUES (:id_competencia, :id_programa, :nombre_competencia, :descripcion, 1)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id_competencia', $id_competencia);
            $stmt->bindValue(':id_programa', $id_programa);
            $stmt->bindValue(':nombre_competencia', $nombre_competencia);
            $stmt->bindValue(':descripcion', $descripcion);
            $stmt->execute();
            return ['ok' => true, 'id_competencia' => $id_competencia];
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    // Actualizar (permite opcionalmente cambiar id_programa)
    public function actualizar($id_competencia, $nombre_competencia, $descripcion, $id_programa = null) {
        try {
            $sets = ["nombre_competencia = :nombre_competencia", "descripcion = :descripcion"];
            if ($id_programa !== null && $id_programa !== '') {
                $sets[] = "id_programa = :id_programa";
            }
            $sql = "UPDATE {$this->table} SET " . implode(', ', $sets) . " WHERE id_competencia = :id_competencia";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_competencia', $id_competencia);
            $stmt->bindParam(':nombre_competencia', $nombre_competencia);
            $stmt->bindParam(':descripcion', $descripcion);
            if (strpos($sql, 'id_programa = :id_programa') !== false) {
                $stmt->bindParam(':id_programa', $id_programa);
            }
            $stmt->execute();
            return ["mensaje" => "Competencia actualizada correctamente."];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    // Funcion para eliminar una competencia
    public function eliminar($id_competencia) {
        try {
            $sql = "DELETE FROM {$this->table} WHERE id_competencia = :id_competencia";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_competencia', $id_competencia);
            $stmt->execute();
            return ["mensaje" => "Competencia eliminada exitosamente."];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    // Funcion para cambiar el estado de una competencia
    public function cambiarEstado($id_competencia, $nuevoEstado) {
        try {
            if ($nuevoEstado != 0 && $nuevoEstado != 1) {
                throw new Exception("El estado debe ser 1 (activo) o 0 (inactivo).");
            }
            $sql = "UPDATE {$this->table} SET estado = :estado WHERE id_competencia = :id_competencia";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':estado', $nuevoEstado);
            $stmt->bindParam(':id_competencia', $id_competencia);
            $stmt->execute();
            return ["mensaje" => "Estado de competencia actualizado correctamente."];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    // Funcion para listar las competencias activas
    public function listarActivas() {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE estado = 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }
}
?>
