<?php
// Establece el tipo de contenido de la respuesta como JSON y configura la codificación de caracteres
header('Content-Type: application/json; charset=utf-8');

// Habilita la visualización de todos los errores para facilitar la depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Incluye el archivo de configuración de la base de datos y el modelo de Competencia
include_once __DIR__ . '/../../config/database.php';
include_once __DIR__ . '/../models/Competencia.php';

// Verifica que la conexión a la base de datos se haya establecido correctamente
if (!isset($conn)) {
    echo json_encode(['error' => 'No se pudo establecer conexión con la base de datos']);
    exit;
}

// Instancia el modelo Competencia pasando la conexión a la base de datos
$competencia = new Competencia($conn);

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
        // Llama al método listar() para obtener todas las competencias
        $res = $competencia->listar();
        echo json_encode($res);
        break;

    case 'obtener':
        // Verifica que se haya enviado el parámetro id_competencia
        if (!isset($_GET['id_competencia'])) {
            echo json_encode(['error' => 'Debe enviar el parámetro id_competencia']);
            exit;
        }
        // Obtiene la competencia por su ID
        $res = $competencia->obtenerPorId($_GET['id_competencia']);
        echo json_encode($res);
        break;

    case 'crear':
        // Decodifica los datos recibidos en formato JSON
        $data = json_decode(file_get_contents("php://input"), true);
        // Obtiene la descripción desde los datos recibidos o desde POST
        $descripcion = $data['descripcion'] ?? $_POST['descripcion'] ?? null;

        // Valida que la descripción no esté vacía
        if (!$descripcion || trim($descripcion) === '') {
            echo json_encode(['error' => 'Debe enviar una descripción válida']);
            exit;
        }

        // Llama al método crear() para insertar una nueva competencia
        $res = $competencia->crear(trim($descripcion));
        echo json_encode($res);
        break;

    case 'actualizar':
        // Decodifica los datos recibidos en formato JSON
        $data = json_decode(file_get_contents("php://input"), true);
        // Obtiene el id_competencia y la descripción desde los datos recibidos o desde POST
        $id_competencia = $data['id_competencia'] ?? $_POST['id_competencia'] ?? null;
        $descripcion = $data['descripcion'] ?? $_POST['descripcion'] ?? null;

        // Valida que ambos parámetros sean válidos
        if (!$id_competencia || !$descripcion || trim($descripcion) === '') {
            echo json_encode(['error' => 'Debe enviar id_competencia y una descripción válida']);
            exit;
        }

        // Llama al método actualizar() para modificar la competencia
        $res = $competencia->actualizar($id_competencia, trim($descripcion));
        echo json_encode($res);
        break;

    case 'eliminar':
        // Decodifica los datos recibidos en formato JSON
        $data = json_decode(file_get_contents("php://input"), true);
        // Obtiene el id_competencia desde los datos recibidos o desde POST
        $id_competencia = $data['id_competencia'] ?? $_POST['id_competencia'] ?? null;

        // Valida que se haya enviado el id_competencia
        if (!$id_competencia) {
            echo json_encode(['error' => 'Debe enviar el parámetro id_competencia']);
            exit;
        }

        // Llama al método eliminar() para borrar la competencia
        $res = $competencia->eliminar($id_competencia);
        echo json_encode($res);
        break;

    default:
        // Si la acción no es válida, retorna un error
        echo json_encode(['error' => 'Acción no válida']);
        break;
}
?>
