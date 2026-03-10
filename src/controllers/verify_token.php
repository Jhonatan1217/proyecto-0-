<?php
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id_usuario'];
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

$conn->prepare("UPDATE usuarios SET estado = 1 WHERE id_usuario = :id")
     ->execute([":id" => $id]);

$conn->prepare("UPDATE tokens_correo SET usado = 1
                WHERE id_usuario = :id AND token = :token")
     ->execute([
         ":id" => $id,
         ":token" => $token
     ]);

echo json_encode(["status" => "verified"]);