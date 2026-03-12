<?php
session_start();

require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/TokenRecuperacion.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/mail.php';
require_once __DIR__ . '/../../vendor/autoload.php'; // Para PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$usuarioModel = new Usuario($conn);
$tokenModel = new TokenRecuperacion($conn);

$accion = $_GET['accion'] ?? '';

/* ================= BASE_URL AUTO ================= */
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        ? 'https://'
        : 'http://';

    $host = $_SERVER['HTTP_HOST'];

    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $project = preg_replace('#/src/.*$#', '', $scriptDir);

    define('BASE_URL', $protocol . $host . $project . '/');
}

function redirectTo(string $url) { 
    header("Location: $url"); 
    exit; 
}

function enmascararCorreo(string $correo): string {
    $partes = explode('@', $correo, 2);

    if (count($partes) !== 2) {
        return $correo;
    }

    $local = $partes[0];
    $dominio = $partes[1];

    $primerosDos = substr($local, 0, 2);
    $cantidadAsteriscos = max(strlen($local) - 2, 1);

    return $primerosDos . str_repeat('*', $cantidadAsteriscos) . '@' . $dominio;
}

switch ($accion) {
    
    /* ======================================
       SOLICITUD DE RECUPERACIÓN (ENVIAR CORREO)
    ====================================== */
    case 'solicitar':
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirectTo(BASE_URL . "src/views/restablecerContrasenia.php");
        }
        
        $correo = trim($_POST['correo'] ?? '');
        
        if (empty($correo)) {
            $_SESSION['error_recuperacion'] = "El correo es obligatorio";
            redirectTo(BASE_URL . "src/views/restablecerContrasenia.php");
        }
        
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error_recuperacion'] = "Ingresa un correo electrónico válido";
            redirectTo(BASE_URL . "src/views/restablecerContrasenia.php");
        }
        
        // Buscar usuario por correo (usa correo_electronico en la BD)
        $usuario = $usuarioModel->obtenerPorCorreo($correo);
        
        // Por seguridad, no revelamos si el correo existe o no
        if (!$usuario) {
            $_SESSION['success_recuperacion'] = "Si el correo está registrado, recibirás un enlace para restablecer tu contraseña.";
            redirectTo(BASE_URL . "src/views/restablecerContrasenia.php");
        }
        
        // Verificar que el usuario esté activo (estado = 1)
        if ($usuario['estado'] != 1) {
            $_SESSION['error_recuperacion'] = "Tu cuenta está inactiva. Contacta al administrador.";
            redirectTo(BASE_URL . "src/views/restablecerContrasenia.php");
        }
        
        // Generar token (el modelo usa tipo 'RECUPERACION')
        $token = $tokenModel->generarToken($usuario['id_usuario']);
        
        if (!$token) {
            error_log("Error al generar token para usuario: " . $usuario['id_usuario']);
            $_SESSION['error_recuperacion'] = "Error al procesar la solicitud. Por favor intenta más tarde.";
            redirectTo(BASE_URL . "src/views/restablecerContrasenia.php");
        }
        
        // Construir el enlace de recuperación - DIRECTO A cambiarContrasenia.php
        $resetLink = BASE_URL . "src/views/cambiarContrasenia.php?token=" . urlencode($token);
        
        // Enviar correo (usa correo_electronico)
        if (enviarCorreoRecuperacion($usuario['correo_electronico'], $usuario['nombre_completo'], $resetLink)) {
            $correoEnmascarado = enmascararCorreo($correo);
            $_SESSION['success_recuperacion'] = "Hemos enviado un enlace de restablecimiento a $correoEnmascarado. Revisa tu bandeja de entrada.";
        } else {
            error_log("Error al enviar correo a: " . $usuario['correo_electronico']);
            $_SESSION['error_recuperacion'] = "Error al enviar el correo. Por favor intenta más tarde.";
        }
        
        redirectTo(BASE_URL . "src/views/restablecerContrasenia.php");
        break;
    
    /* ======================================
       CAMBIAR CONTRASEÑA
    ====================================== */
    case 'cambiar':
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirectTo(BASE_URL . "src/views/restablecerContrasenia.php");
        }
        
        // El token viene del campo hidden en el formulario
        $token = $_POST['token'] ?? '';
        
        if (empty($token)) {
            $_SESSION['error_recuperacion'] = "Token no proporcionado";
            redirectTo(BASE_URL . "src/views/restablecerContrasenia.php");
        }
        
        // Verificar token (el modelo busca tipo 'RECUPERACION' y no usado)
        $tokenData = $tokenModel->verificarToken($token);
        
        if (!$tokenData) {
            $_SESSION['error_recuperacion'] = "El enlace no es válido o ha expirado";
            redirectTo(BASE_URL . "src/views/restablecerContrasenia.php");
        }
        
        // Verificar que el usuario esté activo
        if ($tokenData['estado'] != 1) {
            $_SESSION['error_recuperacion'] = "Tu cuenta está inactiva. Contacta al administrador.";
            redirectTo(BASE_URL . "src/views/restablecerContrasenia.php");
        }
        
        $password = $_POST['password'] ?? '';
        $confirmar = $_POST['confirmar_password'] ?? '';
        
        // Validaciones
        if (strlen($password) < 8) {
            $_SESSION['error_password'] = "La contraseña debe tener al menos 8 caracteres";
            redirectTo(BASE_URL . "src/views/cambiarContrasenia.php?token=" . urlencode($token));
        }
        
        if ($password !== $confirmar) {
            $_SESSION['error_password'] = "Las contraseñas no coinciden";
            redirectTo(BASE_URL . "src/views/cambiarContrasenia.php?token=" . urlencode($token));
        }
        
        // Hashear la nueva contraseña
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Actualizar contraseña usando el modelo (actualiza password_hash)
        $actualizado = $usuarioModel->actualizarPassword($tokenData['id_usuario'], $password_hash);
        
        if ($actualizado) {
            
            // Marcar token como usado
            $tokenModel->marcarComoUsado($token);
            
            // Enviar correo de confirmación
            enviarCorreoConfirmacion(
                $tokenData['correo_electronico'], 
                $tokenData['nombre_completo'] ?? 'Usuario'
            );
            
            // Limpiar sesión
            unset($_SESSION['reset_token'], $_SESSION['reset_usuario_id'], 
                  $_SESSION['reset_correo'], $_SESSION['reset_nombre']);
            
            $_SESSION['success_recuperacion'] = "Contraseña actualizada correctamente. Ya puedes iniciar sesión.";
            redirectTo(BASE_URL . "src/views/login.php");
            
        } else {
            error_log("Error al actualizar contraseña para usuario: " . $tokenData['id_usuario']);
            $_SESSION['error_password'] = "Error al actualizar la contraseña. Intenta nuevamente.";
            redirectTo(BASE_URL . "src/views/cambiarContrasenia.php?token=" . urlencode($token));
        }
        break;
    
    default:
        redirectTo(BASE_URL . "src/views/restablecerContrasenia.php");
        break;
}

