<?php
// Clase Ficha para manejar operaciones CRUD sobre la tabla 'fichas' en la base de datos
class Ficha {
    private $conn;
    private $table = "fichas";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Funcion para listar todas las fichas
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

    // Funcion para obtener una ficha por su ID
    public function obtenerPorId($id_ficha) {
        try {
            $sql = "SELECT * FROM " . $this->table . " WHERE id_ficha = :id_ficha";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_ficha', $id_ficha, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    // Funcion para crear una nueva ficha
    public function crear($numero_ficha, $nivel_ficha = "tecnico") {
        try {
            // Evita duplicar fichas con el mismo número
            $check = $this->conn->prepare("SELECT id_ficha FROM " . $this->table . " WHERE numero_ficha = :num");
            $check->bindParam(':num', $numero_ficha, PDO::PARAM_INT);
            $check->execute();

            $existe = $check->fetch(PDO::FETCH_ASSOC);
            if ($existe) {
                return ["id_ficha" => $existe['id_ficha'], "mensaje" => "La ficha ya existe."];
            }

            // Funcion para insertar la nueva ficha
            $sql = "INSERT INTO " . $this->table . " (numero_ficha, nivel_ficha) VALUES (:num, :nivel)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':num', $numero_ficha, PDO::PARAM_INT);
            $stmt->bindParam(':nivel', $nivel_ficha, PDO::PARAM_STR);
            $stmt->execute();

            // Funcion para retornar el ID autogenerado
            return [
                "id_ficha" => (int)$this->conn->lastInsertId(),
                "mensaje" => "Ficha creada exitosamente."
            ];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    // Funcion para eliminar una ficha
    public function eliminar($id_ficha) {
        try {
            $sql = "DELETE FROM " . $this->table . " WHERE id_ficha = :id_ficha";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_ficha', $id_ficha, PDO::PARAM_INT);
            $stmt->execute();
            return ["mensaje" => "Ficha eliminada correctamente."];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }
}
?>