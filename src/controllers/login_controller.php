<?php
session_start();
session_regenerate_id(true);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Si ya está logueado
if (isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php?page=register_tables");
    exit;
}

// Verificar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../index.php?page=login");
    exit;
}

header('Content-Type: application/json');

$correo = trim($_POST['correo'] ?? '');
$password = trim($_POST['password'] ?? '');

if (empty($correo) || empty($password)) {
    echo json_encode(["status" => "error"]);
    exit;
}

$sql = "SELECT id_usuario, nombre_completo, correo_electronico, password_hash, estado, cargo
        FROM usuarios
        WHERE correo_electronico = :correo
        LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bindParam(":correo", $correo);
$stmt->execute();

if ($stmt->rowCount() === 0) {
    echo json_encode(["status" => "error"]);
    exit;
}

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (
    !password_verify($password, $usuario['password_hash']) &&
    $password !== $usuario['password_hash']
) {
    echo json_encode(["status" => "error"]);
    exit;
}

/* ================= SI ESTA INACTIVO ================= */
if ($usuario['estado'] == 0) {

    $token = random_int(100000, 999999);
    $expira = date("Y-m-d H:i:s", strtotime("+10 minutes"));

    // Guardar token
    $insert = $conn->prepare("INSERT INTO tokens_correo
        (id_usuario, token, tipo, fecha_expiracion)
        VALUES (:id, :token, 'VERIFICACION', :expira)");

    $insert->execute([
        ":id" => $usuario['id_usuario'],
        ":token" => $token,
        ":expira" => $expira
    ]);

    // ================= ENVIAR CORREO =================
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
        <head>
        <meta charset='UTF-8'>
        </head>
        <body style='margin:0; padding:0; background-color:#f4f6f9; font-family:Arial, sans-serif;'>

        <table width='100%' cellpadding='0' cellspacing='0' style='background-color:#f4f6f9; padding:40px 0;'>
        <tr>
        <td align='center'>

        <table width='420' cellpadding='0' cellspacing='0' style='background:#ffffff; border-radius:12px; padding:40px; box-shadow:0 10px 25px rgba(0,0,0,0.05);'>

        <tr>
        <td align='center'>

        <h2 style='margin:0; color:#111; font-size:22px;'>
        Verificación de cuenta
        </h2>

        <p style='color:#555; font-size:14px; margin-top:15px;'>
        Hola {$usuario['nombre_completo']},
        </p>

        <p style='color:#666; font-size:14px; margin-top:5px;'>
        Usa el siguiente código para activar tu cuenta:
        </p>

        <div style='margin:30px 0;'>
        <span style='
        display:inline-block;
        background:#111;
        color:#ffffff;
        font-size:28px;
        letter-spacing:8px;
        padding:15px 30px;
        border-radius:10px;
        font-weight:bold;
        '>
        $token
        </span>
        </div>

        <p style='color:#888; font-size:12px;'>
        Este código expirará en 10 minutos.
        </p>

        <hr style='margin:30px 0; border:none; border-top:1px solid #eee;'>

        <p style='color:#999; font-size:11px;'>
        Si no solicitaste este código, puedes ignorar este mensaje.
        </p>

        </td>
        </tr>

        </table>

        <p style='color:#aaa; font-size:11px; margin-top:20px;'>
        © " . date('Y') . " Sistema de Recuperación
        </p>

        </td>
        </tr>
        </table>

        </body>
        </html>
        ";

        // Versión texto plano (buena práctica)
        $mail->AltBody = "Hola {$usuario['nombre_completo']}, tu código de verificación es: $token. Expira en 10 minutos.";

        $mail->send();

        echo json_encode([
            "status" => "require_verification",
            "id_usuario" => $usuario['id_usuario']
        ]);
        exit;

    } catch (Exception $e) {

        echo json_encode([
            "status" => "error_mail",
            "message" => $mail->ErrorInfo
        ]);
        exit;
    }
}

/* ================= LOGIN NORMAL ================= */

$_SESSION['usuario_id'] = $usuario['id_usuario'];
$_SESSION['usuario_nombre'] = $usuario['nombre_completo'];
$_SESSION['usuario_correo'] = $usuario['correo_electronico'];
$_SESSION['usuario_cargo'] = $usuario['cargo'] ?? '';

echo json_encode(["status" => "success"]);
