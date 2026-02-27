<?php
session_start();

require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/TokenRecuperacion.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/mail.php';
require_once __DIR__ . '/../../vendor/autoload.php'; // Para PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

$usuarioModel = new Usuario($conn);
$tokenModel = new TokenRecuperacion($conn);

$accion = $_GET['accion'] ?? '';

switch ($accion) {
    
    /* ======================================
       SOLICITUD DE RECUPERACIÓN (ENVIAR CORREO)
    ====================================== */
    case 'solicitar':
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: ../../index.php?page=restablecerContrasenia");
        exit;
        }
        
        $correo = trim($_POST['correo'] ?? '');
        
        if (empty($correo)) {
            $_SESSION['error_recuperacion'] = "El correo es obligatorio";
            header("Location: ../../index.php?page=restablecerContrasenia");
            exit;
        }
        
        // Buscar usuario por correo
        $usuario = $usuarioModel->obtenerPorCorreo($correo);
        
        // Siempre mostrar mismo mensaje por seguridad (no revelar si existe o no)
        $mensaje = "Si el correo existe en nuestro sistema, recibirás instrucciones para restablecer tu contraseña.";
        
        if ($usuario && $usuario['estado'] == 1) { // Usuario activo
            
            // Generar token
            $token = $tokenModel->generarToken($usuario['id_usuario']);
            
            if ($token) {
                // Enviar correo
                if (enviarCorreoRecuperacion($usuario['correo_electronico'], $usuario['nombre_completo'], $token)) {
                    $_SESSION['success_recuperacion'] = $mensaje;
                } else {
                    error_log("Error al enviar correo a: " . $usuario['correo_electronico']);
                    $_SESSION['error_recuperacion'] = "Error al enviar el correo. Por favor intenta más tarde.";
                }
            } else {
                error_log("Error al generar token para usuario: " . $usuario['id_usuario']);
                $_SESSION['error_recuperacion'] = "Error al procesar la solicitud. Por favor intenta más tarde.";
            }
        } else {
            // Usuario no existe o está inactivo - mismo mensaje por seguridad
            $_SESSION['success_recuperacion'] = $mensaje;
        }
        
        header("Location: ../../index.php?page=restablecerContrasenia");
        exit;
    
    
    /* ======================================
       VERIFICAR TOKEN Y MOSTRAR FORMULARIO
    ====================================== */
    case 'verificar':
        
        $token = $_GET['token'] ?? '';
        
        if (empty($token)) {
            header("Location: ../../index.php?page=restablecerContrasenia");
            exit;
        }
        
        // Verificar token
        $tokenData = $tokenModel->verificarToken($token);
        
        if (!$tokenData) {
            $_SESSION['error_recuperacion'] = "El enlace no es válido o ha expirado";
            header("Location: ../../index.php?page=restablecerContrasenia");
            exit;
        }
        
        // Guardar token en sesión para el siguiente paso
        $_SESSION['reset_token'] = $token;
        $_SESSION['reset_usuario_id'] = $tokenData['id_usuario'];
        $_SESSION['reset_correo'] = $tokenData['correo_electronico'];
        
        // Redirigir al formulario de cambio de contraseña
        header("Location: ../../index.php?page=cambiarContrasenia");
        exit;
    
    
    /* ======================================
       CAMBIAR CONTRASEÑA
    ====================================== */
    case 'cambiar':
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ../../index.php?page=restablecerContrasenia");
            exit;
        }
        
        // Verificar que tenga token válido en sesión
        if (!isset($_SESSION['reset_token']) || !isset($_SESSION['reset_usuario_id'])) {
            $_SESSION['error_recuperacion'] = "Sesión de recuperación no válida";
            header("Location: ../../index.php?page=restablecerContrasenia");
            exit;
        }
        
        $password = $_POST['password'] ?? '';
        $confirmar = $_POST['confirmar_password'] ?? '';
        
        // Validaciones
        if (strlen($password) < 8) {
            $_SESSION['error_password'] = "La contraseña debe tener al menos 8 caracteres";
            header("Location: ../../index.php?page=cambiarContrasenia");
            exit;
        }
        
        if ($password !== $confirmar) {
            $_SESSION['error_password'] = "Las contraseñas no coinciden";
            header("Location: ../../index.php?page=cambiarContrasenia");
            exit;
        }
        
        // Verificar que el token sigue siendo válido
        $tokenData = $tokenModel->verificarToken($_SESSION['reset_token']);
        
        if (!$tokenData || $tokenData['id_usuario'] != $_SESSION['reset_usuario_id']) {
            $_SESSION['error_recuperacion'] = "El enlace ha expirado o ya fue utilizado";
            unset($_SESSION['reset_token'], $_SESSION['reset_usuario_id'], $_SESSION['reset_correo']);
            header("Location: ../../index.php?page=restablecerContrasenia");
            exit;
        }
        
        // Cambiar contraseña
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        if ($usuarioModel->actualizarPassword($_SESSION['reset_usuario_id'], $password_hash)) {
            
            // Marcar token como usado
            $tokenModel->marcarComoUsado($_SESSION['reset_token']);
            
            // Enviar correo de confirmación
            if (isset($_SESSION['reset_correo'])) {
                enviarCorreoConfirmacion($_SESSION['reset_correo'], $tokenData['nombre_completo'] ?? 'Usuario');
            }
            
            // Limpiar sesión
            unset($_SESSION['reset_token'], $_SESSION['reset_usuario_id'], $_SESSION['reset_correo']);
            
            $_SESSION['success_recuperacion'] = "Contraseña actualizada correctamente. Ya puedes iniciar sesión.";
            header("Location: ../../index.php?page=login");
            exit;
            
        } else {
            $_SESSION['error_password'] = "Error al actualizar la contraseña";
            header("Location: ../../index.php?page=cambiarContrasenia");
            exit;
        }
    
    
    default:
        header("Location: ../../index.php?page=restablecerContrasenia");
        exit;
}

