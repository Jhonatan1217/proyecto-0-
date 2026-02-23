<?php
session_start();

require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../../config/database.php';

$usuarioModel = new Usuario($conn);

$accion = $_GET['accion'] ?? '';

switch ($accion) {

    /* ======================================
       CREAR
    ====================================== */
    case 'crear':

        $nombre = trim($_POST['nombre'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($nombre) || empty($correo) || empty($password)) {
            header("Location: ../../index.php?page=usuarios&error=1");
            exit;
        }

        $usuarioModel->crear($nombre, $correo, $password);

        header("Location: ../../index.php?page=usuarios&success=1");
        exit;


    /* ======================================
       ACTUALIZAR
    ====================================== */
    case 'actualizar':

        $id = $_POST['id'] ?? 0;
        $nombre = trim($_POST['nombre'] ?? '');
        $correo = trim($_POST['correo'] ?? '');

        if (empty($id) || empty($nombre) || empty($correo)) {
            header("Location: ../../index.php?page=usuarios&error=1");
            exit;
        }

        $usuarioModel->actualizar($id, $nombre, $correo);

        header("Location: ../../index.php?page=usuarios&success=2");
        exit;


    /* ======================================
       DESHABILITAR (SOFT DELETE)
    ====================================== */
    case 'deshabilitar':

        $id = $_GET['id'] ?? 0;

        if ($id) {
            $usuarioModel->deshabilitar($id);
        }

        header("Location: ../../index.php?page=usuarios");
        exit;


    /* ======================================
       HABILITAR
    ====================================== */
    case 'habilitar':

        $id = $_GET['id'] ?? 0;

        if ($id) {
            $usuarioModel->habilitar($id);
        }

        header("Location: ../../index.php?page=usuarios");
        exit;


    /* ======================================
       VER UNO (REDIRECCIONA A VISTA)
    ====================================== */
    case 'ver':

        $id = $_GET['id'] ?? 0;

        if (!$id) {
            header("Location: ../../index.php?page=usuarios");
            exit;
        }

        $_SESSION['usuario_ver_id'] = $id;

        header("Location: ../../index.php?page=usuario_detalle");
        exit;


    default:
        header("Location: ../../index.php?page=usuarios");
        exit;
}