/* ======================================
   FUNCIONES DE CORREO
====================================== */

function enviarCorreoRecuperacion($correo, $nombre, $resetLink) {
    $config = require __DIR__ . '/../../config/mail.php';
    
    $mail = new PHPMailer(true);
    
    try {
        // Configuración del servidor
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host       = $config['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $config['username'];
        $mail->Password   = $config['password'];
        $mail->SMTPSecure = $config['encryption'];
        $mail->Port       = $config['port'];
        
        // Remitente y destinatario
        $mail->setFrom($config['from_email'], $config['from_name']);
        $mail->addAddress($correo, $nombre);
        
        // Contenido
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'Recuperación de contraseña - Proyecto Z';
        
        // Cuerpo del mensaje HTML
        $mail->Body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .button { 
                    background-color: #39A900; 
                    border: none; 
                        color: #ffffff !important; 
                    padding: 15px 32px; 
                    text-align: center; 
                    text-decoration: none; 
                    display: inline-block; 
                    font-size: 16px; 
                    margin: 4px 2px; 
                    cursor: pointer; 
                    border-radius: 4px;
                }
                .footer { margin-top: 30px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2>Hola, " . htmlspecialchars($nombre) . "</h2>
                <p>Has solicitado restablecer tu contraseña. Haz clic en el siguiente botón para continuar:</p>
                <p style='text-align: center;'>
                    <a href='" . htmlspecialchars($resetLink) . "' class='button' style='color: #ffffff !important; text-decoration: none;'>Restablecer contraseña</a>
                </p>
                <p>Si el botón no funciona, copia y pega este enlace en tu navegador:</p>
                <p>" . htmlspecialchars($resetLink) . "</p>
                <p>Este enlace expirará en 30 minutos.</p>
                <p>Si no solicitaste este cambio, ignora este correo.</p>
                <div class='footer'>
                    <p>© " . date('Y') . " SENLOCK. Todos los derechos reservados.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        // Versión texto plano
        $mail->AltBody = "Hola " . $nombre . ",\n\nHas solicitado restablecer tu contraseña. Copia y pega este enlace en tu navegador:\n\n" . $resetLink . "\n\nEste enlace expirará en 30 minutos.\n\nSi no solicitaste este cambio, ignora este correo.";
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Error al enviar correo: " . $mail->ErrorInfo);
        return false;
    }
}

function enviarCorreoConfirmacion($correo, $nombre) {
    $config = require __DIR__ . '/../../config/mail.php';
    
    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host       = $config['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $config['username'];
        $mail->Password   = $config['password'];
        $mail->SMTPSecure = $config['encryption'];
        $mail->Port       = $config['port'];
        
        $mail->setFrom($config['from_email'], $config['from_name']);
        $mail->addAddress($correo, $nombre);
        
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'Contraseña actualizada - SENLOCK';
        
        // Construir enlace de login
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        $login_link = $protocol . $host . "/ProyectoZ/proyecto-z/src/views/login.php";
        
        $mail->Body = "
        <html>
        <body>
            <h2>Hola, " . htmlspecialchars($nombre) . "</h2>
            <p>Tu contraseña ha sido actualizada exitosamente.</p>
            <p>Puedes iniciar sesión con tu nueva contraseña:</p>
            <p style='text-align: center;'>
                <a href='" . htmlspecialchars($login_link) . "' style='background-color: #39A900; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>Iniciar sesión</a>
            </p>
            <p>Si no realizaste este cambio, contacta al administrador inmediatamente.</p>
        </body>
        </html>
        ";
        
        $mail->AltBody = "Hola " . $nombre . ",\n\nTu contraseña ha sido actualizada exitosamente.\n\nPuedes iniciar sesión en: " . $login_link . "\n\nSi no realizaste este cambio, contacta al administrador inmediatamente.";
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Error al enviar correo de confirmación: " . $mail->ErrorInfo);
        return false;
    }
}