/* ======================================
   FUNCIONES DE CORREO
====================================== */

function enviarCorreoRecuperacion($correo, $nombre, $token) {
    $config = require __DIR__ . '/../../config/mail.php';
    
    $mail = new PHPMailer(true);
    
    try {
        // Configuración del servidor
        $mail->SMTPDebug = 0; // 0 para producción, 2 para debug
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
        $mail->Subject = 'Recuperación de contraseña';
        
        // Obtener la ruta base del proyecto automáticamente
        $base_url = 'http://localhost/ProyectoZ';
        $reset_link = $base_url . '/index.php?page=restablecerContrasenia&accion=verificar&token=' . $token;
        
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
                    color: white; 
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
                <h2>Hola, $nombre</h2>
                <p>Has solicitado restablecer tu contraseña. Haz clic en el siguiente botón para continuar:</p>
                <p style='text-align: center;'>
                    <a href='$reset_link' class='button'>Restablecer contraseña</a>
                </p>
                <p>Si el botón no funciona, copia y pega este enlace en tu navegador:</p>
                <p>$reset_link</p>
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
        $mail->AltBody = "Hola $nombre,\n\nHas solicitado restablecer tu contraseña. Copia y pega este enlace en tu navegador:\n\n$reset_link\n\nEste enlace expirará en 30 minutos.\n\nSi no solicitaste este cambio, ignora este correo.";
        
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
        $mail->Subject = 'Contraseña actualizada';
        
        $login_link = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/index.php?page=login';
        
        $mail->Body = "
        <html>
        <body>
            <h2>Hola, $nombre</h2>
            <p>Tu contraseña ha sido actualizada exitosamente.</p>
            <p>Puedes iniciar sesión con tu nueva contraseña:</p>
            <p style='text-align: center;'>
                <a href='$login_link' style='background-color: #39A900; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>Iniciar sesión</a>
            </p>
            <p>Si no realizaste este cambio, contacta al administrador inmediatamente.</p>
        </body>
        </html>
        ";
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Error al enviar correo de confirmación: " . $mail->ErrorInfo);
        return false;
    }
}