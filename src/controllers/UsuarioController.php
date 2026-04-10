<?php
// Habilitar reporte de errores para desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Usuario.php'; // Ajusta la ruta a tu modelo

// ============================================================
// COMPATIBILIDAD GET / POST / JSON
// ============================================================
$__RAW = file_get_contents('php://input');
$__JSON = json_decode($__RAW, true);

/** Valores válidos para tipo_documento (enum en DB) */
$TIPO_DOC_VALIDOS = ['CC', 'CE', 'PASAPORTE'];

function normalizarTipoDocumento($val) {
    global $TIPO_DOC_VALIDOS;
    $v = strtoupper(trim((string) $val));
    return in_array($v, $TIPO_DOC_VALIDOS) ? $v : 'CC';
}

/**
 * Resuelve el nombre del área a id_area. Si no existe, la crea.
 * @return int|null id_area o null si nombre vacío
 */
function resolverAreaPorNombre($conn, $nombre) {
    $nombre = trim((string) $nombre);
    if ($nombre === '') return null;
    $stmt = $conn->prepare("SELECT id_area FROM area WHERE TRIM(nombre_area) = :nombre AND estado = 1 LIMIT 1");
    $stmt->execute([':nombre' => $nombre]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) return (int) $row['id_area'];
    $stmt = $conn->prepare("INSERT INTO area (nombre_area, estado) VALUES (:nombre, 1)");
    $stmt->execute([':nombre' => $nombre]);
    return (int) $conn->lastInsertId();
}

function inreq($k) {
    global $__JSON;
    return $_POST[$k] ?? $_GET[$k] ?? ($__JSON[$k] ?? null);
}

// ============================================================
// CONTROLADOR USUARIO
// ============================================================
$accion = $_GET['accion'] ?? '';
$usuarioModel = new Usuario($conn);

