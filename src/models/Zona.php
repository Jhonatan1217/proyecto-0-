<?php
class Zona {
    private $conn;
    private $table = "zonas";

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Crear una nueva zona
     * - Se ingresa el número de la zona (id_zona)
     * - Se selecciona el área (id_area)
     * - Se activa por defecto (estado = 1)
     */
    public function crear($id_zona, $id_area) {
        try {
            // Verificar si ya existe la combinación (id_zona, id_area)
            $check = $this->conn->prepare("SELECT * FROM {$this->table} WHERE id_zona = :id_zona AND id_area = :id_area");
            $check->execute([':id_zona' => $id_zona, ':id_area' => $id_area]);

            if ($check->rowCount() > 0) {
                return ["status" => "error", "message" => "Ya existe una zona con ese número en esta área."];
            }

            $sql = "INSERT INTO {$this->table} (id_zona, id_area, estado) VALUES (:id_zona, :id_area, 1)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_zona', $id_zona, PDO::PARAM_INT);
            $stmt->bindParam(':id_area', $id_area, PDO::PARAM_INT);
            $stmt->execute();

            return ["status" => "success", "message" => "Zona creada correctamente."];
        } catch (PDOException $e) {
            return ["status" => "error", "message" => "Error al crear zona: " . $e->getMessage()];
        }
    }

    /**
     * Actualizar una zona existente
     * - Permite cambiar el número de zona y el área (PK compuesta)
     */
    public function actualizar($id_zona_actual, $id_area_actual, $id_zona_nueva, $id_area_nueva) {
        try {
            $sql = "UPDATE {$this->table}
                    SET id_zona = :id_zona_nueva, id_area = :id_area_nueva
                    WHERE id_zona = :id_zona_actual AND id_area = :id_area_actual";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_zona_nueva', $id_zona_nueva, PDO::PARAM_INT);
            $stmt->bindParam(':id_area_nueva', $id_area_nueva, PDO::PARAM_INT);
            $stmt->bindParam(':id_zona_actual', $id_zona_actual, PDO::PARAM_INT);
            $stmt->bindParam(':id_area_actual', $id_area_actual, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return ["status" => "success", "message" => "Zona actualizada correctamente."];
            } else {
                return ["status" => "warning", "message" => "No se encontró la zona o no hubo cambios."];
            }
        } catch (PDOException $e) {
            return ["status" => "error", "message" => "Error al actualizar zona: " . $e->getMessage()];
        }
    }

    /**
     * Cambiar el estado de una zona (activar o desactivar)
     */
    public function cambiarEstado($id_zona, $id_area, $estado) {
        try {
            $sql = "UPDATE {$this->table} 
                    SET estado = :estado 
                    WHERE id_zona = :id_zona AND id_area = :id_area";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':estado', $estado, PDO::PARAM_INT);
            $stmt->bindParam(':id_zona', $id_zona, PDO::PARAM_INT);
            $stmt->bindParam(':id_area', $id_area, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return ["status" => "success", "message" => "Estado de la zona actualizado correctamente."];
            } else {
                return ["status" => "warning", "message" => "No se encontró la zona o ya tiene ese estado."];
            }
        } catch (PDOException $e) {
            return ["status" => "error", "message" => "Error al cambiar estado: " . $e->getMessage()];
        }
    }


    /**
     * Listar todas las zonas con su área correspondiente
     */
    public function listar() {
        try {
            $sql = "SELECT 
                        z.id_zona, 
                        z.id_area, 
                        a.nombre_area, 
                        z.estado
                    FROM {$this->table} z
                    LEFT JOIN areas a ON z.id_area = a.id_area
                    ORDER BY z.id_zona ASC, a.nombre_area ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**

     * Listar zonas por área
     */
    public function listarPorArea($id_area) {
        try {
            $sql = "SELECT 
                        z.id_zona, 
                        z.id_area, 
                        a.nombre_area, 
                        z.estado
                    FROM {$this->table} z
                    INNER JOIN areas a ON z.id_area = a.id_area
                    WHERE z.id_area = :id_area
                    ORDER BY z.id_zona ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_area', $id_area, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Obtener una zona específica
     */
    public function obtenerPorId($id_zona, $id_area) {
        try {
            $sql = "SELECT z.id_zona, z.id_area, a.nombre_area, z.estado
                    FROM {$this->table} z
                    LEFT JOIN areas a ON z.id_area = a.id_area
                    WHERE z.id_zona = :id_zona AND z.id_area = :id_area";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_zona', $id_zona, PDO::PARAM_INT);
            $stmt->bindParam(':id_area', $id_area, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }
}
?>
