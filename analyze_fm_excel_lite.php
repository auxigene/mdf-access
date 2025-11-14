<?php

/**
 * Script d'analyse légère du fichier Excel du parc sites INWI
 * Se concentre sur les colonnes non-vides uniquement
 */

ini_set('memory_limit', '1G');

require __DIR__.'/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$excelFile = __DIR__.'/storage/app/excel/data/fm-inwi/Parc_Sites_INWI_Version_08-10-2025.xlsx';

if (!file_exists($excelFile)) {
    echo "❌ Fichier Excel introuvable: {$excelFile}\n";
    exit(1);
}

echo "📊 Analyse du fichier Excel: Parc_Sites_INWI_Version_08-10-2025.xlsx\n";
echo "===========================================\n\n";

try {
    $reader = IOFactory::createReaderForFile($excelFile);
    $reader->setReadDataOnly(true);
    $spreadsheet = $reader->load($excelFile);

    echo "📋 Nombre de feuilles: " . $spreadsheet->getSheetCount() . "\n\n";

    $allSheetsReport = [];

    foreach ($spreadsheet->getAllSheets() as $index => $sheet) {
        $sheetName = $sheet->getTitle();
        echo "🔍 Feuille: {$sheetName}\n";
        echo str_repeat('-', 50) . "\n";

        $highestRow = $sheet->getHighestRow();
        echo "  Lignes totales: {$highestRow}\n";

        // Identifier les colonnes non vides (colonnes avec en-tête)
        $nonEmptyColumns = [];
        $col = 1;
        $maxColToCheck = 100; // On va vérifier les 100 premières colonnes

        while ($col <= $maxColToCheck) {
            $headerValue = $sheet->getCellByColumnAndRow($col, 1)->getValue();
            if ($headerValue !== null && trim($headerValue) !== '') {
                $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $nonEmptyColumns[$col] = [
                    'letter' => $columnLetter,
                    'header' => $headerValue,
                    'index' => $col
                ];
            }
            $col++;
        }

        echo "  Colonnes non vides détectées: " . count($nonEmptyColumns) . "\n\n";

        // Afficher les en-têtes
        echo "  📌 Colonnes:\n";
        foreach ($nonEmptyColumns as $colInfo) {
            echo "    {$colInfo['letter']}: {$colInfo['header']}\n";
        }
        echo "\n";

        // Échantillon de données (3 lignes)
        $sampleSize = min(4, $highestRow); // 3 lignes de données + 1 en-tête
        echo "  📝 Échantillon de données (3 premières lignes):\n\n";

        for ($row = 2; $row <= $sampleSize; $row++) {
            echo "    === Ligne {$row} ===\n";
            foreach ($nonEmptyColumns as $colInfo) {
                $cellValue = $sheet->getCellByColumnAndRow($colInfo['index'], $row)->getValue();
                $displayValue = $cellValue === null ? '[vide]' : (string)$cellValue;
                if (strlen($displayValue) > 80) {
                    $displayValue = substr($displayValue, 0, 77) . '...';
                }
                echo "    {$colInfo['letter']} - {$colInfo['header']}: {$displayValue}\n";
            }
            echo "\n";
        }

        // Statistiques rapides
        echo "  📊 Statistiques (100 premières lignes):\n\n";
        $statsRows = min(100, $highestRow);

        foreach ($nonEmptyColumns as $colInfo) {
            $nullCount = 0;
            $nonNullCount = 0;
            $sampleValues = [];

            for ($row = 2; $row <= $statsRows; $row++) {
                $cellValue = $sheet->getCellByColumnAndRow($colInfo['index'], $row)->getValue();
                if ($cellValue === null || trim($cellValue) === '') {
                    $nullCount++;
                } else {
                    $nonNullCount++;
                    if (count($sampleValues) < 5) {
                        $sampleValues[] = $cellValue;
                    }
                }
            }

            $totalAnalyzed = $nullCount + $nonNullCount;
            $fillPercentage = $totalAnalyzed > 0 ? round(($nonNullCount / $totalAnalyzed) * 100, 1) : 0;

            echo "    {$colInfo['letter']} - {$colInfo['header']}:\n";
            echo "      Taux de remplissage: {$fillPercentage}% ({$nonNullCount}/{$totalAnalyzed})\n";
            if (!empty($sampleValues)) {
                echo "      Exemples: " . implode(', ', array_slice($sampleValues, 0, 3)) . "\n";
            }
            echo "\n";
        }

        $allSheetsReport[$sheetName] = [
            'rows' => $highestRow,
            'columns' => $nonEmptyColumns
        ];

        echo "\n" . str_repeat('=', 80) . "\n\n";
    }

    // Générer rapport markdown
    $markdown = "# Analyse du Parc Sites INWI - Excel\n\n";
    $markdown .= "**Date:** " . date('Y-m-d H:i:s') . "\n\n";
    $markdown .= "---\n\n";

    $markdown .= "## Vue d'ensemble\n\n";
    $markdown .= "- **Feuilles:** " . count($allSheetsReport) . "\n\n";

    foreach ($allSheetsReport as $sheetName => $info) {
        $markdown .= "### Feuille: `{$sheetName}`\n\n";
        $markdown .= "- **Lignes de données:** " . ($info['rows'] - 1) . " (+ 1 ligne d'en-tête)\n";
        $markdown .= "- **Colonnes utiles:** " . count($info['columns']) . "\n\n";

        $markdown .= "#### Colonnes\n\n";
        $markdown .= "| # | Col | Nom | Description |\n";
        $markdown .= "|---|-----|-----|-------------|\n";

        foreach ($info['columns'] as $colInfo) {
            $markdown .= "| {$colInfo['index']} | {$colInfo['letter']} | {$colInfo['header']} | |\n";
        }

        $markdown .= "\n";
    }

    // Suggestions de structure de base de données
    $markdown .= "## Suggestions de Structure de Base de Données\n\n";
    $markdown .= "Basé sur la feuille principale `PARC_SITES_INWI`:\n\n";

    if (isset($allSheetsReport['PARC_SITES_INWI'])) {
        $mainColumns = $allSheetsReport['PARC_SITES_INWI']['columns'];

        $markdown .= "### Table principale: `fm_sites`\n\n";
        $markdown .= "```sql\n";
        $markdown .= "CREATE TABLE fm_sites (\n";
        $markdown .= "    id SERIAL PRIMARY KEY,\n";

        foreach ($mainColumns as $colInfo) {
            $fieldName = strtolower(str_replace(' ', '_', $colInfo['header']));
            $fieldName = preg_replace('/[^a-z0-9_]/', '', $fieldName);

            // Déterminer le type de colonne
            $sqlType = "VARCHAR(255)";
            if (strpos(strtolower($colInfo['header']), 'id') !== false) {
                $sqlType = "VARCHAR(50)";
            }

            $markdown .= "    {$fieldName} {$sqlType},\n";
        }

        $markdown .= "    status VARCHAR(20) DEFAULT 'active',\n";
        $markdown .= "    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n";
        $markdown .= "    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n";
        $markdown .= "    deleted_at TIMESTAMP\n";
        $markdown .= ");\n";
        $markdown .= "```\n\n";
    }

    // Sauvegarder
    $reportDir = __DIR__.'/docs/field_maintenance';
    if (!is_dir($reportDir)) {
        mkdir($reportDir, 0755, true);
    }

    file_put_contents($reportDir.'/FM_EXCEL_ANALYSIS.md', $markdown);

    echo "✅ Analyse terminée!\n";
    echo "📄 Rapport sauvegardé: docs/field_maintenance/FM_EXCEL_ANALYSIS.md\n";

} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    exit(1);
}