try {
    switch ($accion) {

        // ============================================================
        // LISTAR USUARIOS CON FILTROS
        // ============================================================
        case 'obtener':
            $id = intval(inreq('id'));
            if (!$id) {
                echo json_encode(['error' => 'ID no proporcionado']);
                break;
            }
            $idSesion = intval($_SESSION['usuario_id'] ?? 0);
            // Permitir consultar datos "es_sistema" únicamente del propio usuario logueado.
            $incluirSistema = ($idSesion > 0 && $idSesion === $id);
            $data = $usuarioModel->obtenerPorId($id, $incluirSistema);
            if ($data) {
                // Asegurar que tipo_documento esté presente (por si la columna tiene otro nombre o es null)
                if (!isset($data['tipo_documento']) || $data['tipo_documento'] === null) {
                    $data['tipo_documento'] = $data['tipoDocumento'] ?? '';
                }
            }
            echo json_encode($data ?: ['error' => 'Usuario no encontrado']);
            break;

        case 'listar':
            // Si se pide un ID específico
            if (isset($_GET['id'])) {
                $id = intval($_GET['id']);
                $data = $usuarioModel->obtenerPorId($id);
                if ($data && (!isset($data['tipo_documento']) || $data['tipo_documento'] === null)) {
                    $data['tipo_documento'] = $data['tipoDocumento'] ?? '';
                }
                echo json_encode($data ?: ['error' => 'Usuario no encontrado']);
                break;
            }

            $cargo = trim((string) inreq('cargo'));
            $buscar = trim((string) inreq('buscar'));
            $id_rol_funcional = inreq('id_rol_funcional');
            // Rol solo aplica cuando el cargo es Instructor
            if (stripos($cargo, 'instructor') === false) {
                $id_rol_funcional = null;
            }
            $usuarios = $usuarioModel->listarConFiltros($cargo, $id_rol_funcional, $buscar);
            echo json_encode($usuarios);
            break;

        // ============================================================
        // CREAR USUARIO
        // ============================================================
        case 'crear':
            $cargo = trim((string) inreq('cargo'));
            $id_area = inreq('id_area') ? intval(inreq('id_area')) : null;
            if (!$id_area && stripos($cargo, 'coordinador') !== false) {
                $areaNombre = trim((string) inreq('area_coordinador'));
                $id_area = $areaNombre ? resolverAreaPorNombre($conn, $areaNombre) : null;
            }
            $datos = [
                'nombre_completo'   => trim((string) inreq('nombre_completo')),
                'tipo_documento'    => normalizarTipoDocumento(inreq('tipo_documento')),
                'numero_documento'  => trim((string) inreq('numero_documento')),
                'correo_electronico' => trim((string) inreq('correo_electronico')),
                'cargo'             => $cargo,
                'id_area'           => $id_area,
                'tipo_instructor'   => inreq('tipo_instructor') ? trim((string) inreq('tipo_instructor')) : null,
                'tipo_contrato'     => trim((string) inreq('tipo_contrato')) ?: 'CONTRATISTA',
                'password_hash'     => password_hash(trim((string) inreq('password')), PASSWORD_DEFAULT),
                'estado'            => (int) (inreq('estado') ?? 0),
                'es_sistema'        => inreq('es_sistema') ?? 0
            ];

            // Validaciones básicas
            $camposRequeridos = ['nombre_completo', 'tipo_documento', 'numero_documento', 'correo_electronico', 'cargo', 'password_hash'];
            foreach ($camposRequeridos as $campo) {
                if (empty($datos[$campo])) {
                    echo json_encode(['error' => "El campo $campo es obligatorio"]);
                    exit;
                }
            }

            $resultado = $usuarioModel->crear($datos);
            if (is_numeric($resultado) && (int) $resultado > 0) {
                echo json_encode(['success' => true, 'message' => 'Usuario creado correctamente', 'id_usuario' => (int) $resultado]);
            } else {
                echo json_encode(['error' => $resultado]);
            }
            break;

        // ============================================================
        // ACTUALIZAR USUARIO
        // ============================================================
        case 'actualizar':
            $id_usuario = intval(inreq('id_usuario'));
            if (!$id_usuario) {
                echo json_encode(['error' => 'ID de usuario no proporcionado']);
                exit;
            }

            // Obtener estado actual para no perderlo si no viene en la petición
            $usuarioActual = $usuarioModel->obtenerPorId($id_usuario);
            if (!$usuarioActual) {
                echo json_encode(['error' => 'Usuario no encontrado']);
                exit;
            }

            $estadoRequest = inreq('estado');
            $cargo = trim((string) inreq('cargo'));
            $id_area = inreq('id_area') ? intval(inreq('id_area')) : null;
            if (!$id_area && stripos($cargo, 'coordinador') !== false) {
                $areaNombre = trim((string) inreq('area_coordinador'));
                $id_area = $areaNombre ? resolverAreaPorNombre($conn, $areaNombre) : null;
            }

            $datos = [
                'nombre_completo'   => trim((string) inreq('nombre_completo')),
                'tipo_documento'    => normalizarTipoDocumento(inreq('tipo_documento')),
                'numero_documento'  => trim((string) inreq('numero_documento')),
                'correo_electronico' => trim((string) inreq('correo_electronico')),
                'cargo'             => $cargo,
                'id_area'           => $id_area,
                'tipo_instructor'   => inreq('tipo_instructor') ? trim((string) inreq('tipo_instructor')) : null,
                'tipo_contrato'     => trim((string) inreq('tipo_contrato')),
                'estado'            => $estadoRequest !== null ? intval($estadoRequest) : (int)($usuarioActual['estado'] ?? 1),
            ];

            // Contraseña nueva (si se envió)
            $nueva_pass = trim((string) inreq('password'));
            if (!empty($nueva_pass)) {
                $datos['password_hash'] = password_hash($nueva_pass, PASSWORD_DEFAULT);
            }

            $resultado = $usuarioModel->actualizar($id_usuario, $datos);
            if ($resultado === true) {
                echo json_encode(['success' => true, 'message' => 'Usuario actualizado correctamente']);
            } else {
                echo json_encode(['error' => $resultado]);
            }
            break;

        // ============================================================
        // CAMBIAR ESTADO (INHABILITAR/ACTIVAR)
        // ============================================================
        case 'cambiarEstado':
            $id_usuario = intval(inreq('id_usuario'));
            $estado = intval(inreq('estado'));

            if (!$id_usuario) {
                echo json_encode(['error' => 'ID de usuario no proporcionado']);
                exit;
            }

            if (!$usuarioModel->obtenerPorId($id_usuario)) {
                echo json_encode(['error' => 'Usuario no encontrado']);
                exit;
            }

            $resultado = $usuarioModel->cambiarEstado($id_usuario, $estado);
            if ($resultado === true) {
                $mensaje = $estado ? 'Usuario activado correctamente' : 'Usuario inhabilitado correctamente';
                echo json_encode(['success' => true, 'message' => $mensaje]);
            } else {
                echo json_encode(['error' => $resultado]);
            }
            break;

        // ============================================================
        // GESTIÓN DE ROLES FUNCIONALES
        // ============================================================

        // Listar roles funcionales de un usuario
        case 'listarRolesUsuario':
            $id_usuario = intval(inreq('id_usuario'));
            if (!$id_usuario) {
                echo json_encode(['error' => 'ID de usuario no proporcionado']);
                exit;
            }
            if (!$usuarioModel->obtenerPorId($id_usuario)) {
                echo json_encode(['error' => 'Usuario no encontrado']);
                exit;
            }
            $roles = $usuarioModel->listarRolesFuncionalesPorUsuario($id_usuario);
            echo json_encode($roles);
            break;

        // Asignar un rol funcional a un usuario
        case 'asignarRol':
            $id_usuario = intval(inreq('id_usuario'));
            $id_rol = intval(inreq('id_rol'));
            $asignado_por = intval(inreq('asignado_por')); // ID del usuario que está haciendo la asignación

            if (!$id_usuario || !$id_rol || !$asignado_por) {
                echo json_encode(['error' => 'Faltan parámetros (id_usuario, id_rol, asignado_por)']);
                exit;
            }

            if (!$usuarioModel->obtenerPorId($id_usuario)) {
                echo json_encode(['error' => 'Usuario no encontrado']);
                exit;
            }

            $resultado = $usuarioModel->asignarRolFuncional($id_usuario, $id_rol, $asignado_por);
            if ($resultado) {
                echo json_encode(['success' => true, 'message' => 'Rol asignado correctamente']);
            } else {
                echo json_encode(['error' => 'No se pudo asignar el rol (puede que ya lo tenga)']);
            }
            break;

        // Quitar un rol funcional de un usuario
        case 'quitarRol':
            $id_usuario = intval(inreq('id_usuario'));
            $id_rol = intval(inreq('id_rol'));

            if (!$id_usuario || !$id_rol) {
                echo json_encode(['error' => 'Faltan parámetros (id_usuario, id_rol)']);
                exit;
            }

            if (!$usuarioModel->obtenerPorId($id_usuario)) {
                echo json_encode(['error' => 'Usuario no encontrado']);
                exit;
            }

            $resultado = $usuarioModel->quitarRolFuncional($id_usuario, $id_rol);
            if ($resultado) {
                echo json_encode(['success' => true, 'message' => 'Rol quitado correctamente']);
            } else {
                echo json_encode(['error' => 'No se pudo quitar el rol']);
            }
            break;

        // Listar roles funcionales disponibles (para llenar combos)
        case 'rolesDisponibles':
            $roles = $usuarioModel->listarRolesFuncionalesDisponibles();
            echo json_encode($roles);
            break;

        // ============================================================
        // CAMBIAR CONTRASEÑA (perfil del usuario logueado)
        // ============================================================
        case 'cambiarContrasena':
            $id_sesion = (int) ($_SESSION['usuario_id'] ?? 0);
            $id_usuario = intval(inreq('id_usuario'));
            $password_actual = trim((string) inreq('password_actual'));
            $password_nueva = trim((string) inreq('password_nueva'));

            if (!$id_sesion) {
                echo json_encode(['error' => 'Debe iniciar sesión']);
                break;
            }
            if (!$id_usuario || $id_usuario !== $id_sesion) {
                echo json_encode(['error' => 'No puede cambiar la contraseña de otro usuario']);
                break;
            }
            if (empty($password_actual)) {
                echo json_encode(['error' => 'Debe ingresar la contraseña actual']);
                break;
            }
            if (empty($password_nueva)) {
                echo json_encode(['error' => 'Debe ingresar la nueva contraseña']);
                break;
            }

            $resultado = $usuarioModel->cambiarContrasena($id_usuario, $password_actual, $password_nueva);
            if ($resultado === true) {
                echo json_encode(['success' => true, 'message' => 'Contraseña actualizada correctamente']);
            } else {
                echo json_encode(['error' => $resultado]);
            }
            break;

        // ============================================================
        // UTILIDADES PARA FILTROS (Áreas, etc.)
        // ============================================================
        case 'areas':
            $stmt = $conn->query("SELECT id_area, nombre_area FROM area WHERE estado = 1 ORDER BY nombre_area");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            break;

        default:
            echo json_encode(['error' => 'Acción no válida']);
            break;
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>