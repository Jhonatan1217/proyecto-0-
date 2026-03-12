<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error"]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$id_usuario = (int) ($input['id_usuario'] ?? 0);

if ($id_usuario <= 0) {
    echo json_encode(["status" => "error"]);
    exit;
}

try {
    $user = $conn->prepare("SELECT id_usuario, nombre_completo, correo_electronico FROM usuarios WHERE id_usuario = ? AND estado = 0 LIMIT 1");
    $user->execute([$id_usuario]);
    $usuario = $user->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        echo json_encode(["status" => "error"]);
        exit;
    }

    $token = random_int(100000, 999999);
    $expira = date("Y-m-d H:i:s", strtotime("+10 minutes"));

    $conn->prepare("INSERT INTO tokens_correo (id_usuario, token, tipo, fecha_expiracion) VALUES (?, ?, 'VERIFICACION', ?)")
        ->execute([$id_usuario, $token, $expira]);

    $mailConfig = require __DIR__ . '/../../config/mail.php';
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = $mailConfig['host'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $mailConfig['username'];
    $mail->Password   = $mailConfig['password'];
    $mail->SMTPSecure = $mailConfig['encryption'];
    $mail->Port       = $mailConfig['port'];
    $mail->CharSet = 'UTF-8';
    $mail->setFrom($mailConfig['from_email'], $mailConfig['from_name']);
    $mail->addAddress($usuario['correo_electronico'], $usuario['nombre_completo']);
    $mail->isHTML(true);
    $mail->Subject = 'Nuevo código de verificación';
    $mail->Body = "Hola {$usuario['nombre_completo']}, tu nuevo código es: <strong>$token</strong>. Expira en 10 minutos.";
    $mail->AltBody = "Tu nuevo código: $token. Expira en 10 minutos.";
    $mail->send();

    echo json_encode([
        "status" => "resent",
        "token_debug" => $token
    ]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error"]);
}
