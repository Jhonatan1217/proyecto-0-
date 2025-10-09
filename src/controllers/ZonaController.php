<?php
// Establece el tipo de contenido de la respuesta como JSON y la codificación de caracteres
header('Content-Type: application/json; charset=utf-8');

// Habilita la visualización de todos los errores para facilitar la depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluye el archivo de configuración de la base de datos y el modelo de Zona
include_once __DIR__ . '/../../config/database.php';
include_once '../models/Zona.php';

// Instancia el modelo Zona pasando la conexión a la base de datos
$zona = new Zona($conn);

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
        // Llama al método listar() para obtener todas las zonas
        $res = $zona->listar();
        echo json_encode($res);
        break;

    case 'obtener':
        // Verifica que se haya enviado el parámetro id_zona
        if (!isset($_GET['id_zona'])) {
            echo json_encode(['error' => 'Debe enviar el parámetro id_zona']);
            exit;
        }
        // Obtiene la zona por su ID
        $res = $zona->obtenerPorId($_GET['id_zona']);
        echo json_encode($res);
        break;

    case 'crear':
        // En la tabla actual no hay más campos, así que solo se crea una fila vacía (id auto-incremental)
        $res = $zona->crear();
        echo json_encode($res);
        break;

    case 'eliminar':
        // Obtiene los datos enviados en el cuerpo de la petición (JSON)
        $data = json_decode(file_get_contents("php://input"), true);

        // Verifica que se haya enviado el parámetro id_zona
        if (!isset($data['id_zona'])) {
            echo json_encode(['error' => 'Debe enviar el parámetro id_zona']);
            exit;
        }

        // Llama al método eliminar() para borrar la zona por su ID
        $res = $zona->eliminar($data['id_zona']);
        echo json_encode($res);
        break;

    default:
        // Si la acción no es válida, retorna un error
        echo json_encode(['error' => 'Acción no válida']);
        break;
}
?>
