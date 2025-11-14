<?php

namespace Database\Seeders\FieldMaintenance;

trait FmExcelHelper
{
    /**
     * Lire et nettoyer une valeur de cellule Excel
     */
    protected function getCellValue($sheet, string $cell): ?string
    {
        $value = $sheet->getCell($cell)->getValue();

        if ($value === null) {
            return null;
        }

        // Convertir en string et trimmer
        $value = trim((string)$value);

        // Retourner null si la valeur est vide après trim
        return $value === '' ? null : $value;
    }
}
