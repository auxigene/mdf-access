<?php

/**
 * Script d'analyse du fichier Excel du parc sites INWI
 * Génère un rapport détaillé de la structure du fichier
 */

// Augmenter la limite de mémoire
ini_set('memory_limit', '512M');

require __DIR__.'/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

$excelFile = __DIR__.'/storage/app/excel/data/fm-inwi/Parc_Sites_INWI_Version_08-10-2025.xlsx';

if (!file_exists($excelFile)) {
    echo "❌ Fichier Excel introuvable: {$excelFile}\n";
    exit(1);
}

echo "📊 Analyse du fichier Excel: Parc_Sites_INWI_Version_08-10-2025.xlsx\n";
echo "===========================================\n\n";

try {
    // Charger en mode lecture seule pour économiser la mémoire
    $reader = IOFactory::createReaderForFile($excelFile);
    $reader->setReadDataOnly(true);
    $spreadsheet = $reader->load($excelFile);
    $report = [];

    echo "📋 Nombre de feuilles: " . $spreadsheet->getSheetCount() . "\n\n";

    // Analyser chaque feuille
    foreach ($spreadsheet->getAllSheets() as $index => $sheet) {
        $sheetName = $sheet->getTitle();
        echo "🔍 Analyse de la feuille: {$sheetName}\n";
        echo str_repeat('-', 50) . "\n";

        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

        echo "  Lignes: {$highestRow}\n";
        echo "  Colonnes: {$highestColumn} ({$highestColumnIndex} colonnes)\n\n";

        // Lire les en-têtes (ligne 1)
        $headers = [];
        for ($col = 1; $col <= $highestColumnIndex; $col++) {
            $cellValue = $sheet->getCellByColumnAndRow($col, 1)->getValue();
            $headers[$col] = $cellValue;
        }

        echo "  📌 En-têtes des colonnes:\n";
        foreach ($headers as $colIndex => $header) {
            $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
            echo "    {$columnLetter}: {$header}\n";
        }
        echo "\n";

        // Échantillon de données (5 premières lignes de données après l'en-tête)
        echo "  📝 Échantillon de données (5 premières lignes):\n";
        $sampleRows = min(6, $highestRow); // Ligne 1 = en-tête, donc on va jusqu'à 6 pour avoir 5 lignes de données

        for ($row = 2; $row <= $sampleRows; $row++) {
            echo "    Ligne {$row}:\n";
            for ($col = 1; $col <= min(10, $highestColumnIndex); $col++) { // Limiter à 10 colonnes pour la lisibilité
                $cellValue = $sheet->getCellByColumnAndRow($col, $row)->getValue();
                $header = $headers[$col] ?? "Col{$col}";
                $displayValue = $cellValue === null ? '[NULL]' : (string)$cellValue;
                if (strlen($displayValue) > 50) {
                    $displayValue = substr($displayValue, 0, 47) . '...';
                }
                echo "      {$header}: {$displayValue}\n";
            }
            if ($highestColumnIndex > 10) {
                echo "      ... (" . ($highestColumnIndex - 10) . " colonnes supplémentaires)\n";
            }
            echo "\n";
        }

        // Statistiques sur les valeurs nulles
        echo "  📊 Statistiques des colonnes:\n";
        foreach ($headers as $colIndex => $header) {
            $nullCount = 0;
            $nonNullCount = 0;
            $uniqueValues = [];

            for ($row = 2; $row <= min(100, $highestRow); $row++) { // Analyser les 100 premières lignes
                $cellValue = $sheet->getCellByColumnAndRow($colIndex, $row)->getValue();
                if ($cellValue === null || $cellValue === '') {
                    $nullCount++;
                } else {
                    $nonNullCount++;
                    if (count($uniqueValues) < 20) { // Limiter à 20 valeurs uniques
                        $uniqueValues[] = $cellValue;
                    }
                }
            }

            $totalAnalyzed = $nullCount + $nonNullCount;
            $nullPercentage = $totalAnalyzed > 0 ? round(($nullCount / $totalAnalyzed) * 100, 1) : 0;
            $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);

            echo "    {$columnLetter} - {$header}:\n";
            echo "      Analysées: {$totalAnalyzed} lignes\n";
            echo "      Vides: {$nullCount} ({$nullPercentage}%)\n";
            echo "      Remplies: {$nonNullCount}\n";

            // Afficher quelques valeurs uniques si peu nombreuses
            $uniqueValues = array_unique($uniqueValues);
            if (count($uniqueValues) <= 10 && count($uniqueValues) > 0) {
                echo "      Valeurs uniques: " . implode(', ', array_slice($uniqueValues, 0, 10)) . "\n";
            } elseif (count($uniqueValues) > 10) {
                echo "      Valeurs uniques: " . count($uniqueValues) . "+ valeurs différentes\n";
            }
            echo "\n";
        }

        echo "\n" . str_repeat('=', 80) . "\n\n";

        $report[$sheetName] = [
            'rows' => $highestRow,
            'columns' => $highestColumnIndex,
            'headers' => $headers,
        ];
    }

    // Générer un fichier markdown avec le rapport
    $markdownReport = "# Analyse du Fichier Excel - Parc Sites INWI\n\n";
    $markdownReport .= "**Fichier:** Parc_Sites_INWI_Version_08-10-2025.xlsx\n";
    $markdownReport .= "**Date d'analyse:** " . date('Y-m-d H:i:s') . "\n\n";
    $markdownReport .= "---\n\n";

    $markdownReport .= "## Vue d'ensemble\n\n";
    $markdownReport .= "- **Nombre de feuilles:** " . count($report) . "\n";
    $markdownReport .= "- **Feuilles:**\n";

    foreach ($report as $sheetName => $info) {
        $markdownReport .= "  - `{$sheetName}` : {$info['rows']} lignes × {$info['columns']} colonnes\n";
    }

    $markdownReport .= "\n---\n\n";

    foreach ($report as $sheetName => $info) {
        $markdownReport .= "## Feuille: `{$sheetName}`\n\n";
        $markdownReport .= "- **Lignes:** {$info['rows']}\n";
        $markdownReport .= "- **Colonnes:** {$info['columns']}\n\n";

        $markdownReport .= "### Colonnes\n\n";
        $markdownReport .= "| # | Colonne | Nom de la colonne |\n";
        $markdownReport .= "|---|---------|-------------------|\n";

        foreach ($info['headers'] as $colIndex => $header) {
            $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
            $markdownReport .= "| {$colIndex} | {$columnLetter} | {$header} |\n";
        }

        $markdownReport .= "\n---\n\n";
    }

    // Sauvegarder le rapport
    $reportFile = __DIR__.'/docs/field_maintenance/FM_EXCEL_ANALYSIS.md';
    $reportDir = dirname($reportFile);

    if (!is_dir($reportDir)) {
        mkdir($reportDir, 0755, true);
    }

    file_put_contents($reportFile, $markdownReport);

    echo "✅ Analyse terminée avec succès!\n";
    echo "📄 Rapport sauvegardé dans: docs/field_maintenance/FM_EXCEL_ANALYSIS.md\n";

} catch (Exception $e) {
    echo "❌ Erreur lors de l'analyse: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
