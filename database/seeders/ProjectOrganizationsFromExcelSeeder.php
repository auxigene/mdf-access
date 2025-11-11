<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Imports\ProjectOrganizationsImport;
use Maatwebsite\Excel\Facades\Excel;

class ProjectOrganizationsFromExcelSeeder extends Seeder
{
    public function run(): void
    {
        $filePath = storage_path('app/excel/data/05_project_organizations.xlsx');

        if (!file_exists($filePath)) {
            $this->command->error("❌ Fichier non trouvé: {$filePath}");
            return;
        }

        $this->command->info("📥 Import des participations organisations...");
        $this->command->warn("⚠️  Vérification des contraintes métier en cours...");

        try {
            Excel::import(new ProjectOrganizationsImport, $filePath);

            $count = \App\Models\ProjectOrganization::count();
            $this->command->info("✅ Participations importées: {$count}");

        } catch (\Exception $e) {
            $this->command->error("❌ Erreur: " . $e->getMessage());
            $this->command->warn("💡 Vérifiez les contraintes: 1 sponsor actif, 1 MOA actif, 1 MOE primaire actif par projet");
            throw $e;
        }
    }
}
