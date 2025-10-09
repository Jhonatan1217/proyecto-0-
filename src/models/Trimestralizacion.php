<?php
require_once "../../config/database.php";
// Clase que maneja las operaciones CRUD para la tabla 'trimestralizacion'
class Trimestralizacion {
    // Conexión a la base de datos
    private $conn;
    // Nombre de la tabla
    private $table = "trimestralizacion";

    // Constructor que recibe la conexión a la base de datos
    public function __construct($db) {
        $this->conn = $db;
    }

    // Listar todos los registros de la tabla 'trimestralizacion'
    public function listar() {
        try {
            // Consulta SQL para seleccionar todos los registros
            $sql = "SELECT * FROM " . $this->table;
            $stmt = $this->conn->prepare($sql); // Prepara la consulta
            $stmt->execute(); // Ejecuta la consulta
            // Devuelve todos los resultados como un array asociativo
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // En caso de error, devuelve el mensaje de error
            return ["error" => $e->getMessage()];
        }
    }

    // Obtener un registro específico por su ID
    public function obtenerPorId($id_trimestral) {
        try {
            // Consulta SQL para seleccionar un registro por su ID
            $sql = "SELECT * FROM " . $this->table . " WHERE id_trimestral = :id_trimestral";
            $stmt = $this->conn->prepare($sql); // Prepara la consulta
            // Asocia el parámetro :id_trimestral con el valor recibido
            $stmt->bindParam(':id_trimestral', $id_trimestral, PDO::PARAM_INT);
            $stmt->execute(); // Ejecuta la consulta
            // Devuelve el registro encontrado como un array asociativo
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // En caso de error, devuelve el mensaje de error
            return ["error" => $e->getMessage()];
        }
    }

    // Crear un nuevo registro en la tabla 'trimestralizacion'
    public function crear($id_horario) {
        try {
            // Consulta SQL para insertar un nuevo registro
            $sql = "INSERT INTO " . $this->table . " (id_horario) VALUES (:id_horario)";
            $stmt = $this->conn->prepare($sql); // Prepara la consulta
            // Asocia el parámetro :id_horario con el valor recibido
            $stmt->bindParam(':id_horario', $id_horario, PDO::PARAM_INT);
            $stmt->execute(); // Ejecuta la consulta
            // Devuelve un mensaje de éxito y el ID del nuevo registro
            return [
                "mensaje" => "Trimestralización creada exitosamente.",
                "id_trimestral" => $this->conn->lastInsertId()
            ];
        } catch (PDOException $e) {
            // En caso de error, devuelve el mensaje de error
            return ["error" => $e->getMessage()];
        }
    }

    // Eliminar un registro por su ID
    public function eliminar() {
        try {
            // Listado de tablas que se deben vaciar
            $tablas = ['competencias', 'fichas', 'horarios', 'instructores', 'trimestralizacion', 'zonas'];

            // Desactivar verificación de claves foráneas
            $this->conn->beginTransaction();
            $this->conn->exec("SET FOREIGN_KEY_CHECKS = 0");

            // Vaciar todas las tablas listadas
            foreach ($tablas as $tabla) {
                $this->conn->exec("TRUNCATE TABLE `$tabla`");
            }

            // Reactivar claves foráneas
            $this->conn->exec("SET FOREIGN_KEY_CHECKS = 1");
            $this->conn->commit();

            return ["status" => "success", "mensaje" => "Todas las tablas fueron vaciadas correctamente."];
        } catch (PDOException $e) {
            // En caso de error, devuelve el mensaje de error
            $this->conn->rollBack();
            return ["status" => "error", "mensaje" => "Error al vaciar tablas: " . $e->getMessage()];
        }
    }
}
?>
