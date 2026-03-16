<?php
class TokenRecuperacion {
    private $conn;
    private $table = 'tokens_correo';
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /*
     Genera un token único para recuperación
     */
    public function generarToken($id_usuario) {
        // Primero, invalidar tokens anteriores no usados
        $sql = "UPDATE {$this->table} 
                SET usado = 1 
                WHERE id_usuario = :id_usuario AND tipo = 'RECUPERACION' AND usado = 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":id_usuario", $id_usuario);
        $stmt->execute();
        
        // Generar token seguro
        $token = bin2hex(random_bytes(32)); // 64 caracteres hexadecimales
        
        // Fecha de expiración (30 minutos)
        $fecha_expiracion = date('Y-m-d H:i:s', strtotime('+30 minutes'));
        
        // Insertar nuevo token
        $sql = "INSERT INTO {$this->table} 
                (id_usuario, token, tipo, fecha_expiracion) 
                VALUES (:id_usuario, :token, 'RECUPERACION', :fecha_expiracion)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":id_usuario", $id_usuario);
        $stmt->bindParam(":token", $token);
        $stmt->bindParam(":fecha_expiracion", $fecha_expiracion);
        
        if ($stmt->execute()) {
            return $token;
        }
        
        return false;
    }
    
    /*
    Verifica si un token es válido
    */
    public function verificarToken($token) {
        $sql = "SELECT t.*, u.correo_electronico, u.nombre_completo, u.id_usuario, u.estado 
                FROM {$this->table} t
                JOIN usuarios u ON t.id_usuario = u.id_usuario
                WHERE t.token = :token 
                AND t.tipo = 'RECUPERACION' 
                AND t.usado = 0 
                AND t.fecha_expiracion > NOW()";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":token", $token);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /*
    Marca un token como usado
    */
    public function marcarComoUsado($token) {
        $sql = "UPDATE {$this->table} 
                SET usado = 1 
                WHERE token = :token";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":token", $token);
        return $stmt->execute();
    }
}