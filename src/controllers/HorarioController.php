<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

include_once __DIR__ . '/../../config/database.php';
include_once __DIR__ . '/../models/Horario.php';

// Verificar conexión
if (!isset($conn)) {
    echo json_encode(['error' => 'No se pudo establecer conexión con la base de datos']);
    exit;
}
class Horario {
    private $conn;
    private $table = "horarios";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Listar todos los horarios
    public function listar() {
        try {
            $sql = "SELECT h.id_horario, h.dia, h.hora_inicio, h.hora_fin,
                           z.nombre AS zona, f.codigo AS ficha, i.nombre AS instructor
                    FROM horarios h
                    INNER JOIN zonas z ON h.id_zona = z.id_zona
                    INNER JOIN fichas f ON h.id_ficha = f.id_ficha
                    INNER JOIN instructores i ON h.id_instructor = i.id_instructor";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    // Obtener un horario por ID
    public function obtenerPorId($id_horario) {
        try {
            $sql = "SELECT * FROM " . $this->table . " WHERE id_horario = :id_horario";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_horario', $id_horario, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    // Crear horario
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
            return ['mensaje' => 'Horario creado correctamente'];
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    // Actualizar horario
    public function actualizar($id_horario, $dia, $hora_inicio, $hora_fin, $id_zona, $id_ficha, $id_instructor) {
        try {
            $sql = "UPDATE " . $this->table . " 
                    SET dia = :dia, hora_inicio = :hora_inicio, hora_fin = :hora_fin,
                        id_zona = :id_zona, id_ficha = :id_ficha, id_instructor = :id_instructor
                    WHERE id_horario = :id_horario";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_horario', $id_horario);
            $stmt->bindParam(':dia', $dia);
            $stmt->bindParam(':hora_inicio', $hora_inicio);
            $stmt->bindParam(':hora_fin', $hora_fin);
            $stmt->bindParam(':id_zona', $id_zona);
            $stmt->bindParam(':id_ficha', $id_ficha);
            $stmt->bindParam(':id_instructor', $id_instructor);
            $stmt->execute();
            return ['mensaje' => 'Horario actualizado correctamente'];
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    // Eliminar horario
    public function eliminar($id_horario) {
        try {
            $sql = "DELETE FROM " . $this->table . " WHERE id_horario = :id_horario";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_horario', $id_horario, PDO::PARAM_INT);
            $stmt->execute();
            return ['mensaje' => 'Horario eliminado correctamente'];
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
?>
