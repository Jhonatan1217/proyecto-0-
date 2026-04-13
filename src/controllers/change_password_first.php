<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data || !isset($data['id_usuario']) || !isset($data['password'])) {
    echo json_encode(["status" => "error"]);
    exit;
}

$id = (int) $data['id_usuario'];
$password = $data['password'];

$sessionUserId = (int) ($_SESSION['first_login_password_change_user_id'] ?? 0);
if ($sessionUserId !== $id || $id <= 0) {
    http_response_code(403);
    echo json_encode([
        "status" => "error",
        "message" => "Debes verificar el código del correo en este mismo navegador antes de establecer la contraseña.",
    ]);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(["status" => "error"]);
    exit;
}

$hash = password_hash($password, PASSWORD_BCRYPT);

$conn->prepare("UPDATE usuarios SET password_hash = :hash, estado = 1 WHERE id_usuario = :id")
     ->execute([
         ":hash" => $hash,
         ":id" => $id
     ]);

unset($_SESSION['first_login_password_change_user_id']);

// Cargar usuario y establecer sesión completa (como en login)
$stmt = $conn->prepare("SELECT id_usuario, nombre_completo, correo_electronico, cargo, COALESCE(es_sistema, 0) AS es_sistema FROM usuarios WHERE id_usuario = :id LIMIT 1");
$stmt->execute([":id" => $id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuario) {
    $_SESSION['usuario_id'] = $usuario['id_usuario'];
    $_SESSION['usuario_nombre'] = $usuario['nombre_completo'];
    $_SESSION['usuario_correo'] = $usuario['correo_electronico'];
    $_SESSION['usuario_cargo'] = $usuario['cargo'] ?? '';
    $_SESSION['usuario_es_sistema'] = (int)($usuario['es_sistema'] ?? 0);
}

echo json_encode(["status" => "password_changed"]);
