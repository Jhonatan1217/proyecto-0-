<?php
require_once __DIR__ . '/../../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "invalid"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data || !isset($data['id_usuario']) || !isset($data['token'])) {
    echo json_encode(["status" => "invalid"]);
    exit;
}

$id = (int) $data['id_usuario'];
$token = $data['token'];

$sql = "SELECT * FROM tokens_correo
        WHERE id_usuario = :id
        AND token = :token
        AND tipo = 'VERIFICACION'
        AND usado = 0
        AND fecha_expiracion > NOW()";

$stmt = $conn->prepare($sql);
$stmt->execute([
    ":id" => $id,
    ":token" => $token
]);

if ($stmt->rowCount() === 0) {
    echo json_encode(["status" => "invalid"]);
    exit;
}

/*
 * No activar la cuenta aquí: si estado pasara a 1 antes de cambiar la contraseña,
 * el usuario podría entrar con la clave por defecto (p. ej. documento) sin completar el paso 2.
 * La activación (estado = 1) ocurre solo en change_password_first.php tras guardar la nueva clave.
 */
session_regenerate_id(true);
$_SESSION['first_login_password_change_user_id'] = $id;

$conn->prepare("UPDATE tokens_correo SET usado = 1
                WHERE id_usuario = :id AND token = :token")
     ->execute([
         ":id" => $id,
         ":token" => $token
     ]);

echo json_encode(["status" => "verified"]);
