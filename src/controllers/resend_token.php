<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data || !isset($data['id_usuario'])) {
    echo json_encode(["status" => "error"]);
    exit;
}

$id = (int) $data['id_usuario'];

$stmt = $conn->prepare("SELECT id_usuario, nombre_completo, correo_electronico FROM usuarios WHERE id_usuario = :id AND estado = 0 LIMIT 1");
$stmt->execute([":id" => $id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    echo json_encode(["status" => "error"]);
    exit;
}

$token = random_int(100000, 999999);
$expira = date("Y-m-d H:i:s", strtotime("+10 minutes"));

$insert = $conn->prepare("INSERT INTO tokens_correo (id_usuario, token, tipo, fecha_expiracion) VALUES (:id, :token, 'VERIFICACION', :expira)");
$insert->execute([
    ":id" => $usuario['id_usuario'],
    ":token" => $token,
    ":expira" => $expira
]);

$mailConfig = require __DIR__ . '/../../config/mail.php';
$mail = new PHPMailer(true);

try {
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
    $mail->Subject = 'Verificación de cuenta - Código de seguridad';

    $mail->Body = "
    <!DOCTYPE html>
    <html>
    <head><meta charset='UTF-8'></head>
    <body style='margin:0; padding:0; background-color:#f4f6f9; font-family:Arial, sans-serif;'>
    <table width='100%' cellpadding='0' cellspacing='0' style='background-color:#f4f6f9; padding:40px 0;'>
    <tr><td align='center'>
    <table width='420' cellpadding='0' cellspacing='0' style='background:#ffffff; border-radius:12px; padding:40px; box-shadow:0 10px 25px rgba(0,0,0,0.05);'>
    <tr><td align='center'>
    <h2 style='margin:0; color:#111; font-size:22px;'>Verificación de cuenta</h2>
    <p style='color:#555; font-size:14px; margin-top:15px;'>Hola {$usuario['nombre_completo']},</p>
    <p style='color:#666; font-size:14px; margin-top:5px;'>Usa el siguiente código para activar tu cuenta:</p>
    <div style='margin:30px 0;'>
    <span style='display:inline-block; background:#111; color:#fff; font-size:28px; letter-spacing:8px; padding:15px 30px; border-radius:10px; font-weight:bold;'>$token</span>
    </div>
    <p style='color:#888; font-size:12px;'>Este código expirará en 10 minutos.</p>
    <hr style='margin:30px 0; border:none; border-top:1px solid #eee;'>
    <p style='color:#999; font-size:11px;'>Si no solicitaste este código, puedes ignorar este mensaje.</p>
    </td></tr></table>
    <p style='color:#aaa; font-size:11px; margin-top:20px;'>© " . date('Y') . " Sistema de Recuperación</p>
    </td></tr></table>
    </body></html>";

    $mail->AltBody = "Hola {$usuario['nombre_completo']}, tu código de verificación es: $token. Expira en 10 minutos.";
    $mail->send();

    $out = ["status" => "resent"];
    if (isset($mailConfig['debug_token']) && $mailConfig['debug_token']) {
        $out['token_debug'] = (string) $token;
    }
    echo json_encode($out);

} catch (Exception $e) {
    echo json_encode(["status" => "error_mail", "message" => $mail->ErrorInfo]);
}
