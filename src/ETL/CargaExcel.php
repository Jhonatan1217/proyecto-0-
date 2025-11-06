<?php
class CargaExcel {

    public static function guardar($db, $info) {

        require_once "../app/Models/Programa.php";
        require_once "../app/Models/Competencia.php";
        require_once "../app/Models/Rae.php";

        $ProgramaM = new Programa($db);
        $CompetenciaM = new Competencia($db);
        $RaeM = new Rae($db);

        // 1. Crear (o validar) el programa
        $ProgramaM->crear($info['programa'], "Importado desde Excel", 24);
        $id_programa = $db->lastInsertId();

        // 2. Diccionario para evitar duplicados
        $competenciasRegistradas = [];

        // 3. Recorrer filas procesadas
        foreach ($info['registros'] as $fila) {

            $nombreComp = trim($fila['competencia']);
            $descripcionRae = trim($fila['rae']);

            // Si la competencia aún no ha sido registrada
            if (!isset($competenciasRegistradas[$nombreComp])) {

                // Crear competencia
                $CompetenciaM->crear($nombreComp, "Importada automáticamente");
                $competenciasRegistradas[$nombreComp] = $db->lastInsertId();
            }

            // Obtener el ID real de esa competencia
            $id_competencia = $competenciasRegistradas[$nombreComp];

            // Crear el RAE ligado a la competencia
            $RaeM->crear($descripcionRae, $id_competencia);
        }

        return ["mensaje" => "✅ Importación completada correctamente."];
    }
}
