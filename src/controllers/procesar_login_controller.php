<?php
// procesar_login.php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {

    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    // ⚠️ EJEMPLO BÁSICO (sin base de datos)
    // Usuario prueba
    $usuario_prueba = "admin@gmail.com";
    $password_prueba = "123456";

    if ($email === $usuario_prueba && $password === $password_prueba) {

        session_start();
        $_SESSION['usuario'] = $email;

        header("Location: dashboard.php");
        exit;

    } else {
        header("Location: login.php?error=1");
        exit;
    }
}
