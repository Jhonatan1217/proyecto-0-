<?php
require_once "../../config/database.php";
// Clase Trimestralizacion para manejar operaciones CRUD sobre la tabla 'trimestralizacion'
class Trimestralizacion {
    private $conn;
    private $table = "trimestralizacion";

    public function __construct($db) {
        $this->conn = $db;
    }
    // Funcion para listar las trimestralizaciones por zona
    public function listar($id_zona = null) {
        try {
            if (!$id_zona) {
                return ["error" => "Debe especificar una zona para listar trimestralizaciones."];
            }

            $sql = "SELECT 
                        t.id_trimestral,
                        h.id_horario,
                        h.dia,
                        h.hora_inicio,
                        h.hora_fin,
                        z.id_zona,
                        z.nombre_zona,
                        f.id_ficha,
                        f.numero_ficha,
                        f.nivel_ficha,
                        i.id_instructor,
                        i.nombre_instructor,
                        i.tipo_instructor,
                        c.id_competencia,
                    FROM {$this->table} t
                    INNER JOIN horarios h ON t.id_horario = h.id_horario
                    LEFT JOIN zonas z ON h.id_zona = z.id_zona
                    LEFT JOIN fichas f ON h.id_ficha = f.id_ficha
                    LEFT JOIN instructores i ON h.id_instructor = i.id_instructor
                    LEFT JOIN competencias c ON h.id_competencia = c.id_competencia
                    WHERE h.id_zona = :id_zona
                    ORDER BY h.hora_inicio ASC";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":id_zona", $id_zona, PDO::PARAM_INT);
            $stmt->execute();
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($datos)) {
                return ["mensaje" => "No hay registros para esta zona."];
            }

            return $datos;

        } catch (PDOException $e) {
            return ["error" => "Error al listar: " . $e->getMessage()];
        }
    }

    // Funcion para obtener una trimestralizacion por su ID
    public function obtenerPorId($id_trimestral) {
        try {
            $sql = "SELECT 
                        t.id_trimestral,
                        h.id_horario,
                        h.dia,
                        h.hora_inicio,
                        h.hora_fin,
                        z.id_zona,
                        f.id_ficha,
                        f.numero_ficha,
                        f.nivel_ficha,
                        i.id_instructor,
                        i.nombre_instructor,
                        i.tipo_instructor,
                        c.id_competencia,
                    FROM {$this->table} t
                    INNER JOIN horarios h ON t.id_horario = h.id_horario
                    INNER JOIN zonas z ON h.id_zona = z.id_zona
                    INNER JOIN fichas f ON h.id_ficha = f.id_ficha
                    INNER JOIN instructores i ON h.id_instructor = i.id_instructor
                    INNER JOIN competencias c ON h.id_competencia = c.id_competencia
                    WHERE t.id_trimestral = :id";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":id", $id_trimestral, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => "Error al obtener: " . $e->getMessage()];
        }
    }

    // Funcion para crear una nueva trimestralizacion
    public function crear($id_horario) {
        try {
            // Verificar que el horario exista
            $check = $this->conn->prepare("SELECT COUNT(*) FROM horarios WHERE id_horario = :id");
            $check->bindParam(":id", $id_horario, PDO::PARAM_INT);
            $check->execute();

            if (!$check->fetchColumn()) {
                throw new PDOException("El horario con ID $id_horario no existe.");
            }

            // Insertar trimestralización
            $sql = "INSERT INTO {$this->table} (id_horario) VALUES (:id_horario)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":id_horario", $id_horario, PDO::PARAM_INT);
            $stmt->execute();

            return [
                "success" => true,
                "message" => "Trimestralización creada correctamente",
                "id_trimestral" => $this->conn->lastInsertId()
            ];

        } catch (PDOException $e) {
            return ["error" => "Error al crear trimestralización: " . $e->getMessage()];
        }
    }

    // Funcion para actualizar una trimestralizacion completa por zona
    public function actualizar($id_zona, $data) {
        try {
            foreach ($data as $fila) {
                // Actualizar horario
                $stmt = $this->conn->prepare("
                    UPDATE horarios
                    SET dia = :dia,
                        hora_inicio = :hora_inicio,
                        hora_fin = :hora_fin
                    WHERE id_horario = :id_horario AND id_zona = :id_zona
                ");
                $stmt->execute([
                    ':dia' => $fila['dia'],
                    ':hora_inicio' => $fila['hora_inicio'],
                    ':hora_fin' => $fila['hora_fin'],
                    ':id_horario' => $fila['id_horario'],
                    ':id_zona' => $id_zona
                ]);

                // Actualizar ficha
                $stmt = $this->conn->prepare("
                    UPDATE fichas f
                    INNER JOIN horarios h ON f.id_ficha = h.id_ficha
                    SET f.numero_ficha = :numero_ficha,
                        f.nivel_ficha = :nivel_ficha
                    WHERE h.id_horario = :id_horario
                ");
                $stmt->execute([
                    ':numero_ficha' => $fila['numero_ficha'],
                    ':nivel_ficha' => $fila['nivel_ficha'] ?? '',
                    ':id_horario' => $fila['id_horario']
                ]);

                // Actualizar instructor
                $stmt = $this->conn->prepare("
                    UPDATE instructores i
                    INNER JOIN horarios h ON i.id_instructor = h.id_instructor
                    SET i.nombre_instructor = :nombre_instructor,
                        i.tipo_instructor = :tipo_instructor
                    WHERE h.id_horario = :id_horario
                ");
                $stmt->execute([
                    ':nombre_instructor' => $fila['nombre_instructor'],
                    ':tipo_instructor' => $fila['tipo_instructor'],
                    ':id_horario' => $fila['id_horario']
                ]);

                // Actualizar competencia
                $stmt = $this->conn->prepare("
                    UPDATE competencias c
                    INNER JOIN horarios h ON c.id_competencia = h.id_competencia
                    WHERE h.id_horario = :id_horario
                ");
                $stmt->execute([
                    ':descripcion' => $fila['descripcion'],
                    ':id_horario' => $fila['id_horario']
                ]);
            }

            return ["success" => true, "message" => "Trimestralización de la zona actualizada correctamente"];

        } catch (PDOException $e) {
            return ["error" => "Error al actualizar: " . $e->getMessage()];
        }
    }

    // Funcion para eliminar una trimestralizacion por zona
    public function eliminarPorZona($id_zona) {
        try {
            $this->conn->beginTransaction();

            // Funcion para marcar todos los horarios de la zona como inactivos (estado = 0)
            $stmt = $this->conn->prepare("UPDATE horarios SET estado = 0 WHERE id_zona = :id_zona");
            $stmt->bindParam(':id_zona', $id_zona, PDO::PARAM_INT);
            $stmt->execute();

            $this->conn->commit();
            return ["success" => true, "message" => "Trimestralización marcada como inactiva correctamente para la zona."];
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return ["error" => "Error al marcar como inactivo: " . $e->getMessage()];
        }
    }
}
?>
