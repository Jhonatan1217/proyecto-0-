<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

$correo = $_POST['correo'] ?? '';
$password = $_POST['password'] ?? '';

$sql = "SELECT * FROM usuarios WHERE correo_electronico = :correo LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->execute([":correo" => $correo]);

if ($stmt->rowCount() === 0) {
    echo json_encode(["status" => "error"]);
    exit;
}

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!password_verify($password, $usuario['password_hash'])) {
    echo json_encode(["status" => "error"]);
    exit;
}

/* SI ESTA INACTIVO */
if ($usuario['estado'] == 0) {

    $token = random_int(100000, 999999);
    $expira = date("Y-m-d H:i:s", strtotime("+10 minutes"));

    $insert = $conn->prepare("INSERT INTO tokens_correo
        (id_usuario, token, tipo, fecha_expiracion)
        VALUES (:id, :token, 'VERIFICACION', :expira)");

    $insert->execute([
        ":id" => $usuario['id_usuario'],
        ":token" => $token,
        ":expira" => $expira
    ]);

    echo json_encode([
        "status" => "require_verification",
        "id_usuario" => $usuario['id_usuario'],
        "token_debug" => $token
    ]);
    exit;
}

/* LOGIN NORMAL */

$_SESSION['usuario_id'] = $usuario['id_usuario'];
$_SESSION['usuario_nombre'] = $usuario['nombre_completo'];
$_SESSION['usuario_correo'] = $usuario['correo_electronico'];
$_SESSION['usuario_cargo'] = $usuario['cargo'];

echo json_encode(["status" => "success"]);