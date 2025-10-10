<?php
// Clase Instructor para manejar operaciones CRUD sobre la tabla 'instructores'
class Instructor {
    // Conexión a la base de datos
    private $conn;
    // Nombre de la tabla
    private $table = "instructores";

    // Constructor que recibe la conexión a la base de datos
    public function __construct($db) {
        $this->conn = $db;
    }

    // Listar todos los instructores
    public function listar() {
        // Prepara la consulta SQL para seleccionar todos los instructores
        $sql = "SELECT * FROM " . $this->table;
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        // Retorna todos los resultados como un arreglo asociativo
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener un instructor por su ID
    public function obtenerPorId($id) {
        // Prepara la consulta SQL para seleccionar un instructor por su ID
        $sql = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        // Asocia el parámetro :id con el valor recibido
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        // Retorna el resultado como un arreglo asociativo
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Crear un nuevo instructor
    public function crear($nombre, $apellido, $tipo) {
        // Prepara la consulta SQL para insertar un nuevo instructor
        $sql = "INSERT INTO " . $this->table . " (nombre, apellido, tipo_instructor)
                VALUES (:nombre, :apellido, :tipo)";
        $stmt = $this->conn->prepare($sql);
        // Asocia los parámetros con los valores recibidos
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':apellido', $apellido);
        $stmt->bindParam(':tipo', $tipo);
        // Ejecuta la consulta
        $stmt->execute();
    }

    // Actualizar un instructor existente
    public function actualizar($id, $nombre, $apellido, $tipo) {
        // Prepara la consulta SQL para actualizar los datos de un instructor
        $sql = "UPDATE " . $this->table . " 
                SET nombre = :nombre, apellido = :apellido, tipo_instructor = :tipo
                WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        // Asocia los parámetros con los valores recibidos
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':apellido', $apellido);
        $stmt->bindParam(':tipo', $tipo);
        // Ejecuta la consulta
        $stmt->execute();
    }

    // Eliminar un instructor por su ID
    public function eliminar($id) {
        // Prepara la consulta SQL para eliminar un instructor por su ID
        $sql = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        // Asocia el parámetro :id con el valor recibido
        $stmt->bindParam(':id', $id);
        // Ejecuta la consulta
        $stmt->execute();
    }
    //quitar este comentario
}
?>
//borrar despues del push