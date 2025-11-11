<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Imports\ResourcesImport;
use App\Imports\ResourceAllocationsImport;
use Maatwebsite\Excel\Facades\Excel;

class ResourcesFromExcelSeeder extends Seeder
{
    public function run(): void
    {
        $filePath = storage_path('app/excel/data/11_resources.xlsx');

        if (!file_exists($filePath)) {
            $this->command->error("❌ Fichier non trouvé: {$filePath}");
            return;
        }

        // Import Feuille 1: Resources
        $this->command->info("📥 Import des ressources...");
        try {
            Excel::import(new ResourcesImport, $filePath);
            $resourcesCount = \App\Models\Resource::count();
            $this->command->info("✅ Ressources importées: {$resourcesCount}");
        } catch (\Exception $e) {
            $this->command->error("❌ Erreur Ressources: " . $e->getMessage());
            throw $e;
        }

        // Import Feuille 2: Resource Allocations
        $this->command->info("📥 Import des allocations de ressources...");
        try {
            Excel::selectSheets('Resource Allocations')->import(new ResourceAllocationsImport, $filePath);
            $allocCount = \App\Models\ResourceAllocation::count();
            $this->command->info("✅ Allocations importées: {$allocCount}");
        } catch (\Exception $e) {
            $this->command->error("❌ Erreur Allocations: " . $e->getMessage());
            throw $e;
        }
    }
}
