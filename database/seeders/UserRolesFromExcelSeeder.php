<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Imports\UserRolesImport;
use Maatwebsite\Excel\Facades\Excel;

class UserRolesFromExcelSeeder extends Seeder
{
    /**
     * Importer les rôles utilisateurs depuis Excel
     */
    public function run(): void
    {
        $filePath = storage_path('app/excel/data/02_user_roles.xlsx');

        if (!file_exists($filePath)) {
            $this->command->error("❌ Fichier non trouvé: {$filePath}");
            $this->command->warn("💡 Veuillez créer et remplir le fichier Excel d'abord.");
            return;
        }

        $this->command->info("📥 Import des rôles utilisateurs depuis Excel...");

        try {
            Excel::import(new UserRolesImport, $filePath);

            $count = \App\Models\UserRole::count();
            $this->command->info("✅ Rôles utilisateurs importés avec succès!");
            $this->command->info("📊 Total attributions: {$count}");

        } catch (\Exception $e) {
            $this->command->error("❌ Erreur lors de l'import: " . $e->getMessage());
            throw $e;
        }
    }
}
