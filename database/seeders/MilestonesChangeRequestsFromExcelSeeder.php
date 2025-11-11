<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Imports\MilestonesImport;
use App\Imports\ChangeRequestsImport;
use Maatwebsite\Excel\Facades\Excel;

class MilestonesChangeRequestsFromExcelSeeder extends Seeder
{
    public function run(): void
    {
        $filePath = storage_path('app/excel/data/10_milestones_change_requests.xlsx');

        if (!file_exists($filePath)) {
            $this->command->error("❌ Fichier non trouvé: {$filePath}");
            return;
        }

        // Import Feuille 1: Milestones
        $this->command->info("📥 Import des jalons...");
        try {
            Excel::import(new MilestonesImport, $filePath);
            $milestonesCount = \App\Models\Milestone::count();
            $this->command->info("✅ Jalons importés: {$milestonesCount}");
        } catch (\Exception $e) {
            $this->command->error("❌ Erreur Jalons: " . $e->getMessage());
            throw $e;
        }

        // Import Feuille 2: Change Requests
        $this->command->info("📥 Import des demandes de changement...");
        try {
            Excel::selectSheets('Change Requests')->import(new ChangeRequestsImport, $filePath);
            $crCount = \App\Models\ChangeRequest::count();
            $this->command->info("✅ Demandes de changement importées: {$crCount}");
        } catch (\Exception $e) {
            $this->command->error("❌ Erreur Demandes: " . $e->getMessage());
            throw $e;
        }
    }
}
