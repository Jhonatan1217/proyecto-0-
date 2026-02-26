<?php
// Habilitar reporte de errores para desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Usuario.php'; // Ajusta la ruta a tu modelo

// ============================================================
// COMPATIBILIDAD GET / POST / JSON
// ============================================================
$__RAW = file_get_contents('php://input');
$__JSON = json_decode($__RAW, true);

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
        case 'listar':
            // Si se pide un ID específico
            if (isset($_GET['id'])) {
                $id = intval($_GET['id']);
                $data = $usuarioModel->obtenerPorId($id);
                echo json_encode($data ?: ['error' => 'Usuario no encontrado']);
                break;
            }

            // Filtrar por cargo principal
            $cargo = inreq('cargo'); // 'COORDINADOR' o 'INSTRUCTOR'
            if ($cargo) {
                $usuarios = $usuarioModel->listarPorCargo($cargo);
                echo json_encode($usuarios);
                break;
            }

            // Filtrar por rol funcional
            $id_rol_funcional = inreq('id_rol_funcional');
            if ($id_rol_funcional) {
                $usuarios = $usuarioModel->listarPorRolFuncional($id_rol_funcional);
                echo json_encode($usuarios);
                break;
            }

            // Listar todos
            $usuarios = $usuarioModel->listar();
            echo json_encode($usuarios);
            break;

        // ============================================================
        // CREAR USUARIO
        // ============================================================
        case 'crear':
            $datos = [
                'nombre_completo'   => trim((string) inreq('nombre_completo')),
                'tipo_documento'    => trim((string) inreq('tipo_documento')),
                'numero_documento'  => trim((string) inreq('numero_documento')),
                'correo_electronico' => trim((string) inreq('correo_electronico')),
                'cargo'             => trim((string) inreq('cargo')),
                'id_area'           => inreq('id_area') ? intval(inreq('id_area')) : null,
                'tipo_instructor'   => inreq('tipo_instructor') ? trim((string) inreq('tipo_instructor')) : null,
                'tipo_contrato'     => trim((string) inreq('tipo_contrato')) ?: 'CONTRATISTA',
                'password_hash'     => password_hash(trim((string) inreq('password')), PASSWORD_DEFAULT),
                'estado'            => inreq('estado') ?? 1,
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
            if ($resultado === true) {
                echo json_encode(['success' => true, 'message' => 'Usuario creado correctamente']);
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

            $datos = [
                'nombre_completo'   => trim((string) inreq('nombre_completo')),
                'tipo_documento'    => trim((string) inreq('tipo_documento')),
                'numero_documento'  => trim((string) inreq('numero_documento')),
                'correo_electronico' => trim((string) inreq('correo_electronico')),
                'cargo'             => trim((string) inreq('cargo')),
                'id_area'           => inreq('id_area') ? intval(inreq('id_area')) : null,
                'tipo_instructor'   => inreq('tipo_instructor') ? trim((string) inreq('tipo_instructor')) : null,
                'tipo_contrato'     => trim((string) inreq('tipo_contrato')),
                'estado'            => intval(inreq('estado'))
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