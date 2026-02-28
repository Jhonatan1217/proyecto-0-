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
                       u.nombre_completo as nombre_lider,
                       u.tipo_documento as tipo_doc_lider,
                       u.numero_documento as num_doc_lider,
                       u.correo_electronico as correo_lider,
                       u.cargo as cargo_lider
                FROM fichas f
                LEFT JOIN usuarios u ON f.id_lider_grupo = u.id_usuario
                ORDER BY f.numero_ficha DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Función para obtener una ficha por su ID
    public function obtenerPorId($id_ficha) {
        $sql = "SELECT f.*, 
                       u.nombre_completo as nombre_lider,
                       u.tipo_documento as tipo_doc_lider,
                       u.numero_documento as num_doc_lider,
                       u.correo_electronico as correo_lider,
                       u.cargo as cargo_lider,
                       u.id_area as area_lider
                FROM fichas f
                LEFT JOIN usuarios u ON f.id_lider_grupo = u.id_usuario
                WHERE f.id_ficha = :id_ficha";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_ficha', $id_ficha);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Función para verificar si ya existe una ficha con el mismo número
    public function existeNumeroFicha($numero_ficha) {
    $sql = "SELECT COUNT(*) as total 
            FROM fichas 
            WHERE numero_ficha = :numero_ficha";

    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':numero_ficha', $numero_ficha);
    $stmt->execute();

    $res = $stmt->fetch(PDO::FETCH_ASSOC);

    return $res['total'] > 0;
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
        $sql = "SELECT id_usuario as id_instructor, 
                       nombre_completo as nombre_instructor, 
                       tipo_documento, 
                       numero_documento, 
                       correo_electronico,
                       tipo_contrato,
                       tipo_instructor,
                       id_area
                FROM usuarios 
                WHERE estado = 1 AND cargo = 'INSTRUCTOR'
                ORDER BY nombre_completo";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Función para crear una nueva ficha
    public function crear($numero_ficha, $jornada, $modalidad, $id_lider_grupo) {
        // Verificar si ya existe una ficha con el mismo número
        $sqlCheck = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE numero_ficha = :numero_ficha";
        $stmtCheck = $this->conn->prepare($sqlCheck);
        $stmtCheck->bindParam(':numero_ficha', $numero_ficha);
        $stmtCheck->execute();
        $result = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        
        if ($result['total'] > 0) {
            throw new Exception("Ya existe una ficha con el número: " . $numero_ficha);
        }

        // Verificar que el líder de grupo existe y es instructor
        $sqlCheckInstructor = "SELECT COUNT(*) as total FROM usuarios 
                               WHERE id_usuario = :id_usuario AND cargo = 'INSTRUCTOR' AND estado = 1";
        $stmtCheckIns = $this->conn->prepare($sqlCheckInstructor);
        $stmtCheckIns->bindParam(':id_usuario', $id_lider_grupo);
        $stmtCheckIns->execute();
        $resultIns = $stmtCheckIns->fetch(PDO::FETCH_ASSOC);
        
        if ($resultIns['total'] == 0) {
            throw new Exception("El líder de grupo seleccionado no es válido o no existe");
        }

        $sql = "INSERT INTO " . $this->table . " 
                (numero_ficha, jornada, modalidad, id_lider_grupo, estado) 
                VALUES (:numero_ficha, :jornada, :modalidad, :id_lider_grupo, 1)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':numero_ficha', $numero_ficha);
        $stmt->bindParam(':jornada', $jornada);
        $stmt->bindParam(':modalidad', $modalidad);
        $stmt->bindParam(':id_lider_grupo', $id_lider_grupo);
        $stmt->execute();
        
        return $this->conn->lastInsertId();
    }

    // Función para actualizar una ficha existente
    public function actualizar($id_ficha, $numero_ficha, $jornada, $modalidad, $id_lider_grupo) {
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

        // Verificar que el líder de grupo existe y es instructor (si se proporciona)
        if ($id_lider_grupo) {
            $sqlCheckInstructor = "SELECT COUNT(*) as total FROM usuarios 
                                   WHERE id_usuario = :id_usuario AND cargo = 'INSTRUCTOR' AND estado = 1";
            $stmtCheckIns = $this->conn->prepare($sqlCheckInstructor);
            $stmtCheckIns->bindParam(':id_usuario', $id_lider_grupo);
            $stmtCheckIns->execute();
            $resultIns = $stmtCheckIns->fetch(PDO::FETCH_ASSOC);
            
            if ($resultIns['total'] == 0) {
                throw new Exception("El líder de grupo seleccionado no es válido o no existe");
            }
        }

        $sql = "UPDATE " . $this->table . " 
                SET numero_ficha = :numero_ficha,
                    jornada = :jornada,
                    modalidad = :modalidad,
                    id_lider_grupo = :id_lider_grupo
                WHERE id_ficha = :id_ficha";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_ficha', $id_ficha);
        $stmt->bindParam(':numero_ficha', $numero_ficha);
        $stmt->bindParam(':jornada', $jornada);
        $stmt->bindParam(':modalidad', $modalidad);
        $stmt->bindParam(':id_lider_grupo', $id_lider_grupo);
        $stmt->execute();
    }

    // Función para eliminar una ficha por su ID (cambio lógico - desactivar)
    public function eliminar($id_ficha) {
        // Verificar si la ficha tiene horarios asociados
        $sqlCheck = "SELECT COUNT(*) as total FROM horario WHERE id_ficha = :id_ficha";
        $stmtCheck = $this->conn->prepare($sqlCheck);
        $stmtCheck->bindParam(':id_ficha', $id_ficha);
        $stmtCheck->execute();
        $result = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        
        if ($result['total'] > 0) {
            throw new Exception("No se puede eliminar la ficha porque tiene horarios asociados");
        }

        // Cambiamos DELETE por UPDATE de estado (borrado lógico)
        $sql = "UPDATE " . $this->table . " SET estado = 0 WHERE id_ficha = :id_ficha";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_ficha', $id_ficha);
        $stmt->execute();
    }

    // Función para cambiar el líder de grupo
    public function cambiarLider($id_ficha, $id_usuario) {
        // Verificar que el usuario existe y es instructor
        $sqlCheck = "SELECT COUNT(*) as total FROM usuarios 
                     WHERE id_usuario = :id_usuario AND cargo = 'INSTRUCTOR' AND estado = 1";
        $stmtCheck = $this->conn->prepare($sqlCheck);
        $stmtCheck->bindParam(':id_usuario', $id_usuario);
        $stmtCheck->execute();
        $result = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        
        if ($result['total'] == 0) {
            throw new Exception("El instructor seleccionado no es válido o no existe");
        }

        $sql = "UPDATE " . $this->table . " 
                SET id_lider_grupo = :id_lider_grupo 
                WHERE id_ficha = :id_ficha";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_lider_grupo', $id_usuario);
        $stmt->bindParam(':id_ficha', $id_ficha);
        $stmt->execute();
    }

    // Función para buscar fichas por número
    public function buscarPorNumero($numero_ficha) {
        $sql = "SELECT f.*, 
                       u.nombre_completo as nombre_lider
                FROM fichas f
                LEFT JOIN usuarios u ON f.id_lider_grupo = u.id_usuario
                WHERE f.numero_ficha LIKE :numero_ficha AND f.estado = 1
                ORDER BY f.numero_ficha";
        $stmt = $this->conn->prepare($sql);
        $numero = "%{$numero_ficha}%";
        $stmt->bindParam(':numero_ficha', $numero);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Función para activar/desactivar ficha
    public function cambiarEstado($id_ficha, $estado) {
        if ($estado != 1 && $estado != 0) {
            throw new Exception("El estado debe ser 1 (activo) o 0 (inactivo)");
        }
        
        $sql = "UPDATE " . $this->table . " SET estado = :estado WHERE id_ficha = :id_ficha";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':id_ficha', $id_ficha);
        $stmt->execute();
    }

    // Función para listar solo fichas activas
    public function listarActivas() {
        $sql = "SELECT f.*, 
                       u.nombre_completo as nombre_lider
                FROM fichas f
                LEFT JOIN usuarios u ON f.id_lider_grupo = u.id_usuario
                WHERE f.estado = 1
                ORDER BY f.numero_ficha DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>