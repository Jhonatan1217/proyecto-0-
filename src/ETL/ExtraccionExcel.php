<?php
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExtraccionExcel {

    public static function leer($rutaArchivo) {
        try {

            // Cargar el archivo Excel
            $documento = IOFactory::load($rutaArchivo);

            // Tomar la hoja activa
            $hoja = $documento->getActiveSheet();

            // Convertir la hoja en un arreglo fila x fila
            $data = $hoja->toArray(null, true, true, true);

            // Convertimos el arreglo para usar índices numéricos
            $limpio = [];
            foreach ($data as $fila) {
                $limpio[] = array_values($fila);
            }

            return $limpio;

        } catch (Exception $e) {
            return ["error" => "Error al leer el archivo: " . $e->getMessage()];
        }
    }
}
