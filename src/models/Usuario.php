<?php

class Usuario
{
    private $conn;
    private $table = "usuarios";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /* ============================================
       CREAR USUARIO
    ============================================ */
    public function crear($nombre, $correo, $password)
    {
        $sql = "INSERT INTO {$this->table}
                (nombre_completo, correo_electronico, password_hash, estado)
                VALUES (:nombre, :correo, :password, 1)";

        $stmt = $this->conn->prepare($sql);

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":correo", $correo);
        $stmt->bindParam(":password", $passwordHash);

        return $stmt->execute();
    }

    /* ============================================
       LISTAR TODOS
    ============================================ */
    public function listar()
    {
        $sql = "SELECT id_usuario, nombre_completo, correo_electronico, estado
                FROM {$this->table}
                ORDER BY id_usuario DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ============================================
       OBTENER UNO
    ============================================ */
    public function obtenerPorId($id)
    {
        $sql = "SELECT id_usuario, nombre_completo, correo_electronico, estado
                FROM {$this->table}
                WHERE id_usuario = :id
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Buscar usuario por correo 
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

    /* ============================================
       ACTUALIZAR
    ============================================ */
    public function actualizar($id, $nombre, $correo)
    {
        $sql = "UPDATE {$this->table}
                SET nombre_completo = :nombre,
                    correo_electronico = :correo
                WHERE id_usuario = :id";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":correo", $correo);

        return $stmt->execute();
    }

    // Actualizar contraseña 
    public function actualizarPassword($id_usuario, $password_hash) {
        $sql = "UPDATE {$this->table} 
                SET password_hash = :password 
                WHERE id_usuario = :id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":password", $password_hash);
        $stmt->bindParam(":id", $id_usuario);
        
        return $stmt->execute();
    }

    /* ============================================
       DESHABILITAR (SOFT DELETE)
    ============================================ */
    public function deshabilitar($id)
    {
        $sql = "UPDATE {$this->table}
                SET estado = 0
                WHERE id_usuario = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }

    /* ============================================
       HABILITAR
    ============================================ */
    public function habilitar($id)
    {
        $sql = "UPDATE {$this->table}
                SET estado = 1
                WHERE id_usuario = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }

    /* ============================================
       VALIDAR LOGIN
    ============================================ */
    public function login($correo, $password)
    {
        $sql = "SELECT id_usuario, nombre_completo, correo_electronico, password_hash, estado
                FROM {$this->table}
                WHERE correo_electronico = :correo
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":correo", $correo);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            return false;
        }

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!password_verify($password, $usuario['password_hash'])) {
            return false;
        }

        if ($usuario['estado'] != 1) {
            return false;
        }

        return $usuario;
    }
}