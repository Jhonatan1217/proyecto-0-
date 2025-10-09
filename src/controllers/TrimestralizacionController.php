<?php
// Establece el tipo de contenido de la respuesta como JSON y la codificación de caracteres
header('Content-Type: application/json; charset=utf-8');

// Habilita la visualización de todos los errores para facilitar la depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Incluye el archivo de configuración de la base de datos y el modelo de Trimestralizacion
include_once __DIR__ . '/../../config/database.php';
include_once __DIR__ . '/../models/Trimestralizacion.php';

// Verifica que la conexión a la base de datos se haya establecido correctamente
if (!isset($conn)) {
    echo json_encode(['error' => 'No se pudo establecer conexión con la base de datos']);
    exit;
}

// Instancia el modelo Trimestralizacion pasando la conexión a la base de datos
$trimestral = new Trimestralizacion($conn);

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
        // Llama al método listar() para obtener todas las trimestralizaciones
        $res = $trimestral->listar();
        echo json_encode($res);
        break;

    case 'obtener':
        // Verifica que se haya enviado el parámetro id_trimestral
        if (!isset($_GET['id_trimestral'])) {
            echo json_encode(['error' => 'Debe enviar el parámetro id_trimestral']);
            exit;
        }
        // Obtiene la trimestralización por su ID
        $res = $trimestral->obtenerPorId($_GET['id_trimestral']);
        echo json_encode($res);
        break;

    case 'crear':
        // Decodifica los datos recibidos en formato JSON
        $data = json_decode(file_get_contents("php://input"), true);
        // Obtiene el id_horario desde los datos recibidos o desde POST
        $id_horario = $data['id_horario'] ?? $_POST['id_horario'] ?? null;

        // Valida que el id_horario no esté vacío
        if (!$id_horario) {
            echo json_encode(['error' => 'Debe enviar el campo id_horario']);
            exit;
        }

        // Llama al método crear() para insertar una nueva trimestralización
        $res = $trimestral->crear($id_horario);
        echo json_encode($res);
        break;

    case 'eliminar':
        // Decodifica los datos recibidos en formato JSON
        $data = json_decode(file_get_contents("php://input"), true);
        // Obtiene el id_trimestral desde los datos recibidos o desde POST
        $id_trimestral = $data['id_trimestral'] ?? $_POST['id_trimestral'] ?? null;

        // Valida que el id_trimestral no esté vacío
        if (!$id_trimestral) {
            echo json_encode(['error' => 'Debe enviar el parámetro id_trimestral']);
            exit;
        }

        // Llama al método eliminar() para borrar la trimestralización
        $res = $trimestral->eliminar($id_trimestral);
        echo json_encode($res);
        break;

    default:
        // Acción no reconocida
        echo json_encode(['error' => 'Acción no válida']);
        break;
}
?>
