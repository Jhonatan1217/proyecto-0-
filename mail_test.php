<?php
require_once __DIR__ . '/vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$config = require __DIR__ . '/config/mail.php';

$mail = new PHPMailer(true);

try {
    // Configuración detallada para debug
    $mail->SMTPDebug = 2; // Cambia a 2 para ver todo el debug
    $mail->Debugoutput = function($str, $level) {
        echo "SMTP Debug: $str\n";
    };
    
    $mail->isSMTP();
    $mail->Host       = $config['host'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $config['username'];
    $mail->Password   = $config['password'];
    $mail->SMTPSecure = $config['encryption'];
    $mail->Port       = $config['port'];
    
    // Configuración adicional para Gmail
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
    
    $mail->setFrom($config['from_email'], $config['from_name']);
    $mail->addAddress('destinatario@test.com', 'Test'); // Cambia por tu correo
    
    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';
    $mail->Subject = 'Test de configuración SMTP';
    $mail->Body    = 'Este es un correo de prueba para verificar la configuración SMTP.';
    
    if($mail->send()) {
        echo "Correo enviado correctamente\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $mail->ErrorInfo . "\n";
}