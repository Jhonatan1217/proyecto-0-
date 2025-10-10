<?php
require_once "../../config/database.php";

class Trimestralizacion {
    private $conn;
    private $table = "trimestralizacion";

    public function __construct($db) {
        $this->conn = $db;
    }

    // 🔹 LISTAR todas las trimestralizaciones con detalles
    public function listar() {
        try {
            $sql = "SELECT 
                        t.id_trimestral,
                        h.id_horario,
                        h.dia,
                        h.hora_inicio,
                        h.hora_fin,
                        z.id_zona,
                        f.numero_ficha,
                        f.nivel_ficha,
                        i.nombre_instructor,
                        c.descripcion
                    FROM {$this->table} t
                    INNER JOIN horarios h ON t.id_horario = h.id_horario
                    INNER JOIN zonas z ON h.id_zona = z.id_zona
                    INNER JOIN fichas f ON h.id_ficha = f.id_ficha
                    INNER JOIN instructores i ON h.id_instructor = i.id_instructor
                    INNER JOIN competencias c ON h.id_competencia = c.id_competencia
                    ORDER BY t.id_trimestral DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => "Error al listar: " . $e->getMessage()];
        }
    }

    // 🔹 OBTENER una trimestralización específica
    public function obtenerPorId($id_trimestral) {
        try {
            $sql = "SELECT 
                        t.id_trimestral,
                        h.dia,
                        h.hora_inicio,
                        h.hora_fin,
                        z.id_zona,
                        f.numero_ficha,
                        f.nivel_ficha,
                        i.nombre_instructor,
                        c.descripcion
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

    // 🔹 CREAR trimestralización (recibe un id_horario existente)
    public function crear($id_horario) {
        try {
            $check = $this->conn->prepare("SELECT COUNT(*) FROM horarios WHERE id_horario = :id");
            $check->bindParam(":id", $id_horario, PDO::PARAM_INT);
            $check->execute();

            if (!$check->fetchColumn()) {
                throw new PDOException("El horario con ID $id_horario no existe.");
            }

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

    // 🔹 ELIMINAR / REINICIAR todas las tablas relacionadas
    public function eliminar() {
        try {
            $tablas = ['trimestralizacion', 'horarios', 'fichas', 'instructores', 'competencias'];
            $this->conn->exec("SET FOREIGN_KEY_CHECKS = 0");

            foreach ($tablas as $tabla) {
                $this->conn->exec("TRUNCATE TABLE `$tabla`");
            }

            $this->conn->exec("SET FOREIGN_KEY_CHECKS = 1");

            return [
                "status" => "success",
                "mensaje" => "Las tablas se vaciaron correctamente"
            ];
        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            return [
                "status" => "error",
                "mensaje" => "Error al vaciar tablas: " . $e->getMessage()
            ];
        }
    }

    //  ACTUALIZAR Ficha, Instructor y Competencia
    public function actualizar($id_horario, $data)
    {
        try {
            $this->conn->beginTransaction();

            // Consultar relaciones del horario
            $stmt = $this->conn->prepare("
                SELECT id_ficha, id_instructor, id_competencia 
                FROM horarios 
                WHERE id_horario = :id
            ");
            $stmt->bindParam(':id', $id_horario, PDO::PARAM_INT);
            $stmt->execute();
            $rel = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$rel) {
                return [
                    "status" => "error",
                    "mensaje" => "Horario no encontrado"
                ];
            }

            // Actualizar ficha solo si hay datos válidos
            if (!empty($data['numero_ficha']) || !empty($data['nivel_ficha'])) {
                $campos = [];
                if (!empty($data['numero_ficha'])) $campos['numero_ficha'] = $data['numero_ficha'];
                if (!empty($data['nivel_ficha'])) $campos['nivel_ficha'] = $data['nivel_ficha'];

                $sets = [];
                foreach ($campos as $col => $val) {
                    $sets[] = "$col = :$col";
                }

                $sqlFicha = "UPDATE fichas SET " . implode(", ", $sets) . " WHERE id_ficha = :id_ficha";
                $stmt = $this->conn->prepare($sqlFicha);
                foreach ($campos as $col => &$val) {
                    $stmt->bindParam(":$col", $val);
                }
                $stmt->bindParam(':id_ficha', $rel['id_ficha'], PDO::PARAM_INT);
                $stmt->execute();
            }

            //  Actualizar instructor solo si hay datos válidos
            if (!empty($data['nombre_instructor']) || !empty($data['tipo_instructor'])) {
                $campos = [];
                if (!empty($data['nombre_instructor'])) $campos['nombre_instructor'] = $data['nombre_instructor'];
                if (!empty($data['tipo_instructor'])) $campos['tipo_instructor'] = $data['tipo_instructor'];

                $sets = [];
                foreach ($campos as $col => $val) {
                    $sets[] = "$col = :$col";
                }

                $sqlInstructor = "UPDATE instructores SET " . implode(", ", $sets) . " WHERE id_instructor = :id_instructor";
                $stmt = $this->conn->prepare($sqlInstructor);
                foreach ($campos as $col => &$val) {
                    $stmt->bindParam(":$col", $val);
                }
                $stmt->bindParam(':id_instructor', $rel['id_instructor'], PDO::PARAM_INT);
                $stmt->execute();
            }

            //  Actualizar competencia solo si hay descripción
            if (!empty($data['descripcion'])) {
                $sqlComp = "UPDATE competencias 
                            SET descripcion = :descripcion 
                            WHERE id_competencia = :id_competencia";
                $stmt = $this->conn->prepare($sqlComp);
                $stmt->bindParam(':descripcion', $data['descripcion']);
                $stmt->bindParam(':id_competencia', $rel['id_competencia'], PDO::PARAM_INT);
                $stmt->execute();
            }

            $this->conn->commit();
            return [
                "status" => "success",
                "mensaje" => "Ficha, instructor y competencia actualizados correctamente."
            ];

        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            return [
                "status" => "error",
                "mensaje" => "Error SQL: " . $e->getMessage()
            ];
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            return [
                "status" => "error",
                "mensaje" => "Error general: " . $e->getMessage()
            ];
        }
    }
}
?>
