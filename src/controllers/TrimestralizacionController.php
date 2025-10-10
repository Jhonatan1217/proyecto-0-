<?php
class Actualizar
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function actualizar($id_horario, $data)
    {
        try {
            $this->conn->beginTransaction();

            
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

            //  Actualizar ficha
            if (isset($data['numero_ficha']) || isset($data['nivel_ficha'])) {
                $stmt = $this->conn->prepare("
                    UPDATE fichas
                    SET numero_ficha = :numero_ficha,
                        nivel_ficha = :nivel_ficha
                    WHERE id_ficha = :id_ficha
                ");
                $stmt->bindParam(':numero_ficha', $data['numero_ficha']);
                $stmt->bindParam(':nivel_ficha', $data['nivel_ficha']);
                $stmt->bindParam(':id_ficha', $rel['id_ficha'], PDO::PARAM_INT);
                $stmt->execute();
            }

            // Actualizar instructor
            if (isset($data['nombre_instructor']) || isset($data['tipo_instructor'])) {
                $stmt = $this->conn->prepare("
                    UPDATE instructores
                    SET nombre_instructor = :nombre_instructor,
                        tipo_instructor = :tipo_instructor
                    WHERE id_instructor = :id_instructor
                ");
                $stmt->bindParam(':nombre_instructor', $data['nombre_instructor']);
                $stmt->bindParam(':tipo_instructor', $data['tipo_instructor']);
                $stmt->bindParam(':id_instructor', $rel['id_instructor'], PDO::PARAM_INT);
                $stmt->execute();
            }

            // ✅ Actualizar competencia
            if (isset($data['descripcion'])) {
                $stmt = $this->conn->prepare("
                    UPDATE competencias
                    SET descripcion = :descripcion
                    WHERE id_competencia = :id_competencia
                ");
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
                "mensaje" => "Error: " . $e->getMessage()
            ];
        }
    }
}
?>
