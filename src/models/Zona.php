<?php
class Zona {
    private $conn;
    private $table = 'zonas';

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Listar todas las zonas
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

    // Obtener una zona por su ID
    public function obtenerPorId($id_zona) {
        try {
            $sql = "SELECT * FROM " . $this->table . " WHERE id_zona = :id_zona";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_zona', $id_zona, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    // Crear una nueva zona (solo si agregas más campos)
    public function crear($id_zona = null) {
        try {
            // Si la tabla 'zonas' solo tiene el campo id_zona como AUTO_INCREMENT,
            // no es necesario especificar columnas ni valores en el INSERT.
            // Preparamos la consulta SQL para insertar una nueva zona.
            $sql = "INSERT INTO " . $this->table . " () VALUES ()";
            $stmt = $this->conn->prepare($sql); // Preparamos la sentencia
            $stmt->execute(); // Ejecutamos la sentencia

            // Retornamos un mensaje de éxito y el id_zona generado automáticamente
            return [
                "mensaje" => "Zona creada exitosamente.",
                "id_zona" => $this->conn->lastInsertId()
            ];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    // Eliminar una zona
    public function eliminar($id_zona) {
        try {
            $sql = "DELETE FROM " . $this->table . " WHERE id_zona = :id_zona";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_zona', $id_zona, PDO::PARAM_INT);
            $stmt->execute();
            return ["mensaje" => "Zona eliminada correctamente."];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
        //quitar este comentario
    }
}
?>