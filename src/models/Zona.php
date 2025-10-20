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
            $sql = "INSERT INTO " . $this->table . " (id_zona, id_area, estado)
                    VALUES (:id_zona, :id_area, 1)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_zona', $id_zona, PDO::PARAM_INT);
            $stmt->bindParam(':id_area', $id_area, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            echo "Error al crear zona: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Actualizar una zona existente
     * - Permite cambiar el número de zona (id_zona) y el área
     */
    public function actualizar($id_zona_actual, $id_zona_nueva, $id_area) {
        try {
            $sql = "UPDATE " . $this->table . "
                    SET id_zona = :id_zona_nueva, id_area = :id_area
                    WHERE id_zona = :id_zona_actual";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_zona_nueva', $id_zona_nueva, PDO::PARAM_INT);
            $stmt->bindParam(':id_area', $id_area, PDO::PARAM_INT);
            $stmt->bindParam(':id_zona_actual', $id_zona_actual, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al actualizar zona: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cambiar el estado de una zona (activar o desactivar)
     */
    public function cambiarEstado($id_zona, $estado) {
        try {
            $sql = "UPDATE " . $this->table . "
                    SET estado = :estado
                    WHERE id_zona = :id_zona";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_zona', $id_zona, PDO::PARAM_INT);
            $stmt->bindParam(':estado', $estado, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            echo "Error al cambiar estado de la zona: " . $e->getMessage();
            return false;
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
                ORDER BY z.id_zona ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // ❌ No hagas echo aquí
        return [];
    }
}

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
        error_log('Error en listarPorArea: ' . $e->getMessage());
        return [];
    }
}


    /**
     * Obtener una zona específica por su número (id_zona)
     */
    public function obtenerPorId($id_zona) {
        try {
            $sql = "SELECT z.id_zona, z.id_area, a.nombre_area, z.estado
                    FROM " . $this->table . " z
                    LEFT JOIN areas a ON z.id_area = a.id_area
                    WHERE z.id_zona = :id_zona";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_zona', $id_zona, PDO::PARAM_INT);
            $stmt->execute();

            // Devuelve una sola fila o null si no existe
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error al obtener zona: " . $e->getMessage();
            return null;
        }
    }
}
?>
