<?php
class Horario {
    private $conn;
    private $table = "horarios";

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Crear un nuevo horario
     * Recibe todos los parámetros del formulario.
     * El campo estado se activa por defecto (1).
     */
    public function crearHorario($dia, $hora_inicio, $hora_fin, $id_zona, $id_area, $id_ficha, $id_instructor, $id_competencia, $numero_trimestre) {
        try {
            $sql = "INSERT INTO " . $this->table . " 
                    (dia, hora_inicio, hora_fin, id_zona, id_area, id_ficha, id_instructor, id_competencia, numero_trimestre, estado)
                    VALUES (:dia, :hora_inicio, :hora_fin, :id_zona, :id_area, :id_ficha, :id_instructor, :id_competencia, :numero_trimestre, 1)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':dia', $dia);
            $stmt->bindParam(':hora_inicio', $hora_inicio);
            $stmt->bindParam(':hora_fin', $hora_fin);
            $stmt->bindParam(':id_zona', $id_zona);
            $stmt->bindParam(':id_area', $id_area);
            $stmt->bindParam(':id_ficha', $id_ficha);
            $stmt->bindParam(':id_instructor', $id_instructor);
            $stmt->bindParam(':id_competencia', $id_competencia);
            $stmt->bindParam(':numero_trimestre', $numero_trimestre);

            if ($stmt->execute()) {
                return true;
            }
            return false;
        } catch (PDOException $e) {
            echo "Error al crear horario: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Actualizar un horario existente
     * Solo actualiza: número ficha, trimestre, instructor y competencia
     */
    public function actualizarHorario($id_horario, $id_ficha, $numero_trimestre, $id_instructor, $id_competencia) {
        try {
            $sql = "UPDATE " . $this->table . " 
                    SET id_ficha = :id_ficha, 
                        numero_trimestre = :numero_trimestre, 
                        id_instructor = :id_instructor, 
                        id_competencia = :id_competencia
                    WHERE id_horario = :id_horario";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_horario', $id_horario);
            $stmt->bindParam(':id_ficha', $id_ficha);
            $stmt->bindParam(':numero_trimestre', $numero_trimestre);
            $stmt->bindParam(':id_instructor', $id_instructor);
            $stmt->bindParam(':id_competencia', $id_competencia);

            if ($stmt->execute()) {
                return true;
            }
            return false;
        } catch (PDOException $e) {
            echo "Error al actualizar horario: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Inhabilitar (limpiar) todos los horarios de una zona
     * Cambia el estado a 0 en todos los horarios con ese id_zona
     */
    public function inhabilitarPorZona($id_zona) {
        try {
            $sql = "UPDATE " . $this->table . " 
                    SET estado = 0 
                    WHERE id_zona = :id_zona";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_zona', $id_zona);

            if ($stmt->execute()) {
                return true;
            }
            return false;
        } catch (PDOException $e) {
            echo "Error al inhabilitar horarios por zona: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Activar un horario específico
     * Restaura el estado a 1.
     */
    public function activarHorario($id_horario) {
        try {
            $sql = "UPDATE " . $this->table . " 
                    SET estado = 1 
                    WHERE id_horario = :id_horario";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_horario', $id_horario);

            if ($stmt->execute()) {
                return true;
            }
            return false;
        } catch (PDOException $e) {
            echo "Error al activar horario: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Obtener todos los horarios activos o inactivos
     * (Útil para listar en el frontend)
     */
    public function listarHorarios($estado = 1) {
        try {
            $sql = "SELECT h.*, 
                        a.nombre_area,
                        z.id_zona,
                        f.numero_ficha,
                        f.nivel_ficha,
                        i.nombre_instructor,
                        c.descripcion AS competencia,
                        t.estado AS estado_trimestre
                    FROM horarios h
                    LEFT JOIN areas a ON h.id_area = a.id_area
                    LEFT JOIN zonas z ON h.id_zona = z.id_zona
                    LEFT JOIN fichas f ON h.id_ficha = f.id_ficha
                    LEFT JOIN instructores i ON h.id_instructor = i.id_instructor
                    LEFT JOIN competencias c ON h.id_competencia = c.id_competencia
                    LEFT JOIN trimestre t ON h.numero_trimestre = t.numero_trimestre
                    WHERE h.estado = :estado";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':estado', $estado);
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            echo "Error al listar horarios: " . $e->getMessage();
            return null;
        }
    }
}
?>
