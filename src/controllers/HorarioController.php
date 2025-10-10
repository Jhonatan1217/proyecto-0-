<?php
// Establece el tipo de contenido de la respuesta como JSON y la codificación de caracteres
header('Content-Type: application/json; charset=utf-8');

// Habilita la visualización de todos los errores para facilitar la depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Incluye el archivo de configuración de la base de datos y el modelo de Horario
include_once __DIR__ . '/../../config/database.php';
include_once __DIR__ . '/../models/Horario.php';

// Verifica que la conexión a la base de datos se haya establecido correctamente
if (!isset($conn)) {
    echo json_encode(['error' => 'No se pudo establecer conexión con la base de datos']);
    exit;
}

// Definición de la clase Horario para manejar operaciones CRUD sobre la tabla 'horarios'
class Horario {
    private $conn; // Conexión a la base de datos
    private $table = "horarios"; // Nombre de la tabla

    // Constructor que recibe la conexión a la base de datos
    public function __construct($db) {
        $this->conn = $db;
    }

    // Método para listar todos los horarios con información relacionada (zona, ficha, instructor)
    public function listar() {
        try {
            $sql = "SELECT h.id_horario,
                        h.dia,
                        h.hora_inicio,
                        h.hora_fin,
                        h.id_zona,
                        h.id_ficha,
                        c.id_competencia,
                        i.nombre_instructor,
                        i.apellido_instructor,
                        f.id_ficha AS ficha
                    FROM horarios h
                    INNER JOIN zonas z ON h.id_zona = z.id_zona
                    INNER JOIN competencias c ON h.id_competencia = c.id_competencia
                    INNER JOIN fichas f ON h.id_ficha = f.id_ficha
                    INNER JOIN instructores i ON h.id_instructor = i.id_instructor;
                    ";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            // Devuelve todos los resultados como un array asociativo
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // En caso de error, devuelve el mensaje de error
            return ['error' => $e->getMessage()];
        }
    }

    // Método para obtener un horario específico por su ID
    public function obtenerPorId($id_horario) {
        try {
            $sql = "SELECT * FROM " . $this->table . " WHERE id_horario = :id_horario";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_horario', $id_horario, PDO::PARAM_INT);
            $stmt->execute();
            // Devuelve el horario encontrado como un array asociativo
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // En caso de error, devuelve el mensaje de error
            return ['error' => $e->getMessage()];
        }
    }

    // Método para crear un nuevo horario
    public function crear($dia, $hora_inicio, $hora_fin, $id_zona, $id_ficha, $id_instructor) {
        try {
            $sql = "INSERT INTO " . $this->table . "
                    (dia, hora_inicio, hora_fin, id_zona, id_ficha, id_instructor)
                    VALUES (:dia, :hora_inicio, :hora_fin, :id_zona, :id_ficha, :id_instructor)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':dia', $dia);
            $stmt->bindParam(':hora_inicio', $hora_inicio);
            $stmt->bindParam(':hora_fin', $hora_fin);
            $stmt->bindParam(':id_zona', $id_zona);
            $stmt->bindParam(':id_ficha', $id_ficha);
            $stmt->bindParam(':id_instructor', $id_instructor);
            $stmt->execute();
            // Devuelve un mensaje de éxito
            return ['mensaje' => 'Horario creado correctamente'];
        } catch (PDOException $e) {
            // En caso de error, devuelve el mensaje de error
            return ['error' => $e->getMessage()];
        }
    }

    // Método para actualizar solo ficha, instructor y competencia
    public function actualizar($id_horario, $id_ficha, $id_instructor, $id_competencia) {
        try {
            $sql = "UPDATE " . $this->table . "
                    SET id_ficha = :id_ficha,
                        id_instructor = :id_instructor,
                        id_competencia = :id_competencia
                    WHERE id_horario = :id_horario";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_horario', $id_horario);
            $stmt->bindParam(':id_ficha', $id_ficha);
            $stmt->bindParam(':id_instructor', $id_instructor);
            $stmt->bindParam(':id_competencia', $id_competencia);
            $stmt->execute();

            // Devuelve un mensaje de éxito
            return ['mensaje' => 'Horario actualizado correctamente'];
        } catch (PDOException $e) {
            // En caso de error, devuelve el mensaje de error
            return ['error' => $e->getMessage()];
        }
    }


    // Método para eliminar un horario por su ID
    public function eliminar($id_horario) {
        try {
            $sql = "DELETE FROM " . $this->table . " WHERE id_horario = :id_horario";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_horario', $id_horario, PDO::PARAM_INT);
            $stmt->execute();
            // Devuelve un mensaje de éxito
            return ['mensaje' => 'Horario eliminado correctamente'];
        } catch (PDOException $e) {
            // En caso de error, devuelve el mensaje de error
            return ['error' => $e->getMessage()];
        }
        //quitar este comentario
    }
}
?>
