<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isAuthenticated() {
    return isset($_SESSION['usuario_id']);
}

function requireAuth() {
    if (!isAuthenticated()) {
        header("Location: /login.php");
        exit;
    }
}

function user($key = null) {
    if (!isAuthenticated()) {
        return null;
    }

    if ($key) {
        return $_SESSION[$key] ?? null;
    }

    return $_SESSION;
}   

function hasRole($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

function requireRole($role) {
    if (!hasRole($role)) {
        header("Location: /unauthorized.php");
        exit;
    }
}

function logout() {
    session_unset();
    session_destroy();
    header("Location: /login.php");
    exit;
}