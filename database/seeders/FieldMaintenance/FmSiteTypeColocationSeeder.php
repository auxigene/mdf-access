<?php

namespace Database\Seeders\FieldMaintenance;

use Illuminate\Database\Seeder;
use App\Models\FieldMaintenance\FmSiteTypeColocation;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;

class FmSiteTypeColocationSeeder extends Seeder
{
    use FmExcelHelper;

    /**
     * Seed les configurations de colocation depuis le fichier Excel
     */
    public function run(): void
    {
        $excelFile = storage_path('app/excel/data/fm-inwi/Parc_Sites_INWI_Version_08-10-2025.xlsx');

        if (!file_exists($excelFile)) {
            $this->command->error("Fichier Excel introuvable: {$excelFile}");
            return;
        }

        $this->command->info('🔗 Import des configurations de colocation depuis Excel...');

        $reader = IOFactory::createReaderForFile($excelFile);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($excelFile);

        // Lire la feuille Params_Tenants_Config
        $sheet = $spreadsheet->getSheetByName('Params_Tenants_Config');

        if (!$sheet) {
            $this->command->error('Feuille Params_Tenants_Config introuvable');
            return;
        }

        $highestRow = $sheet->getHighestRow();
        $imported = 0;

        // Commencer à la ligne 2 (ligne 1 = en-têtes)
        for ($row = 2; $row <= $highestRow; $row++) {
            $reference = $this->getCellValue($sheet, "A{$row}");
            $code = $this->getCellValue($sheet, "B{$row}"); // Utiliser la colonne Code
            $tenant1 = $this->getCellValue($sheet, "D{$row}");
            $tenant2 = $this->getCellValue($sheet, "E{$row}");
            $tenant3 = $this->getCellValue($sheet, "F{$row}");
            $tenant4 = $this->getCellValue($sheet, "G{$row}");

            if (empty($code)) {
                continue;
            }

            // Construire la liste des tenants
            $tenants = array_filter([$tenant1, $tenant2, $tenant3, $tenant4]);
            $tenantCount = count($tenants);

            if ($tenantCount === 0) {
                continue;
            }

            FmSiteTypeColocation::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $reference,
                    'tenant_count' => $tenantCount,
                    'tenants' => $tenants,
                    'status' => 'active',
                ]
            );

            // Créer le mapping entre la référence Excel et le code
            if (!empty($reference)) {
                DB::table('fm_references_mapping')->updateOrInsert(
                    [
                        'table_name' => 'fm_site_type_colocations',
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

        $this->command->info("✅ {$imported} configurations de colocation importées");
    }
}
