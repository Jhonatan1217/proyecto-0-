<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once __DIR__ . '/../../config/database.php';


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Debe usar el método POST para subir el archivo']);
    exit;
}

if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['error' => 'No se recibió ningún archivo o hubo un error al subirlo']);
    exit;
}

$archivo_tmp = $_FILES['archivo']['tmp_name'];
$extension = pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION);

if (strtolower($extension) !== 'xlsx') {
    echo json_encode(['error' => 'El archivo debe ser formato .xlsx']);
    exit;
}



$zip = new ZipArchive();
if ($zip->open($archivo_tmp) !== true) {
    echo json_encode(['error' => 'No se pudo abrir el archivo Excel.']);
    exit;
}

$sharedStringsXML = $zip->getFromName('xl/sharedStrings.xml');
$sheetXML = $zip->getFromName('xl/worksheets/sheet1.xml');
$zip->close();

if (!$sheetXML) {
    echo json_encode(['error' => 'No se encontró la hoja principal (sheet1.xml)']);
    exit;
}



$sharedStrings = [];
if ($sharedStringsXML) {
    $xml = simplexml_load_string($sharedStringsXML);
    foreach ($xml->si as $si) {
        $sharedStrings[] = (string)$si->t;
    }
}


$sheet = simplexml_load_string($sheetXML);
$rows = $sheet->sheetData->row;

$insertadasCompetencias = 0;
$insertadasRaes = 0;
$filaIndex = 0;

foreach ($rows as $row) {
    if ($filaIndex == 0) {
        $filaIndex++;
        continue;
    }

    $cells = $row->c;
    $colIndex = 0;

   
    $codigoCompetencia = '';
    $nombreCompetencia = '';
    $descripcionCompetencia = '';
    $codigoRae = '';
    $descripcionRae = '';

    foreach ($cells as $c) {
        $val = (string)$c->v;
        $t = (string)$c['t'];
        if ($t === 's' && isset($sharedStrings[(int)$val])) {
            $val = $sharedStrings[(int)$val];
        }

        
        if ($colIndex == 0) $codigoCompetencia = trim($val);
        if ($colIndex == 1) $nombreCompetencia = trim($val);
        if ($colIndex == 2) $descripcionCompetencia = trim($val);
        if ($colIndex == 3) $codigoRae = trim($val);
        if ($colIndex == 4) $descripcionRae = trim($val);

        $colIndex++;
    }

    if (empty($codigoCompetencia) || empty($codigoRae)) continue;

    

    
    $stmt = $conn->prepare("SELECT id_competencia FROM competencias WHERE id_competencia = ?");
    $stmt->execute([$codigoCompetencia]);
    $competencia = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$competencia) {
        $insert = $conn->prepare("INSERT INTO competencias (id_competencia, nombre_competencia, descripcion, estado) VALUES (?, ?, ?, 1)");
        $insert->execute([$codigoCompetencia, $nombreCompetencia, $descripcionCompetencia]);
        $insertadasCompetencias++;
    }

    
    $stmt = $conn->prepare("SELECT id_rae FROM raes WHERE id_rae = ?");
    $stmt->execute([$codigoRae]);
    $rae = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$rae) {
        $insertRae = $conn->prepare("INSERT INTO raes (id_rae, descripcion, id_competencia, estado) VALUES (?, ?, ?, 1)");
        $insertRae->execute([$codigoRae, $descripcionRae, $codigoCompetencia]);
        $insertadasRaes++;
    }
}

echo json_encode([
    'success' => true,
    'competencias_insertadas' => $insertadasCompetencias,
    'raes_insertadas' => $insertadasRaes,
]);
?>
