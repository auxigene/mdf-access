<?php

namespace Database\Seeders\FieldMaintenance;

use Illuminate\Database\Seeder;
use App\Models\FieldMaintenance\FmMaintenanceTypology;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;

class FmMaintenanceTypologySeeder extends Seeder
{
    use FmExcelHelper;

    /**
     * Seed les typologies de maintenance depuis le fichier Excel
     */
    public function run(): void
    {
        $excelFile = storage_path('app/excel/data/fm-inwi/Parc_Sites_INWI_Version_08-10-2025.xlsx');

        if (!file_exists($excelFile)) {
            $this->command->error("Fichier Excel introuvable: {$excelFile}");
            return;
        }

        $this->command->info('🔧 Import des typologies de maintenance depuis Excel...');

        $reader = IOFactory::createReaderForFile($excelFile);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($excelFile);

        // Lire la feuille Params_Typologie_Maintenance
        $sheet = $spreadsheet->getSheetByName('Params_Typologie_Maintenance');

        if (!$sheet) {
            $this->command->error('Feuille Params_Typologie_Maintenance introuvable');
            return;
        }

        $highestRow = $sheet->getHighestRow();
        $imported = 0;

        // Commencer à la ligne 2 (ligne 1 = en-têtes)
        for ($row = 2; $row <= $highestRow; $row++) {
            $reference = $this->getCellValue($sheet, "A{$row}");
            $code = $this->getCellValue($sheet, "B{$row}");
            $typologie = $this->getCellValue($sheet, "C{$row}");

            if (empty($code)) {
                continue;
            }

            FmMaintenanceTypology::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $typologie,
                    'status' => 'active',
                ]
            );

            // Créer le mapping entre la référence Excel et le code
            if (!empty($reference)) {
                DB::table('fm_references_mapping')->updateOrInsert(
                    [
                        'table_name' => 'fm_maintenance_typologies',
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

        $this->command->info("✅ {$imported} typologies de maintenance importées");
    }
}
