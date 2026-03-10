<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id_usuario'];
$password = $data['password'];

$hash = password_hash($password, PASSWORD_BCRYPT);

$conn->prepare("UPDATE usuarios
                SET password_hash = :hash
                WHERE id_usuario = :id")
     ->execute([
         ":hash" => $hash,
         ":id" => $id
     ]);

$_SESSION['usuario_id'] = $id;

echo json_encode(["status" => "password_changed"]);