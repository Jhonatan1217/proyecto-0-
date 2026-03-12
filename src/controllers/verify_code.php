<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Método no permitido"]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$id_usuario = (int) ($input['id_usuario'] ?? 0);
$codigo = trim((string) ($input['codigo'] ?? ''));

if ($id_usuario <= 0 || strlen($codigo) !== 6 || !ctype_digit($codigo)) {
    echo json_encode(["status" => "error", "message" => "Código inválido"]);
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT t.id_usuario 
        FROM tokens_correo t 
        WHERE t.id_usuario = :id 
          AND t.token = :token 
          AND t.tipo = 'VERIFICACION' 
          AND t.fecha_expiracion > NOW() 
        LIMIT 1
    ");
    $stmt->execute([":id" => $id_usuario, ":token" => $codigo]);

    if ($stmt->rowCount() === 0) {
        echo json_encode(["status" => "error", "message" => "Código inválido o expirado"]);
        exit;
    }

    $conn->prepare("UPDATE usuarios SET estado = 1 WHERE id_usuario = ?")->execute([$id_usuario]);

    $_SESSION['usuario_id'] = $id_usuario;
    $user = $conn->prepare("SELECT nombre_completo, correo_electronico, cargo FROM usuarios WHERE id_usuario = ?");
    $user->execute([$id_usuario]);
    $u = $user->fetch(PDO::FETCH_ASSOC);
    if ($u) {
        $_SESSION['usuario_nombre'] = $u['nombre_completo'];
        $_SESSION['usuario_correo'] = $u['correo_electronico'];
        $_SESSION['usuario_cargo'] = $u['cargo'] ?? '';
    }

    echo json_encode(["status" => "success"]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Error al verificar"]);
}
