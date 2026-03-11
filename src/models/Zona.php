<?php
class Zona {
    private $conn;
    private $table = "zonas";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function crear($nombre_zona, $id_area) {
        try {
            $check = $this->conn->prepare(
                "SELECT 1 FROM {$this->table}
                 WHERE LOWER(TRIM(nombre_zona)) = LOWER(TRIM(:nombre_zona))
                   AND id_area = :id_area LIMIT 1"
            );
            $check->execute([':nombre_zona' => $nombre_zona, ':id_area' => $id_area]);
            if ($check->rowCount() > 0) {
                return ["status" => "error", "message" => "Ya existe una zona con ese nombre en esta área."];
            }

            $sql = "INSERT INTO {$this->table} (id_area, nombre_zona, estado)
                    VALUES (:id_area, :nombre_zona, 1)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_area',     $id_area,     PDO::PARAM_INT);
            $stmt->bindParam(':nombre_zona', $nombre_zona);
            $stmt->execute();

            return ["status" => "success", "message" => "Zona creada correctamente."];
        } catch (PDOException $e) {
            return ["status" => "error", "message" => "Error al crear zona: " . $e->getMessage()];
        }
    }

    public function actualizar($id_zona, $nombre_zona_nueva, $id_area_nueva) {
        try {
            $check = $this->conn->prepare(
                "SELECT 1 FROM {$this->table}
                 WHERE LOWER(TRIM(nombre_zona)) = LOWER(TRIM(:nombre_zona))
                   AND id_area = :id_area
                   AND id_zona != :id_zona LIMIT 1"
            );
            $check->execute([
                ':nombre_zona' => $nombre_zona_nueva,
                ':id_area'     => $id_area_nueva,
                ':id_zona'     => $id_zona
            ]);
            if ($check->rowCount() > 0) {
                return ["status" => "error", "message" => "Ya existe una zona con ese nombre en esta área."];
            }

            $sql = "UPDATE {$this->table}
                    SET nombre_zona = :nombre_zona, id_area = :id_area
                    WHERE id_zona = :id_zona";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':nombre_zona', $nombre_zona_nueva);
            $stmt->bindParam(':id_area',     $id_area_nueva,    PDO::PARAM_INT);
            $stmt->bindParam(':id_zona',     $id_zona,          PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return ["status" => "success", "message" => "Zona actualizada correctamente."];
            } else {
                return ["status" => "warning", "message" => "Sin cambios."];
            }
        } catch (PDOException $e) {
            return ["status" => "error", "message" => "Error al actualizar zona: " . $e->getMessage()];
        }
    }

    public function cambiarEstado($id_zona, $estado) {
        try {
            $sql = "UPDATE {$this->table} SET estado = :estado WHERE id_zona = :id_zona";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':estado',   $estado,   PDO::PARAM_INT);
            $stmt->bindParam(':id_zona',  $id_zona,  PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return ["status" => "success", "message" => "Estado actualizado."];
            } else {
                return ["status" => "warning", "message" => "Sin cambios."];
            }
        } catch (PDOException $e) {
            return ["status" => "error", "message" => "Error al cambiar estado: " . $e->getMessage()];
        }
    }

    public function listar() {
        try {
            $sql = "SELECT z.id_zona, z.id_area, z.nombre_zona, a.nombre_area, z.estado
                    FROM {$this->table} z
                    LEFT JOIN area a ON z.id_area = a.id_area
                    ORDER BY z.nombre_zona ASC, a.nombre_area ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function listarPorArea($id_area) {
        try {
            $sql = "SELECT z.id_zona, z.id_area, z.nombre_zona, a.nombre_area, z.estado
                    FROM {$this->table} z
                    LEFT JOIN area a ON z.id_area = a.id_area
                    WHERE z.id_area = :id_area
                    ORDER BY z.nombre_zona ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_area', $id_area, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function obtenerPorId($id_zona) {
        try {
            $sql = "SELECT z.id_zona, z.id_area, z.nombre_zona, a.nombre_area, z.estado
                    FROM {$this->table} z
                    LEFT JOIN area a ON z.id_area = a.id_area
                    WHERE z.id_zona = :id_zona";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_zona', $id_zona, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }
}
?>
