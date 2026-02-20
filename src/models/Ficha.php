<?php
// Clase Ficha para manejar operaciones CRUD sobre la tabla 'fichas'
class Ficha {
    private $conn;
    private $table = "fichas";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Función para listar todas las fichas con información relacionada
    public function listar() {
        $sql = "SELECT f.*, 
                       i.nombre_instructor as nombre_lider,
                       i.tipo_documento as tipo_doc_lider,
                       i.numero_documento as num_doc_lider,
                       i.correo_electronico as correo_lider
                FROM " . $this->table . " f
                LEFT JOIN instructores i ON f.lider_grupo = i.id_instructor
                ORDER BY f.numero_ficha DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Función para obtener una ficha por su ID
    public function obtenerPorId($id_ficha) {
        $sql = "SELECT f.*, 
                       i.nombre_instructor as nombre_lider,
                       i.tipo_documento as tipo_doc_lider,
                       i.numero_documento as num_doc_lider,
                       i.correo_electronico as correo_lider
                FROM " . $this->table . " f
                LEFT JOIN instructores i ON f.lider_grupo = i.id_instructor
                WHERE f.id_ficha = :id_ficha";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_ficha', $id_ficha);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Función para obtener programas de formación activos
    public function obtenerProgramas() {
        $sql = "SELECT id_programa, nombre_programa, nivel_formacion 
                FROM programas 
                WHERE estado = 1 
                ORDER BY nombre_programa";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Función para obtener instructores activos (posibles líderes de grupo)
    public function obtenerInstructores() {
        $sql = "SELECT id_instructor, nombre_instructor, tipo_documento, 
                       numero_documento, correo_electronico, tipo_contrato
                FROM instructores 
                WHERE estado = 1 AND es_interno = 0
                ORDER BY nombre_instructor";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Función para crear una nueva ficha
    public function crear($numero_ficha, $jornada, $modalidad, $lider_grupo = null) {
        // Verificar si ya existe una ficha con el mismo número
        $sqlCheck = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE numero_ficha = :numero_ficha";
        $stmtCheck = $this->conn->prepare($sqlCheck);
        $stmtCheck->bindParam(':numero_ficha', $numero_ficha);
        $stmtCheck->execute();
        $result = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        
        if ($result['total'] > 0) {
            throw new Exception("Ya existe una ficha con el número: " . $numero_ficha);
        }

        $sql = "INSERT INTO " . $this->table . " 
                (numero_ficha, jornada, modalidad, lider_grupo) 
                VALUES (:numero_ficha, :jornada, :modalidad, :lider_grupo)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':numero_ficha', $numero_ficha);
        $stmt->bindParam(':jornada', $jornada);
        $stmt->bindParam(':modalidad', $modalidad);
        $stmt->bindParam(':lider_grupo', $lider_grupo);
        $stmt->execute();
        
        return $this->conn->lastInsertId();
    }

    // Función para actualizar una ficha existente
    public function actualizar($id_ficha, $numero_ficha, $jornada, $modalidad, $lider_grupo = null) {
        // Verificar si ya existe otra ficha con el mismo número (excluyendo la actual)
        $sqlCheck = "SELECT COUNT(*) as total FROM " . $this->table . " 
                     WHERE numero_ficha = :numero_ficha AND id_ficha != :id_ficha";
        $stmtCheck = $this->conn->prepare($sqlCheck);
        $stmtCheck->bindParam(':numero_ficha', $numero_ficha);
        $stmtCheck->bindParam(':id_ficha', $id_ficha);
        $stmtCheck->execute();
        $result = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        
        if ($result['total'] > 0) {
            throw new Exception("Ya existe otra ficha con el número: " . $numero_ficha);
        }

        $sql = "UPDATE " . $this->table . " 
                SET numero_ficha = :numero_ficha,
                    jornada = :jornada,
                    modalidad = :modalidad,
                    lider_grupo = :lider_grupo
                WHERE id_ficha = :id_ficha";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_ficha', $id_ficha);
        $stmt->bindParam(':numero_ficha', $numero_ficha);
        $stmt->bindParam(':jornada', $jornada);
        $stmt->bindParam(':modalidad', $modalidad);
        $stmt->bindParam(':lider_grupo', $lider_grupo);
        $stmt->execute();
    }

    // Función para eliminar una ficha por su ID
    public function eliminar($id_ficha) {
        // Verificar si la ficha tiene horarios asociados
        $sqlCheck = "SELECT COUNT(*) as total FROM horarios WHERE id_ficha = :id_ficha";
        $stmtCheck = $this->conn->prepare($sqlCheck);
        $stmtCheck->bindParam(':id_ficha', $id_ficha);
        $stmtCheck->execute();
        $result = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        
        if ($result['total'] > 0) {
            throw new Exception("No se puede eliminar la ficha porque tiene horarios asociados");
        }

        $sql = "DELETE FROM " . $this->table . " WHERE id_ficha = :id_ficha";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_ficha', $id_ficha);
        $stmt->execute();
    }

    // Función para cambiar el líder de grupo
    public function cambiarLider($id_ficha, $id_instructor) {
        $sql = "UPDATE " . $this->table . " 
                SET lider_grupo = :lider_grupo 
                WHERE id_ficha = :id_ficha";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':lider_grupo', $id_instructor);
        $stmt->bindParam(':id_ficha', $id_ficha);
        $stmt->execute();
    }

    // Función para buscar fichas por número
    public function buscarPorNumero($numero_ficha) {
        $sql = "SELECT f.*, 
                       i.nombre_instructor as nombre_lider
                FROM " . $this->table . " f
                LEFT JOIN instructores i ON f.lider_grupo = i.id_instructor
                WHERE f.numero_ficha LIKE :numero_ficha
                ORDER BY f.numero_ficha";
        $stmt = $this->conn->prepare($sql);
        $numero = "%{$numero_ficha}%";
        $stmt->bindParam(':numero_ficha', $numero);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>