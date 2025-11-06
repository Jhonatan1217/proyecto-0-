<?php
class TransformacionExcel {

    public static function procesar($data) {

        // 1. Extraer denominación del programa (C6 → fila 5, columna 2)
        $programa = trim($data[5][2] ?? "");

        $registros = [];

        // 2. Leer desde fila 12 (índice 11) hasta el final
        for ($i = 11; $i < count($data); $i++) {

            $fila = $data[$i];

            // Competencia (columna F → index 5)
            $competencia = trim($fila[5] ?? "");

            // RAE (columna G → index 6)
            $rae = trim($fila[6] ?? "");

            // Agregar solo si ambos tienen datos
            if ($competencia !== "" && $rae !== "") {
                $registros[] = [
                    "competencia" => $competencia,
                    "rae" => $rae
                ];
            }
        }

        return [
            'programa' => $programa,
            'registros' => $registros
        ];
    }
}
