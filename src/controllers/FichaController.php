<?php
// Establece el tipo de contenido de la respuesta como JSON y configura la codificación de caracteres
header('Content-Type: application/json; charset=utf-8');

// Habilita la visualización de todos los errores para facilitar la depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Incluye el archivo de configuración de la base de datos y el modelo de Ficha
include_once __DIR__ . '/../../config/database.php';
include_once __DIR__ . '/../models/Ficha.php';

// Verifica que la conexión a la base de datos se haya establecido correctamente
if (!isset($conn)) {
    echo json_encode(['error' => 'No se pudo establecer conexión con la base de datos']);
    exit;
}

// Instancia el modelo Ficha pasando la conexión a la base de datos
$ficha = new Ficha($conn);

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
        // Llama al método listar() para obtener todas las fichas
        $res = $ficha->listar();
        echo json_encode($res);
        break;

    case 'obtener':
        // Verifica que se haya enviado el parámetro id_ficha
        if (!isset($_GET['id_ficha'])) {
            echo json_encode(['error' => 'Debe enviar el parámetro id_ficha']);
            exit;
        }
        // Obtiene la ficha por su ID
        $res = $ficha->obtenerPorId($_GET['id_ficha']);
        echo json_encode($res);
        break;

    // case 'crear':
    //     // Llama al método crear() para insertar una nueva ficha
    //     $res = $ficha->crear();
    //     echo json_encode($res);
    //     break;

    case 'eliminar':
        // Decodifica los datos recibidos en formato JSON
        $data = json_decode(file_get_contents("php://input"), true);
        // Obtiene el id_ficha desde los datos recibidos o desde POST
        $id_ficha = $data['id_ficha'] ?? $_POST['id_ficha'] ?? null;

        // Valida que el parámetro id_ficha sea válido
        if (!$id_ficha) {
            echo json_encode(['error' => 'Debe enviar el parámetro id_ficha']);
            exit;
        }

        // Llama al método eliminar() para borrar la ficha
        $res = $ficha->eliminar($id_ficha);
        echo json_encode($res);
        break;

    default:
        // Acción no reconocida
        echo json_encode(['error' => 'Acción no válida']);
        break;
}
?>
