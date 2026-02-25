<?php
session_start();
session_regenerate_id(true);

require_once __DIR__ . '/../../config/database.php';

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

$correo = trim($_POST['correo'] ?? '');
$password = trim($_POST['password'] ?? '');

if (empty($correo) || empty($password)) {
    header("Location: ../../index.php?page=login&error=1");
    exit;
}

try {

    $sql = "SELECT id_usuario, nombre_completo, correo_electronico, password_hash, estado 
            FROM usuarios 
            WHERE correo_electronico = :correo 
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":correo", $correo);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        header("Location: ../../index.php?page=login&error=1");
        exit;
    }

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (
        !password_verify($password, $usuario['password_hash']) &&
        $password !== $usuario['password_hash']
        ) {
            header("Location: ../../index.php?page=login&error=1");
            exit;
    }

    if ($usuario['estado'] != 1) {
        header("Location: ../../index.php?page=login&error=1");
        exit;
    }

    $_SESSION['usuario_id'] = $usuario['id_usuario'];
    $_SESSION['usuario_nombre'] = $usuario['nombre_completo'];
    $_SESSION['usuario_correo'] = $usuario['correo_electronico'];

    header("Location: ../../index.php?page=register_tables");
    exit;

} catch (PDOException $e) {
    die("Error en login: " . $e->getMessage());
}