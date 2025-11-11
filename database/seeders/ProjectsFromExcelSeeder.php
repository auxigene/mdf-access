<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Imports\ProjectsImport;
use Maatwebsite\Excel\Facades\Excel;

class ProjectsFromExcelSeeder extends Seeder
{
    public function run(): void
    {
        $filePath = storage_path('app/excel/data/04_projects.xlsx');

        if (!file_exists($filePath)) {
            $this->command->error("❌ Fichier non trouvé: {$filePath}");
            return;
        }

        $this->command->info("📥 Import des projets depuis Excel...");

        try {
            Excel::import(new ProjectsImport, $filePath);

            $count = \App\Models\Project::count();
            $this->command->info("✅ Projets importés: {$count}");

        } catch (\Exception $e) {
            $this->command->error("❌ Erreur: " . $e->getMessage());
            throw $e;
        }
    }
}
