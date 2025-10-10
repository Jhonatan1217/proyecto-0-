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

    case 'listar':
        // Llama al método listar() para obtener todos los instructores
        $res = $instructor->listar();
        echo json_encode($res);
        break;

    case 'obtener':
        // Verifica que se haya enviado el parámetro id_instructor
        if (!isset($_GET['id_instructor'])) {
            echo json_encode(['error' => 'Debe enviar el parámetro id_instructor']);
            exit;
        }
        // Obtiene el instructor por su ID
        $res = $instructor->obtenerPorId($_GET['id_instructor']);
        echo json_encode($res);
        break;

    case 'crear':
        // Decodifica los datos recibidos en formato JSON
        $data = json_decode(file_get_contents("php://input"), true);

        // Obtiene los datos del instructor desde el cuerpo de la petición o desde POST
        $nombre = $data['nombre_instructor'] ?? $_POST['nombre_instructor'] ?? null;
        $apellido = $data['apellido_instructor'] ?? $_POST['apellido_instructor'] ?? null;
        $tipo = $data['tipo_instructor'] ?? $_POST['tipo_instructor'] ?? null;

        // Valida que todos los campos requeridos estén presentes
        if (!$nombre || !$apellido || !$tipo) {
            echo json_encode(['error' => 'Debe enviar nombre_instructor, apellido_instructor y tipo_instructor']);
            exit;
        }

        // Valida que el tipo de instructor sea válido
        $tiposValidos = ['TRANSVERSAL', 'TECNICO'];
        if (!in_array(strtoupper($tipo), $tiposValidos)) {
            echo json_encode(['error' => 'El tipo_instructor debe ser TRANSVERSAL o TECNICO']);
            exit;
        }

        // Llama al método crear() para insertar un nuevo instructor
        $res = $instructor->crear($nombre, $apellido, strtoupper($tipo));
        echo json_encode($res);
        break;

    case 'actualizar':
        // Decodifica los datos recibidos en formato JSON
        $data = json_decode(file_get_contents("php://input"), true);

        // Obtiene los datos del instructor desde el cuerpo de la petición o desde POST
        $id_instructor = $data['id_instructor'] ?? $_POST['id_instructor'] ?? null;
        $nombre = $data['nombre_instructor'] ?? $_POST['nombre_instructor'] ?? null;
        $apellido = $data['apellido_instructor'] ?? $_POST['apellido_instructor'] ?? null;
        $tipo = $data['tipo_instructor'] ?? $_POST['tipo_instructor'] ?? null;

        // Valida que todos los campos requeridos estén presentes
        if (!$id_instructor || !$nombre || !$apellido || !$tipo) {
            echo json_encode(['error' => 'Debe enviar id_instructor, nombre_instructor, apellido_instructor y tipo_instructor']);
            exit;
        }

        // Valida que el tipo de instructor sea válido
        $tiposValidos = ['TRANSVERSAL', 'TECNICO'];
        if (!in_array(strtoupper($tipo), $tiposValidos)) {
            echo json_encode(['error' => 'El tipo_instructor debe ser TRANSVERSAL o TECNICO']);
            exit;
        }

        // Llama al método actualizar() para modificar el instructor
        $res = $instructor->actualizar($id_instructor, $nombre, $apellido, strtoupper($tipo));
        echo json_encode($res);
        break;

    case 'eliminar':
        // Decodifica los datos recibidos en formato JSON
        $data = json_decode(file_get_contents("php://input"), true);
        // Obtiene el id_instructor desde el cuerpo de la petición o desde POST
        $id_instructor = $data['id_instructor'] ?? $_POST['id_instructor'] ?? null;

        // Valida que se haya enviado el id_instructor
        if (!$id_instructor) {
            echo json_encode(['error' => 'Debe enviar el parámetro id_instructor']);
            exit;
        }

        // Llama al método eliminar() para borrar el instructor
        $res = $instructor->eliminar($id_instructor);
        echo json_encode($res);
        break;

    default:
        // Acción no reconocida
        echo json_encode(['error' => 'Acción no válida']);
        break;
        //quitar este comentario
}
?>
