<?php

namespace Database\Seeders\FieldMaintenance;

use Illuminate\Database\Seeder;
use App\Models\FieldMaintenance\FmEnergySource;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;

class FmEnergySourceSeeder extends Seeder
{
    use FmExcelHelper;

    /**
     * Seed les sources d'énergie depuis le fichier Excel
     */
    public function run(): void
    {
        $excelFile = storage_path('app/excel/data/fm-inwi/Parc_Sites_INWI_Version_08-10-2025.xlsx');

        if (!file_exists($excelFile)) {
            $this->command->error("Fichier Excel introuvable: {$excelFile}");
            return;
        }

        $this->command->info('⚡ Import des sources d\'énergie depuis Excel...');

        $reader = IOFactory::createReaderForFile($excelFile);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($excelFile);

        // Lire la feuille Params_SourceEnergie
        $sheet = $spreadsheet->getSheetByName('Params_SourceEnergie');

        if (!$sheet) {
            $this->command->error('Feuille Params_SourceEnergie introuvable');
            return;
        }

        $highestRow = $sheet->getHighestRow();
        $imported = 0;

        // Commencer à la ligne 2 (ligne 1 = en-têtes)
        for ($row = 2; $row <= $highestRow; $row++) {
            $reference = $this->getCellValue($sheet, "A{$row}");
            $code = $this->getCellValue($sheet, "B{$row}");
            $sourceEnergie = $this->getCellValue($sheet, "C{$row}");

            if (empty($code)) {
                continue;
            }

            FmEnergySource::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $sourceEnergie,
                    'status' => 'active',
                ]
            );

            // Créer le mapping entre la référence Excel et le code
            if (!empty($reference)) {
                DB::table('fm_references_mapping')->updateOrInsert(
                    [
                        'table_name' => 'fm_energy_sources',
                        'excel_reference' => $reference,
                    ],
                    [
                        'code' => $code,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }

            $imported++;
        }

        $this->command->info("✅ {$imported} sources d'énergie importées");
    }
}
