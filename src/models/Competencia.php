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
            $sql = "SELECT c.*, p.nombre_programa 
                    FROM {$this->table} c
                    LEFT JOIN programas p ON c.id_programa = p.id_programa
                    ORDER BY c.nombre_competencia ASC";
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
            $sql = "SELECT c.*, p.nombre_programa 
                    FROM {$this->table} c
                    LEFT JOIN programas p ON c.id_programa = p.id_programa
                    WHERE c.id_competencia = :id_competencia";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_competencia', $id_competencia);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    // Funcion para crear una nueva competencia
    public function crear($id_competencia, $id_programa, $nombre_competencia) {
        try {
            $sql = "INSERT INTO {$this->table}
                    (id_competencia, id_programa, nombre_competencia, estado)
                    VALUES (:id_competencia, :id_programa, :nombre_competencia, 1)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id_competencia', $id_competencia);
            $stmt->bindValue(':id_programa', $id_programa);
            $stmt->bindValue(':nombre_competencia', $nombre_competencia);
            $stmt->execute();
            return ['ok' => true, 'id_competencia' => $id_competencia, 'success' => 'Competencia creada exitosamente.'];
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                return ['error' => 'Ya existe una competencia con ese código.'];
            }
            return ['error' => $e->getMessage()];
        }
    }

    // Actualizar competencia (permite cambiar código)
    public function actualizar($id_competencia_actual, $nuevo_id_competencia, $nombre_competencia, $id_programa) {
        try {
            $this->conn->beginTransaction();
            
            // Verificar si el nuevo código ya existe (solo si cambió)
            if ($nuevo_id_competencia != $id_competencia_actual) {
                $check = $this->conn->prepare("SELECT 1 FROM {$this->table} WHERE id_competencia = ?");
                $check->execute([$nuevo_id_competencia]);
                if ($check->fetchColumn()) {
                    $this->conn->rollBack();
                    return ["error" => "Ya existe una competencia con el nuevo código."];
                }
            }
            
            $sql = "UPDATE {$this->table} 
                    SET id_competencia = :nuevo_id,
                        nombre_competencia = :nombre_competencia,
                        id_programa = :id_programa
                    WHERE id_competencia = :id_actual";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_actual', $id_competencia_actual);
            $stmt->bindParam(':nuevo_id', $nuevo_id_competencia);
            $stmt->bindParam(':nombre_competencia', $nombre_competencia);
            $stmt->bindParam(':id_programa', $id_programa);
            $stmt->execute();
            
            $this->conn->commit();
            return ['ok' => true, 'success' => 'Competencia actualizada correctamente.', 'id_competencia' => $nuevo_id_competencia];
        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            if ($e->errorInfo[1] == 1062) {
                return ["error" => "Ya existe una competencia con ese código."];
            }
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
            return ["success" => "Competencia eliminada exitosamente."];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    // Funcion para cambiar el estado de una competencia
    public function cambiarEstado($id_competencia, $nuevoEstado) {
        try {
            if ($nuevoEstado != 0 && $nuevoEstado != 1) {
                return ["error" => "El estado debe ser 1 (activo) o 0 (inactivo)."];
            }
            $sql = "UPDATE {$this->table} SET estado = :estado WHERE id_competencia = :id_competencia";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':estado', $nuevoEstado, PDO::PARAM_INT);
            $stmt->bindParam(':id_competencia', $id_competencia);
            $stmt->execute();
            $mensaje = $nuevoEstado == 1 ? "Competencia activada correctamente." : "Competencia inhabilitada correctamente.";
            return ["success" => $mensaje];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    // Funcion para listar las competencias activas
    public function listarActivas() {
        try {
            $sql = "SELECT c.*, p.nombre_programa 
                    FROM {$this->table} c
                    LEFT JOIN programas p ON c.id_programa = p.id_programa
                    WHERE c.estado = 1
                    ORDER BY c.nombre_competencia ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }
}
?>