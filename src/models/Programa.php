<?php
// Clase Programa para manejar operaciones CRUD sobre la tabla 'programas'
class Programa {
    private $conn;
    private $table = "programas";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Funcion para listar todos los programas
    public function listar() {
        try {
            $sql = "SELECT p.*, 
                    (SELECT COUNT(*) FROM instructores_programas ip WHERE ip.id_programa = p.id_programa AND ip.estado = 1) as num_instructores 
                    FROM " . $this->table . " p 
                    ORDER BY p.nombre_programa ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    // Funcion para obtener un programa por su ID
    public function obtenerPorId($id_programa) {
        try {
            $sql = "SELECT p.*, 
                    (SELECT COUNT(*) FROM instructores_programas ip WHERE ip.id_programa = p.id_programa AND ip.estado = 1) as num_instructores 
                    FROM " . $this->table . " p 
                    WHERE p.id_programa = :id_programa";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_programa', $id_programa, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    // Funcion para listar instructores de un programa
    public function listarInstructores($id_programa) {
        try {
            $sql = "SELECT u.id_usuario as id_instructor, u.nombre_completo as nombre_instructor, u.correo_electronico, ip.fecha_asignacion 
                    FROM usuarios u 
                    INNER JOIN instructores_programas ip ON u.id_usuario = ip.id_instructor 
                    WHERE ip.id_programa = :id_programa AND u.cargo = 'INSTRUCTOR' AND ip.estado = 1
                    ORDER BY u.nombre_completo ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_programa', $id_programa, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    // Funcion para listar todos los instructores disponibles
    public function listarTodosInstructores() {
        try {
            $sql = "SELECT id_usuario as id_instructor, nombre_completo as nombre_instructor, correo_electronico 
                    FROM usuarios 
                    WHERE cargo = 'INSTRUCTOR' AND estado = 1
                    ORDER BY nombre_completo ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    // Funcion para crear un nuevo programa
    public function crear($id_programa, $nombre_programa, $descripcion, $duracion, $nivel_formacion) {
        try {
            if (!in_array($nivel_formacion, ['tecnico', 'tecnologo'])) {
                return ["error" => "Nivel de formación no válido"];
            }

            $sql = "INSERT INTO " . $this->table . " 
                    (id_programa, nombre_programa, nivel_formacion, descripcion, duracion, estado)
                    VALUES (:id_programa, :nombre_programa, :nivel_formacion, :descripcion, :duracion, 1)";
            $stmt = $this->conn->prepare($sql);
            
            $stmt->bindParam(':id_programa', $id_programa, PDO::PARAM_INT);
            $stmt->bindParam(':nombre_programa', $nombre_programa);
            $stmt->bindParam(':nivel_formacion', $nivel_formacion);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':duracion', $duracion, PDO::PARAM_INT);
            
            $stmt->execute();
            return ["success" => "Programa creado exitosamente."];
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                return ["error" => "Ya existe un programa con ese nombre o código."];
            }
            return ["error" => $e->getMessage()];
        }
    }

    // Funcion para actualizar un programa existente
    public function actualizar($id_programa_actual, $nuevo_id_programa, $nombre_programa, $descripcion, $duracion, $nivel_formacion) {
        try {
            if (!in_array($nivel_formacion, ['tecnico', 'tecnologo'])) {
                return ["error" => "Nivel de formación no válido"];
            }

            $this->conn->beginTransaction();

            if ($nuevo_id_programa != $id_programa_actual) {
                $check = $this->conn->prepare("SELECT 1 FROM " . $this->table . " WHERE id_programa = ?");
                $check->execute([$nuevo_id_programa]);
                if ($check->fetchColumn()) {
                    $this->conn->rollBack();
                    return ["error" => "Ya existe un programa con el nuevo código."];
                }
            }

            $sql = "UPDATE " . $this->table . " 
                    SET id_programa = :nuevo_id_programa,
                        nombre_programa = :nombre_programa,
                        nivel_formacion = :nivel_formacion,
                        descripcion = :descripcion,
                        duracion = :duracion
                    WHERE id_programa = :id_programa_actual";
            $stmt = $this->conn->prepare($sql);
            
            $stmt->bindParam(':id_programa_actual', $id_programa_actual, PDO::PARAM_INT);
            $stmt->bindParam(':nuevo_id_programa', $nuevo_id_programa, PDO::PARAM_INT);
            $stmt->bindParam(':nombre_programa', $nombre_programa);
            $stmt->bindParam(':nivel_formacion', $nivel_formacion);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':duracion', $duracion, PDO::PARAM_INT);
            
            $stmt->execute();

            $this->conn->commit();
            return ["success" => "Programa actualizado correctamente.", "id_programa" => $nuevo_id_programa];
        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            if ($e->errorInfo[1] == 1062) {
                return ["error" => "Ya existe un programa con ese nombre o código."];
            }
            return ["error" => $e->getMessage()];
        }
    }

    // Funcion para asignar instructores a un programa
    public function asignarInstructores($id_programa, $instructores) {
        try {
            $this->conn->beginTransaction();

            // Actualizar instructores existentes a estado 0 (inactivos)
            $update = $this->conn->prepare("UPDATE instructores_programas SET estado = 0 WHERE id_programa = ?");
            $update->execute([$id_programa]);

            // Insertar nuevos instructores
            if (!empty($instructores)) {
                $insert = $this->conn->prepare("INSERT INTO instructores_programas (id_programa, id_instructor, estado) VALUES (?, ?, 1)");
                foreach ($instructores as $id_instructor) {
                    // Verificar si ya existe la relación
                    $check = $this->conn->prepare("SELECT 1 FROM instructores_programas WHERE id_programa = ? AND id_instructor = ?");
                    $check->execute([$id_programa, $id_instructor]);
                    if ($check->fetchColumn()) {
                        // Si existe, actualizar a estado 1
                        $update = $this->conn->prepare("UPDATE instructores_programas SET estado = 1 WHERE id_programa = ? AND id_instructor = ?");
                        $update->execute([$id_programa, $id_instructor]);
                    } else {
                        // Si no existe, insertar nueva
                        $insert->execute([$id_programa, $id_instructor]);
                    }
                }
            }

            $this->conn->commit();
            return ["success" => "Instructores asignados correctamente."];
        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return ["error" => $e->getMessage()];
        }
    }

    // Funcion para remover un instructor de un programa
    public function removerInstructor($id_programa, $id_instructor) {
        try {
            $sql = "UPDATE instructores_programas SET estado = 0 WHERE id_programa = ? AND id_instructor = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$id_programa, $id_instructor]);
            return ["success" => "Instructor removido correctamente."];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    // Funcion para cambiar estado (activar/inhabilitar)
    public function cambiarEstado($id_programa, $estado) {
        try {
            $sql = "UPDATE " . $this->table . " SET estado = :estado WHERE id_programa = :id_programa";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_programa', $id_programa, PDO::PARAM_INT);
            $stmt->bindParam(':estado', $estado, PDO::PARAM_INT);
            $stmt->execute();
            $mensaje = $estado == 1 ? "Programa activado correctamente." : "Programa inhabilitado correctamente.";
            return ["success" => $mensaje];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    // Funcion para eliminar un programa
    public function eliminar($id_programa) {
        try {
            $this->conn->beginTransaction();

            // Las relaciones se eliminarán automáticamente por ON DELETE CASCADE
            $sql = "DELETE FROM " . $this->table . " WHERE id_programa = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$id_programa]);

            $this->conn->commit();
            return ["success" => "Programa eliminado exitosamente."];
        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return ["error" => $e->getMessage()];
        }
    }
}
?>