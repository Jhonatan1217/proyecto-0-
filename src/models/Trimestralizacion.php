<?php
require_once "../../config/database.php";
// Clase que maneja las operaciones CRUD para la tabla 'trimestralizacion'
class Trimestralizacion {
    private $conn;
    private $table = "trimestralizacion";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Listar todas las trimestralizaciones
    public function listar() {
        try {
            $sql = "SELECT t.id_trimestral, h.id_horario, h.dia, h.hora_inicio, h.hora_fin, h.id_zona, h.id_ficha, h.id_instructor
                    FROM {$this->table} t
                    INNER JOIN horarios h ON t.id_horario = h.id_horario";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    // Obtener una trimestralización específica
    public function obtenerPorId($id_trimestral) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE id_trimestral = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":id", $id_trimestral, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    // Crear una nueva trimestralización
    public function crear($data) {
    try {
        // Crear el nuevo horario
        $sqlHorario = "INSERT INTO horarios (dia, hora_inicio, hora_fin, id_zona, id_ficha, id_instructor)
                       VALUES (:dia, :hora_inicio, :hora_fin, :id_zona, :id_ficha, :id_instructor)";
        $stmtH = $this->conn->prepare($sqlHorario);
        $stmtH->bindParam(":dia", $data['dia']);
        $stmtH->bindParam(":hora_inicio", $data['hora_inicio']);
        $stmtH->bindParam(":hora_fin", $data['hora_fin']);
        $stmtH->bindParam(":id_zona", $data['id_zona'], PDO::PARAM_INT);
        $stmtH->bindParam(":id_ficha", $data['id_ficha'], PDO::PARAM_INT);
        $stmtH->bindParam(":id_instructor", $data['id_instructor'], PDO::PARAM_INT);
        $stmtH->execute();

        $id_horario = $this->conn->lastInsertId();

        // Crear la trimestralización asociada
        $sqlTrimestral = "INSERT INTO {$this->table} (id_horario) VALUES (:id_horario)";
        $stmtT = $this->conn->prepare($sqlTrimestral);
        $stmtT->bindParam(":id_horario", $id_horario, PDO::PARAM_INT);
        $stmtT->execute();

        $id_trimestral = $this->conn->lastInsertId();

        return [
            "success" => true,
            "message" => "Horario y trimestralización creados correctamente",
            "id_horario" => $id_horario,
            "id_trimestral" => $id_trimestral
        ];
    } catch (PDOException $e) {
        return ["error" => $e->getMessage()];
    }
}
    //******************************************************/ Eliminar datos DB ******************************************************
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
