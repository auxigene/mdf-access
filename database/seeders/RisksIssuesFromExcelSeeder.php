<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Imports\RisksImport;
use App\Imports\IssuesImport;
use Maatwebsite\Excel\Facades\Excel;

class RisksIssuesFromExcelSeeder extends Seeder
{
    public function run(): void
    {
        $filePath = storage_path('app/excel/data/09_risks_issues.xlsx');

        if (!file_exists($filePath)) {
            $this->command->error("❌ Fichier non trouvé: {$filePath}");
            return;
        }

        // Import Feuille 1: Risks
        $this->command->info("📥 Import des risques...");
        try {
            Excel::import(new RisksImport, $filePath);
            $risksCount = \App\Models\Risk::count();
            $this->command->info("✅ Risques importés: {$risksCount}");
        } catch (\Exception $e) {
            $this->command->error("❌ Erreur Risques: " . $e->getMessage());
            throw $e;
        }

        // Import Feuille 2: Issues
        $this->command->info("📥 Import des problèmes...");
        try {
            Excel::selectSheets('Issues')->import(new IssuesImport, $filePath);
            $issuesCount = \App\Models\Issue::count();
            $this->command->info("✅ Problèmes importés: {$issuesCount}");
        } catch (\Exception $e) {
            $this->command->error("❌ Erreur Problèmes: " . $e->getMessage());
            throw $e;
        }
    }
}
