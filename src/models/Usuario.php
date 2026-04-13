<?php
// Clase Usuario para manejar operaciones CRUD sobre la tabla 'usuarios' y 'usuarios_roles_funcionales'
class Usuario {
    private $conn;
    private $table = "usuarios";
    private $table_roles = "usuarios_roles_funcionales";

    /**
     * Usuarios con es_sistema = 1 son cuentas administrativas internas:
     * no deben aparecer en listados ni ser consultables vía API de gestión.
     */
    private function sqlExcluirUsuarioSistema(string $alias = 'u'): string {
        return " AND (COALESCE({$alias}.es_sistema, 0) = 0) ";
    }

    public function __construct($db) {
        $this->conn = $db;
    }

    // ============================================================
    // CRUD BÁSICO DE USUARIOS
    // ============================================================

    /**
     * Lista todos los usuarios (con información de su área)
     * @return array Lista de usuarios.
     */
    public function listar() {
        $sql = "SELECT u.*, a.nombre_area 
                FROM " . $this->table . " u
                LEFT JOIN area a ON u.id_area = a.id_area
                WHERE 1=1 " . $this->sqlExcluirUsuarioSistema('u') . "
                ORDER BY u.id_usuario DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene un usuario por su ID.
     * @param int $id ID del usuario.
     * @param bool $incluirSistema Si true, incluye usuarios con es_sistema = 1 (solo uso interno, ej. cambio de contraseña).
     * @return array|false Datos del usuario o false si no existe.
     */
    public function obtenerPorId($id, $incluirSistema = false) {
        $sql = "SELECT u.*, a.nombre_area 
                FROM " . $this->table . " u
                LEFT JOIN area a ON u.id_area = a.id_area
                WHERE u.id_usuario = :id";
        if (!$incluirSistema) {
            $sql .= $this->sqlExcluirUsuarioSistema('u');
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Busca un usuario por su correo electrónico (para recuperación de contraseña).
     * @param string $correo Correo electrónico.
     * @return array|false Datos del usuario o false si no existe.
     */
    public function obtenerPorCorreo($correo) {
        $sql = "SELECT id_usuario, nombre_completo, correo_electronico, estado 
                FROM {$this->table} 
                WHERE correo_electronico = :correo 
                LIMIT 1";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":correo", $correo);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crea un nuevo usuario.
     * @param array $data Datos del usuario (nombre_completo, tipo_documento, numero_documento, correo_electronico, cargo, password_hash, [id_area], [tipo_instructor], [tipo_contrato]).
     * @return bool|string True si se crea correctamente, o un mensaje de error (ej. duplicado).
     */
    public function crear($data) {
        // Verificar si el documento o correo ya existen
        if ($this->existeDocumento($data['numero_documento'])) {
            return "El número de documento ya está registrado.";
        }
        if ($this->existeCorreo($data['correo_electronico'])) {
            return "El correo electrónico ya está registrado.";
        }

        $sql = "INSERT INTO " . $this->table . " 
                (nombre_completo, tipo_documento, numero_documento, correo_electronico, cargo, id_area, tipo_instructor, tipo_contrato, password_hash, estado, es_sistema)
                VALUES 
                (:nombre_completo, :tipo_documento, :numero_documento, :correo_electronico, :cargo, :id_area, :tipo_instructor, :tipo_contrato, :password_hash, :estado, :es_sistema)";
        
        $stmt = $this->conn->prepare($sql);

        // Bind de parámetros
        $stmt->bindParam(':nombre_completo', $data['nombre_completo']);
        $stmt->bindParam(':tipo_documento', $data['tipo_documento']);
        $stmt->bindParam(':numero_documento', $data['numero_documento']);
        $stmt->bindParam(':correo_electronico', $data['correo_electronico']);
        $stmt->bindParam(':cargo', $data['cargo']);
        $stmt->bindParam(':id_area', $data['id_area'], PDO::PARAM_INT); // Puede ser NULL
        $stmt->bindParam(':tipo_instructor', $data['tipo_instructor']); // Puede ser NULL
        $stmt->bindParam(':tipo_contrato', $data['tipo_contrato']);
        $stmt->bindParam(':password_hash', $data['password_hash']);
        $estado = isset($data['estado']) ? (int) $data['estado'] : 0;
        $stmt->bindParam(':estado', $estado, PDO::PARAM_INT);
        $es_sistema = $data['es_sistema'] ?? 0;
        $stmt->bindParam(':es_sistema', $es_sistema, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return (int) $this->conn->lastInsertId();
        }
        return "Error al crear el usuario.";
    }

    /**
     * Actualiza un usuario existente.
     * @param int $id ID del usuario a actualizar.
     * @param array $data Datos a actualizar.
     * @return bool|string True si se actualiza correctamente, o un mensaje de error.
     */
    public function actualizar($id, $data) {
        // Verificar si el documento o correo ya existen en OTRO usuario
        if (isset($data['numero_documento']) && $this->existeDocumento($data['numero_documento'], $id)) {
            return "El número de documento ya está registrado por otro usuario.";
        }
        if (isset($data['correo_electronico']) && $this->existeCorreo($data['correo_electronico'], $id)) {
            return "El correo electrónico ya está registrado por otro usuario.";
        }

        $sql = "UPDATE " . $this->table . " SET 
                nombre_completo = :nombre_completo,
                tipo_documento = :tipo_documento,
                numero_documento = :numero_documento,
                correo_electronico = :correo_electronico,
                cargo = :cargo,
                id_area = :id_area,
                tipo_instructor = :tipo_instructor,
                tipo_contrato = :tipo_contrato,
                estado = :estado
                WHERE id_usuario = :id";
        
        // Si se proporciona una nueva contraseña, actualizarla también
        if (!empty($data['password_hash'])) {
            $sql = "UPDATE " . $this->table . " SET 
                    nombre_completo = :nombre_completo,
                    tipo_documento = :tipo_documento,
                    numero_documento = :numero_documento,
                    correo_electronico = :correo_electronico,
                    cargo = :cargo,
                    id_area = :id_area,
                    tipo_instructor = :tipo_instructor,
                    tipo_contrato = :tipo_contrato,
                    password_hash = :password_hash,
                    estado = :estado
                    WHERE id_usuario = :id";
        }

        $stmt = $this->conn->prepare($sql);

        // Bind de parámetros
        $stmt->bindParam(':nombre_completo', $data['nombre_completo']);
        $stmt->bindParam(':tipo_documento', $data['tipo_documento']);
        $stmt->bindParam(':numero_documento', $data['numero_documento']);
        $stmt->bindParam(':correo_electronico', $data['correo_electronico']);
        $stmt->bindParam(':cargo', $data['cargo']);
        if ($data['id_area'] === null || $data['id_area'] === '') {
            $stmt->bindValue(':id_area', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':id_area', (int) $data['id_area'], PDO::PARAM_INT);
        }
        $stmt->bindParam(':tipo_instructor', $data['tipo_instructor']);
        $stmt->bindParam(':tipo_contrato', $data['tipo_contrato']);
        $stmt->bindParam(':estado', $data['estado'], PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        if (!empty($data['password_hash'])) {
            $stmt->bindParam(':password_hash', $data['password_hash']);
        }

        if ($stmt->execute()) {
            return true;
        }
        return "Error al actualizar el usuario.";
    }

    /**
     * Cambia el estado de un usuario (activo/inactivo).
     * @param int $id ID del usuario.
     * @param int $nuevoEstado 1 (activo) o 0 (inactivo).
     * @return bool|string True si se cambia correctamente, o un mensaje de error.
     */
    public function cambiarEstado($id, $nuevoEstado) {
        if ($nuevoEstado != 1 && $nuevoEstado != 0) {
            return "El estado debe ser 1 (activo) o 0 (inactivo).";
        }

        $sql = "UPDATE " . $this->table . " SET estado = :estado WHERE id_usuario = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':estado', $nuevoEstado, PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return true;
        }
        return "Error al cambiar el estado del usuario.";
    }

    // ============================================================
    // FUNCIONES PARA ROLES FUNCIONALES
    // ============================================================

    /**
     * Asigna un rol funcional a un usuario.
     * @param int $id_usuario ID del usuario.
     * @param int $id_rol ID del rol.
     * @param int $asignado_por ID del usuario que asigna el rol.
     * @return bool True si se asigna correctamente.
     */
    public function asignarRolFuncional($id_usuario, $id_rol, $asignado_por) {
        // Verificar si ya tiene el rol
        if ($this->tieneRolFuncional($id_usuario, $id_rol)) {
            return false; // O podrías lanzar una excepción
        }

        $sql = "INSERT INTO " . $this->table_roles . " (id_usuario, id_rol, asignado_por, fecha_asignacion) VALUES (:id_usuario, :id_rol, :asignado_por, NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->bindParam(':id_rol', $id_rol, PDO::PARAM_INT);
        $stmt->bindParam(':asignado_por', $asignado_por, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Elimina un rol funcional de un usuario.
     * @param int $id_usuario ID del usuario.
     * @param int $id_rol ID del rol.
     * @return bool True si se elimina correctamente.
     */
    public function quitarRolFuncional($id_usuario, $id_rol) {
        $sql = "DELETE FROM " . $this->table_roles . " WHERE id_usuario = :id_usuario AND id_rol = :id_rol";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->bindParam(':id_rol', $id_rol, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Actualiza la contraseña de un usuario (para recuperación de contraseña).
     * @param int $id_usuario ID del usuario.
     * @param string $password_hash Hash de la nueva contraseña.
     * @return bool True si se actualiza correctamente.
     */
    public function actualizarPassword($id_usuario, $password_hash) {
        $sql = "UPDATE {$this->table} 
                SET password_hash = :password 
                WHERE id_usuario = :id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":password", $password_hash);
        $stmt->bindParam(":id", $id_usuario);
        
        return $stmt->execute();
    }

    /**
     * Deshabilita un usuario (soft delete - establece estado = 0).
     * @param int $id ID del usuario.
     * @return bool True si se deshabilita correctamente.
     */
    public function deshabilitar($id) {
        $sql = "UPDATE {$this->table}
                SET estado = 0
                WHERE id_usuario = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Lista los roles funcionales de un usuario específico.
     * @param int $id_usuario ID del usuario.
     * @return array Lista de roles con detalles de quién y cuándo los asignó.
     */
    public function listarRolesFuncionalesPorUsuario($id_usuario) {
        $sql = "SELECT urf.*, rf.nombre_rol, 
                       u_asignador.nombre_completo as nombre_asignador
                FROM " . $this->table_roles . " urf
                INNER JOIN roles_funcionales rf ON urf.id_rol = rf.id_rol
                LEFT JOIN usuarios u_asignador ON urf.asignado_por = u_asignador.id_usuario
                WHERE urf.id_usuario = :id_usuario
                ORDER BY urf.fecha_asignacion DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Verifica si un usuario ya tiene un rol funcional específico.
     * @param int $id_usuario ID del usuario.
     * @param int $id_rol ID del rol.
     * @return bool True si ya tiene el rol.
     */
    private function tieneRolFuncional($id_usuario, $id_rol) {
        $sql = "SELECT COUNT(*) FROM " . $this->table_roles . " WHERE id_usuario = :id_usuario AND id_rol = :id_rol";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->bindParam(':id_rol', $id_rol, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    // ============================================================
    // FUNCIONES DE LISTADO POR ROL (PRINCIPAL)
    // ============================================================

    /**
     * Lista usuarios con filtros opcionales: cargo, rol funcional (solo aplica si cargo es Instructor), búsqueda por texto.
     * @param string $cargo Opcional. Ej: 'Instructor', 'Coordinador'.
     * @param int|null $id_rol_funcional Opcional. Solo filtra por rol cuando se combina con cargo Instructor.
     * @param string $buscar Opcional. Busca en nombre_completo, numero_documento, correo_electronico.
     * @return array Lista de usuarios.
     */
    public function listarConFiltros($cargo = '', $id_rol_funcional = null, $buscar = '') {
        $cargo = trim((string) $cargo);
        $buscar = trim((string) $buscar);
        $id_rol = $id_rol_funcional !== null && $id_rol_funcional !== '' ? intval($id_rol_funcional) : null;
        if ($id_rol <= 0) $id_rol = null;

        $joinRol = $id_rol !== null ? " INNER JOIN " . $this->table_roles . " urf ON u.id_usuario = urf.id_usuario AND urf.id_rol = :id_rol " : "";
        $sql = "SELECT DISTINCT u.*, a.nombre_area 
                FROM " . $this->table . " u
                LEFT JOIN area a ON u.id_area = a.id_area
                " . $joinRol . "
                WHERE 1=1 " . $this->sqlExcluirUsuarioSistema('u');
        $params = [];

        if ($cargo !== '') {
            $sql .= " AND LOWER(TRIM(u.cargo)) = LOWER(:cargo) ";
            $params[':cargo'] = $cargo;
        }
        if ($buscar !== '') {
            $sql .= " AND (u.nombre_completo LIKE :buscar OR CAST(u.numero_documento AS CHAR) LIKE :buscar_num OR u.correo_electronico LIKE :buscar_correo) ";
            $term = '%' . $buscar . '%';
            $params[':buscar'] = $term;
            $params[':buscar_num'] = $term;
            $params[':buscar_correo'] = $term;
        }

        $sql .= " ORDER BY u.nombre_completo ASC";
        $stmt = $this->conn->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        if ($id_rol !== null) {
            $stmt->bindValue(':id_rol', $id_rol, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lista todos los usuarios filtrados por su cargo principal (COORDINADOR o INSTRUCTOR).
     * @param string $cargo 'COORDINADOR' o 'INSTRUCTOR'.
     * @return array Lista de usuarios con ese cargo.
     */
    public function listarPorCargo($cargo) {
        $sql = "SELECT u.*, a.nombre_area 
                FROM " . $this->table . " u
                LEFT JOIN area a ON u.id_area = a.id_area
                WHERE u.cargo = :cargo " . $this->sqlExcluirUsuarioSistema('u') . "
                ORDER BY u.nombre_completo ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':cargo', $cargo);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lista todos los usuarios que tienen un rol funcional específico.
     * @param int $id_rol ID del rol funcional.
     * @return array Lista de usuarios con ese rol funcional.
     */
    public function listarPorRolFuncional($id_rol) {
        $sql = "SELECT u.*, a.nombre_area, urf.fecha_asignacion, u_asignador.nombre_completo as asignado_por_nombre
                FROM " . $this->table . " u
                INNER JOIN " . $this->table_roles . " urf ON u.id_usuario = urf.id_usuario
                LEFT JOIN area a ON u.id_area = a.id_area
                LEFT JOIN usuarios u_asignador ON urf.asignado_por = u_asignador.id_usuario
                WHERE urf.id_rol = :id_rol AND u.estado = 1 " . $this->sqlExcluirUsuarioSistema('u') . "
                ORDER BY u.nombre_completo ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_rol', $id_rol, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ============================================================
    // FUNCIONES AUXILIARES DE VALIDACIÓN
    // ============================================================

    /**
     * Verifica si un número de documento ya existe.
     * @param string $numero Número de documento.
     * @param int|null $excluir_id ID de usuario a excluir de la búsqueda (para actualizar).
     * @return bool True si ya existe.
     */
    private function existeDocumento($numero, $excluir_id = null) {
        $sql = "SELECT COUNT(*) FROM " . $this->table . " WHERE numero_documento = :numero";
        if ($excluir_id) {
            $sql .= " AND id_usuario != :excluir_id";
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':numero', $numero);
        if ($excluir_id) {
            $stmt->bindParam(':excluir_id', $excluir_id, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Verifica si un correo electrónico ya existe.
     * @param string $correo Correo electrónico.
     * @param int|null $excluir_id ID de usuario a excluir de la búsqueda (para actualizar).
     * @return bool True si ya existe.
     */
    private function existeCorreo($correo, $excluir_id = null) {
        $sql = "SELECT COUNT(*) FROM " . $this->table . " WHERE correo_electronico = :correo";
        if ($excluir_id) {
            $sql .= " AND id_usuario != :excluir_id";
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':correo', $correo);
        if ($excluir_id) {
            $stmt->bindParam(':excluir_id', $excluir_id, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Cambia la contraseña del usuario validando primero la contraseña actual.
     * @param int $id_usuario ID del usuario.
     * @param string $password_actual Contraseña actual (en texto plano).
     * @param string $password_nueva Nueva contraseña (en texto plano).
     * @return bool|string True si se actualiza correctamente, o mensaje de error.
     */
    public function cambiarContrasena($id_usuario, $password_actual, $password_nueva) {
        $usuario = $this->obtenerPorId($id_usuario, true);
        if (!$usuario) {
            return "Usuario no encontrado.";
        }
        $hash_actual = $usuario['password_hash'] ?? '';
        if (empty($hash_actual) || !password_verify($password_actual, $hash_actual)) {
            return "La contraseña actual no es correcta.";
        }
        $password_nueva = trim((string) $password_nueva);
        if (strlen($password_nueva) < 6) {
            return "La nueva contraseña debe tener al menos 6 caracteres.";
        }
        if ($password_actual === $password_nueva) {
            return "La nueva contraseña no puede ser igual a la actual.";
        }
        $nuevo_hash = password_hash($password_nueva, PASSWORD_DEFAULT);
        $sql = "UPDATE " . $this->table . " SET password_hash = :password_hash WHERE id_usuario = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':password_hash', $nuevo_hash);
        $stmt->bindParam(':id', $id_usuario, PDO::PARAM_INT);
        if ($stmt->execute()) {
            return true;
        }
        return "Error al actualizar la contraseña.";
    }

    /**
     * Lista todos los roles funcionales disponibles.
     * @return array Lista de roles.
     */
    public function listarRolesFuncionalesDisponibles() {
        $sql = "SELECT id_rol, nombre_rol FROM roles_funcionales ORDER BY nombre_rol";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>