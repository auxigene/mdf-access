<?php

namespace Database\Seeders\FieldMaintenance;

use Illuminate\Database\Seeder;
use App\Models\FieldMaintenance\FmSiteClass;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;

class FmSiteClassSeeder extends Seeder
{
    use FmExcelHelper;

    /**
     * Seed les classes de sites depuis le fichier Excel
     */
    public function run(): void
    {
        $excelFile = storage_path('app/excel/data/fm-inwi/Parc_Sites_INWI_Version_08-10-2025.xlsx');

        if (!file_exists($excelFile)) {
            $this->command->error("Fichier Excel introuvable: {$excelFile}");
            return;
        }

        $this->command->info('🏷️  Import des classes de sites depuis Excel...');

        $reader = IOFactory::createReaderForFile($excelFile);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($excelFile);

        // Lire la feuille Params_Classifications
        $sheet = $spreadsheet->getSheetByName('Params_Classifications');

        if (!$sheet) {
            $this->command->error('Feuille Params_Classifications introuvable');
            return;
        }

        $highestRow = $sheet->getHighestRow();
        $imported = 0;

        // Définir les priorités (A=5, B=4, C=3, D=2, E=1)
        $priorities = ['A' => 5, 'B' => 4, 'C' => 3, 'D' => 2, 'E' => 1];

        // Commencer à la ligne 2 (ligne 1 = en-têtes)
        for ($row = 2; $row <= $highestRow; $row++) {
            $reference = $this->getCellValue($sheet, "A{$row}");
            $code = $this->getCellValue($sheet, "B{$row}");
            $classe = $this->getCellValue($sheet, "C{$row}");

            if (empty($code)) {
                continue;
            }

            FmSiteClass::updateOrCreate(
                ['code' => $code],
                [
                    'name' => "Classe {$classe}",
                    'priority' => $priorities[$code] ?? 0,
                    'status' => 'active',
                ]
            );

            // Créer le mapping entre la référence Excel et le code
            if (!empty($reference)) {
                DB::table('fm_references_mapping')->updateOrInsert(
                    [
                        'table_name' => 'fm_site_classes',
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

        $this->command->info("✅ {$imported} classes de sites importées");
    }
}
