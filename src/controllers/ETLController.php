<?php
ini_set("display_errors", 0);
error_reporting(0);

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Competencia.php';
require_once __DIR__ . '/../models/Rae.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

class EtlController {

    public function subir() {

        header('Content-Type: application/json');

        if (!isset($_FILES['archivo'])) {
            echo json_encode([
                'success' => false,
                'error' => 'No se recibió archivo.'
            ]);
            exit;
        }

        $programa = $_POST['programa'] ?? null;

        if (!$programa) {
            echo json_encode([
                'success' => false,
                'error' => 'Debe seleccionar un programa.'
            ]);
            exit;
        }

        global $conn;

        if (!$conn) {
            echo json_encode([
                'success' => false,
                'error' => 'Error de conexión a la base de datos.'
            ]);
            exit;
        }

        $file = $_FILES['archivo']['tmp_name'];

        try {

            $spreadsheet = IOFactory::load($file);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            $insertadasComp = 0;
            $insertadasRae = 0;

            for ($i = 13; $i < count($rows); $i++) {

                $comp = trim($rows[$i][5] ?? "");
                $rae  = trim($rows[$i][6] ?? "");

                if ($comp === "" || $rae === "") continue;

                if (!str_contains($comp, "-") || !str_contains($rae, "-")) continue;

                // Separar competencia
                [$codC, $nomC] = array_map('trim', explode("-", $comp, 2));

                $stmt = $conn->prepare("
                    INSERT IGNORE INTO competencias 
                    (id_competencia, id_programa, nombre_competencia, estado)
                    VALUES (?, ?, ?, 1)
                ");
                $stmt->execute([$codC, $programa, $nomC]);

                if ($stmt->rowCount() > 0) {
                    $insertadasComp++;
                }

                // Separar RAE
                [$codR, $descR] = array_map('trim', explode("-", $rae, 2));

                $stmt2 = $conn->prepare("
                    INSERT IGNORE INTO raes 
                    (id_rae, descripcion, id_competencia, estado)
                    VALUES (?, ?, ?, 1)
                ");
                $stmt2->execute([$codR, $descR, $codC]);

                if ($stmt2->rowCount() > 0) {
                    $insertadasRae++;
                }
            }

            echo json_encode([
                'success' => true,
                'competencias' => $insertadasComp,
                'raes' => $insertadasRae
            ]);
            exit;

        } catch (Exception $e) {

            echo json_encode([
                'success' => false,
                'error' => 'Error procesando archivo: ' . $e->getMessage()
            ]);
            exit;
        }
    }
}

/* ================================
   EJECUCIÓN DEL CONTROLADOR
   ================================ */

$accion = $_GET['accion'] ?? null;
$controller = new EtlController();

if ($accion && method_exists($controller, $accion)) {
    $controller->$accion();
} else {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Acción no válida'
    ]);
    exit;
}