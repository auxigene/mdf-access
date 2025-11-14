<?php

namespace Database\Seeders\FieldMaintenance;

use Illuminate\Database\Seeder;
use App\Models\FieldMaintenance\FmTenant;
use PhpOffice\PhpSpreadsheet\IOFactory;

class FmTenantSeeder extends Seeder
{
    use FmExcelHelper;

    /**
     * Seed les tenants depuis le fichier Excel
     */
    public function run(): void
    {
        $excelFile = storage_path('app/excel/data/fm-inwi/Parc_Sites_INWI_Version_08-10-2025.xlsx');

        if (!file_exists($excelFile)) {
            $this->command->error("Fichier Excel introuvable: {$excelFile}");
            return;
        }

        $this->command->info('🏢 Import des tenants depuis Excel...');

        $reader = IOFactory::createReaderForFile($excelFile);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($excelFile);

        // Lire la feuille Params_Site_Shared_With_Tenant
        $sheet = $spreadsheet->getSheetByName('Params_Site_Shared_With_Tenant');

        if (!$sheet) {
            $this->command->error('Feuille Params_Site_Shared_With_Tenant introuvable');
            return;
        }

        $highestRow = $sheet->getHighestRow();
        $imported = 0;

        // Commencer à la ligne 2 (ligne 1 = en-têtes)
        for ($row = 2; $row <= $highestRow; $row++) {
            $code = $this->getCellValue($sheet, "B{$row}");
            $name = $this->getCellValue($sheet, "C{$row}");

            if (empty($code)) {
                continue;
            }

            FmTenant::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'status' => 'active',
                ]
            );

            $imported++;
        }

        $this->command->info("✅ {$imported} tenants importés");
    }
}
