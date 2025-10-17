<?php
// Establece el tipo de contenido de la respuesta como JSON y la codificación de caracteres
header('Content-Type: application/json; charset=utf-8');

// Habilita la visualización de todos los errores para facilitar la depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Incluye el archivo de configuración de la base de datos y el modelo de Instructor
include_once __DIR__ . '/../../config/database.php';
include_once __DIR__ . '/../models/Instructor.php';

// Verifica que la conexión a la base de datos se haya establecido correctamente
if (!isset($conn)) {
    echo json_encode(['error' => 'No se pudo establecer conexión con la base de datos']);
    exit;
}

// ---------- Helpers mínimos añadidos (no rompen tu base) ----------
function validarSoloTexto($s) {
    // Solo letras (incluye acentos/ñ) y espacios
    return preg_match('/^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ\s]+$/u', $s) === 1;
}
function colapsarEspacios($s) {
    return trim(preg_replace('/\s{2,}/u', ' ', (string)$s));
}

// Instancia el modelo Instructor pasando la conexión a la base de datos
$instructor = new Instructor($conn);

// Obtiene la acción a realizar desde la URL (?accion=)
$accion = isset($_GET['accion']) ? $_GET['accion'] : null;

// Si no se especifica una acción, retorna un error
if (!$accion) {
    echo json_encode(['error' => 'Debe especificar la acción en la URL, por ejemplo: ?accion=listar']);
    exit;
}

// Estructura principal para manejar las diferentes acciones solicitadas
switch ($accion) {

    // 🔹 Listar todos los instructores
    case 'listar':
        $res = $instructor->listar();
        echo json_encode($res);
        break;

    // 🔹 Obtener un instructor por ID
    case 'obtener':
        if (!isset($_GET['id_instructor'])) {
            echo json_encode(['error' => 'Debe enviar el parámetro id_instructor']);
            exit;
        }
        $res = $instructor->obtenerPorId($_GET['id_instructor']);
        echo json_encode($res);
        break;

    // 🔹 Crear un nuevo instructor
    case 'crear':
        $data = json_decode(file_get_contents("php://input"), true);

        $nombre = $data['nombre_instructor'] ?? $_POST['nombre_instructor'] ?? null;
        $tipo   = $data['tipo_instructor']    ?? $_POST['tipo_instructor']    ?? null;

        if (!$nombre || !$tipo) {
            echo json_encode(['error' => 'Debe enviar nombre_instructor y tipo_instructor']);
            exit;
        }

        // Normalizar y validar nombre (solo letras y espacios)
        $nombre = colapsarEspacios($nombre);
        if ($nombre === '' || !validarSoloTexto($nombre)) {
            echo json_encode(['error' => 'El nombre solo puede contener letras y espacios']);
            exit;
        }

        $tiposValidos = ['TECNICO', 'TRANSVERSAL', 'MIXTO'];
        if (!in_array(strtoupper($tipo), $tiposValidos)) {
            echo json_encode(['error' => 'El tipo_instructor debe ser TECNICO, TRANSVERSAL o MIXTO']);
            exit;
        }

        $instructor->crear($nombre, strtoupper($tipo));
        echo json_encode(['mensaje' => 'Instructor creado correctamente']);
        break;

    // 🔹 Actualizar un instructor existente
    case 'actualizar':
        $data = json_decode(file_get_contents("php://input"), true);

        $id_instructor = $data['id_instructor']      ?? $_POST['id_instructor']      ?? null;
        $nombre        = $data['nombre_instructor']  ?? $_POST['nombre_instructor']  ?? null;
        $tipo          = $data['tipo_instructor']    ?? $_POST['tipo_instructor']    ?? null;

        if (!$id_instructor || !$nombre || !$tipo) {
            echo json_encode(['error' => 'Debe enviar id_instructor, nombre_instructor y tipo_instructor']);
            exit;
        }

        // Normalizar y validar nombre (solo letras y espacios)
        $nombre = colapsarEspacios($nombre);
        if ($nombre === '' || !validarSoloTexto($nombre)) {
            echo json_encode(['error' => 'El nombre solo puede contener letras y espacios']);
            exit;
        }

        $tiposValidos = ['TECNICO', 'TRANSVERSAL', 'MIXTO'];
        if (!in_array(strtoupper($tipo), $tiposValidos)) {
            echo json_encode(['error' => 'El tipo_instructor debe ser TECNICO, TRANSVERSAL o MIXTO']);
            exit;
        }

        $instructor->actualizar($id_instructor, $nombre, strtoupper($tipo));
        echo json_encode(['mensaje' => 'Instructor actualizado correctamente']);
        break;

    // 🔹 Eliminar un instructor
    case 'eliminar':
        $data = json_decode(file_get_contents("php://input"), true);
        $id_instructor = $data['id_instructor'] ?? $_POST['id_instructor'] ?? null;

        if (!$id_instructor) {
            echo json_encode(['error' => 'Debe enviar el parámetro id_instructor']);
            exit;
        }

        $instructor->eliminar($id_instructor);
        echo json_encode(['mensaje' => 'Instructor eliminado correctamente']);
        break;

    // 🔹 Cambiar el estado (activo/inactivo)
    case 'cambiar_estado':
        $data = json_decode(file_get_contents("php://input"), true);
        $id_instructor = $data['id_instructor'] ?? $_POST['id_instructor'] ?? $_GET['id_instructor'] ?? null;
        $estado        = $data['estado']        ?? $_POST['estado']        ?? $_GET['estado']        ?? null;

        if ($id_instructor === null || $estado === null) {
            echo json_encode(['error' => 'Debe enviar id_instructor y estado (1 o 0)']);
            exit;
        }

        if ($estado != 1 && $estado != 0) {
            echo json_encode(['error' => 'El estado debe ser 1 (activo) o 0 (inactivo)']);
            exit;
        }

        $instructor->cambiarEstado($id_instructor, $estado);
        echo json_encode(['mensaje' => 'Estado del instructor actualizado correctamente']);
        break;

    // 🔹 Acción no válida
    default:
        echo json_encode(['error' => 'Acción no válida']);
        break;
}
?>
