<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Imports\WbsElementsImport;
use App\Imports\DeliverablesImport;
use Maatwebsite\Excel\Facades\Excel;

class WbsDeliverablesFromExcelSeeder extends Seeder
{
    public function run(): void
    {
        $filePath = storage_path('app/excel/data/08_wbs_deliverables.xlsx');

        if (!file_exists($filePath)) {
            $this->command->error("❌ Fichier non trouvé: {$filePath}");
            return;
        }

        // Import Feuille 1: WBS Elements
        $this->command->info("📥 Import des éléments WBS...");
        try {
            Excel::import(new WbsElementsImport, $filePath);
            $wbsCount = \App\Models\WbsElement::count();
            $this->command->info("✅ Éléments WBS importés: {$wbsCount}");
        } catch (\Exception $e) {
            $this->command->error("❌ Erreur WBS: " . $e->getMessage());
            throw $e;
        }

        // Import Feuille 2: Deliverables
        $this->command->info("📥 Import des livrables...");
        try {
            Excel::selectSheets('Deliverables')->import(new DeliverablesImport, $filePath);
            $delivCount = \App\Models\Deliverable::count();
            $this->command->info("✅ Livrables importés: {$delivCount}");
        } catch (\Exception $e) {
            $this->command->error("❌ Erreur Livrables: " . $e->getMessage());
            throw $e;
        }
    }
}
