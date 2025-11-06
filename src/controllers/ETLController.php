<?php
// Mostrar errores (para depurar)
ini_set("display_errors", 1);
error_reporting(E_ALL);

// 1) Cargar Autoload de Composer (para PhpSpreadsheet)
require_once __DIR__ . '/../../vendor/autoload.php';

// 2) Conexión a base de datos (usa tu archivo tal como lo tienes)
require_once __DIR__ . '/../../config/database.php';
// Ahora tienes disponible: $conn (NO $db)

// 3) Modelos
require_once __DIR__ . '/../models/Competencia.php';
require_once __DIR__ . '/../models/Rae.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

class EtlController {

    public function test() {
        echo "✅ CONTROLADOR FUNCIONA";
    }

    public function subir() {

        // Validar archivo
        if (!isset($_FILES['archivo'])) {
            echo "❌ No se recibió archivo.";
            return;
        }

        // Validar programa seleccionado
        $programa = $_POST['programa'] ?? null;
        if (!$programa) {
            echo "❌ Debe seleccionar un programa.";
            return;
        }

        global $conn; // <- Importamos conexión correcta
        $competenciasModel = new Competencia($conn);
        $raesModel = new Rae($conn);

        $file = $_FILES['archivo']['tmp_name'];

        try {
            $spreadsheet = IOFactory::load($file);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            $insertadasComp = 0;
            $insertadasRae = 0;

            // La tabla comienza desde fila 14 -> índice 13
            for ($i = 13; $i < count($rows); $i++) {

                $comp = trim($rows[$i][5] ?? "");
                $rae  = trim($rows[$i][6] ?? "");

                if ($comp === "" || $rae === "") continue;

                // Separar COMPETENCIA: "228115 - APLICAR DISEÑO DE SOFTWARE"
                [$codC, $nomC] = array_map('trim', explode("-", $comp, 2));

                // Crear competencia si no existe
                $conn->prepare("INSERT IGNORE INTO competencias (codigo, nombre_competencia, id_programa, estado) VALUES (?, ?, ?, 1)")
                     ->execute([$codC, $nomC, $programa]);

                // Obtener ID de competencia
                $stmt = $conn->prepare("SELECT id_competencia FROM competencias WHERE codigo = ? AND id_programa = ?");
                $stmt->execute([$codC, $programa]);
                $idComp = $stmt->fetchColumn();

                if ($idComp) $insertadasComp++;

                // Separar RAE: "228115001 - DESCRIBIR LA ARQUITECTURA"
                [$codR, $descR] = array_map('trim', explode("-", $rae, 2));

                // Crear RAE si no existe
                $conn->prepare("INSERT IGNORE INTO raes (codigo, descripcion, id_competencia, estado) VALUES (?, ?, ?, 1)")
                     ->execute([$codR, $descR, $idComp]);

                $insertadasRae++;
            }

            echo "✅ Importación finalizada correctamente:<br>
            • Competencias procesadas: $insertadasComp <br>
            • Resultados de aprendizaje procesados: $insertadasRae";

        } catch (Exception $e) {
            echo "❌ Error procesando archivo: " . $e->getMessage();
        }
    }
}


// ============ RUTEO ============ //
$accion = $_GET['accion'] ?? null;
$controller = new EtlController();

if ($accion && method_exists($controller, $accion)) {
    $controller->$accion();
} else {
    echo "❌ Acción no válida";
}
