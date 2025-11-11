<?php
ini_set("display_errors", 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Competencia.php';
require_once __DIR__ . '/../models/Rae.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

class EtlController {

    public function subir() {

        if (!isset($_FILES['archivo'])) {
            echo " No se recibió archivo.";
            return;
        }

        $programa = $_POST['programa'] ?? null;
        if (!$programa) {
            echo " Debe seleccionar un programa.";
            return;
        }

        global $conn;
        $file = $_FILES['archivo']['tmp_name'];

        try {
            $spreadsheet = IOFactory::load($file);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            $insertadasComp = 0;
            $insertadasRae = 0;

            // Empieza en fila 14
            for ($i = 13; $i < count($rows); $i++) {

                $comp = trim($rows[$i][5] ?? "");
                $rae  = trim($rows[$i][6] ?? "");

                if ($comp === "" || $rae === "") continue;

                // COMPETENCIA: "228115 - APLICAR DISEÑO DE SOFTWARE"
                [$codC, $nomC] = array_map('trim', explode("-", $comp, 2));

                // Insertar competencia (id_competencia = código)
                $stmt = $conn->prepare("
                    INSERT IGNORE INTO competencias (id_competencia, id_programa, descripcion, nombre_competencia, estado)
                    VALUES (?, ?, '', ?, 1)
                ");
                $stmt->execute([$codC, $programa, $nomC]);

                if ($stmt->rowCount() > 0) $insertadasComp++;

                // RAE: "228115001 - DESCRIBIR LA ARQUITECTURA"
                [$codR, $descR] = array_map('trim', explode("-", $rae, 2));

                // Insertar RAE (id_rae = código)
                $stmt2 = $conn->prepare("
                    INSERT IGNORE INTO raes (id_rae, descripcion, id_competencia, estado)
                    VALUES (?, ?, ?, 1)
                ");
                $stmt2->execute([$codR, $descR, $codC]);

                if ($stmt2->rowCount() > 0) $insertadasRae++;
            }

            echo "Importación completada:<br>
                  • Competencias procesadas: $insertadasComp <br>
                  • Resultados de aprendizaje procesados: $insertadasRae";

        } catch (Exception $e) {
            echo "Error procesando archivo: " . $e->getMessage();
        }
    }
}

$accion = $_GET['accion'] ?? null;
$controller = new EtlController();

if ($accion && method_exists($controller, $accion)) {
    $controller->$accion();
} else {
    echo "Acción no válida";
}
