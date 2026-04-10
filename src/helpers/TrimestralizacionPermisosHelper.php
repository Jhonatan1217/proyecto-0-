<?php

/**
 * Permisos de trimestralización / horarios (vista register_tables y TrimestralizacionController).
 *
 * Crear trimestralización: COORDINADOR, o INSTRUCTOR con rol ENCARGADO_TRIMESTRALIZACION.
 * Gestión de horas, limpiar trimestralización y persistencia masiva (actualizar): misma base que
 * "crear" para instructores; cualquier otro cargo autenticado conserva acceso operativo.
 */

require_once __DIR__ . '/../models/Usuario.php';

/**
 * @param PDO $conn
 * @param int $idUsuario
 */
function trimestralizacion_usuario_tiene_rol_encargado(PDO $conn, $idUsuario): bool
{
    $idUsuario = (int) $idUsuario;
    if ($idUsuario <= 0) {
        return false;
    }
    $usuarioModel = new Usuario($conn);
    foreach ($usuarioModel->listarRolesFuncionalesPorUsuario($idUsuario) as $r) {
        if (strtoupper((string) ($r['nombre_rol'] ?? '')) === 'ENCARGADO_TRIMESTRALIZACION') {
            return true;
        }
    }

    return false;
}

/**
 * Permisos para la vista de horarios (register_tables).
 *
 * @param PDO $conn
 * @param array $session normalmente $_SESSION
 * @return array{puede_crear_trimestralizacion: bool, puede_gestionar_horas_y_limpiar: bool, tiene_rol_encargado_trimestralizacion: bool}
 */
function trimestralizacion_permisos_horarios(PDO $conn, array $session): array
{
    $idUsuario = (int) ($session['usuario_id'] ?? 0);
    $cargo = strtoupper(trim((string) ($session['usuario_cargo'] ?? '')));

    $tieneEncargado = false;
    if ($cargo === 'INSTRUCTOR' && $idUsuario > 0) {
        $tieneEncargado = trimestralizacion_usuario_tiene_rol_encargado($conn, $idUsuario);
    }

    $puedeCrear = $cargo === 'COORDINADOR' || ($cargo === 'INSTRUCTOR' && $tieneEncargado);
    $puedeGestionarHorasLimpiar = $idUsuario > 0 && ($puedeCrear || $cargo !== 'INSTRUCTOR');

    return [
        'tiene_rol_encargado_trimestralizacion' => $tieneEncargado,
        'puede_crear_trimestralizacion' => $puedeCrear,
        'puede_gestionar_horas_y_limpiar' => $puedeGestionarHorasLimpiar,
    ];
}

/**
 * API: crear nueva trimestralización (POST crear).
 */
function trimestralizacion_puede_crear_api(PDO $conn): bool
{
    $idUsuario = (int) ($_SESSION['usuario_id'] ?? 0);
    if ($idUsuario <= 0) {
        return false;
    }
    $cargo = strtoupper(trim((string) ($_SESSION['usuario_cargo'] ?? '')));
    if ($cargo === 'COORDINADOR') {
        return true;
    }
    if ($cargo === 'INSTRUCTOR') {
        return trimestralizacion_usuario_tiene_rol_encargado($conn, $idUsuario);
    }

    return false;
}

/**
 * API: resumen de horas, limpiar trimestralización, actualización masiva desde la grilla.
 */
function trimestralizacion_puede_gestionar_horas_o_limpiar_api(PDO $conn): bool
{
    $idUsuario = (int) ($_SESSION['usuario_id'] ?? 0);
    if ($idUsuario <= 0) {
        return false;
    }
    $cargo = strtoupper(trim((string) ($_SESSION['usuario_cargo'] ?? '')));
    if ($cargo === 'INSTRUCTOR') {
        return trimestralizacion_usuario_tiene_rol_encargado($conn, $idUsuario);
    }

    return true;
}
