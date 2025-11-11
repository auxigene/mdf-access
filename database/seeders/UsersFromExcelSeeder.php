<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Imports\UsersImport;
use Maatwebsite\Excel\Facades\Excel;

class UsersFromExcelSeeder extends Seeder
{
    /**
     * Importer les utilisateurs depuis Excel
     */
    public function run(): void
    {
        $filePath = storage_path('app/excel/data/01_users.xlsx');

        if (!file_exists($filePath)) {
            $this->command->error("❌ Fichier non trouvé: {$filePath}");
            $this->command->warn("💡 Veuillez créer et remplir le fichier Excel d'abord.");
            $this->command->warn("📋 Consultez: docs/EXCEL_TEMPLATES_GUIDE.md");
            return;
        }

        $this->command->info("📥 Import des utilisateurs depuis Excel...");

        try {
            Excel::import(new UsersImport, $filePath);

            $count = \App\Models\User::count();
            $this->command->info("✅ Utilisateurs importés avec succès!");
            $this->command->info("📊 Total utilisateurs: {$count}");

        } catch (\Exception $e) {
            $this->command->error("❌ Erreur lors de l'import: " . $e->getMessage());
            throw $e;
        }
    }
}
