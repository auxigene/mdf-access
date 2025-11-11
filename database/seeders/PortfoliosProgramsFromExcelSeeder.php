<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Imports\PortfoliosImport;
use App\Imports\ProgramsImport;
use Maatwebsite\Excel\Facades\Excel;

class PortfoliosProgramsFromExcelSeeder extends Seeder
{
    /**
     * Importer portfolios et programmes depuis Excel
     */
    public function run(): void
    {
        $filePath = storage_path('app/excel/data/03_portfolios_programs.xlsx');

        if (!file_exists($filePath)) {
            $this->command->error("❌ Fichier non trouvé: {$filePath}");
            $this->command->warn("💡 Veuillez créer et remplir le fichier Excel d'abord.");
            return;
        }

        $this->command->info("📥 Import des portfolios et programmes depuis Excel...");

        try {
            // Import Feuille 1: Portfolios
            $this->command->info("  → Import des portfolios...");
            Excel::import(new PortfoliosImport, $filePath, null, \Maatwebsite\Excel\Excel::XLSX);

            $portfoliosCount = \App\Models\Portfolio::count();
            $this->command->info("  ✓ Portfolios importés: {$portfoliosCount}");

            // Import Feuille 2: Programs (utiliser le nom de la feuille)
            $this->command->info("  → Import des programmes...");
            Excel::selectSheets('Programs')->import(new ProgramsImport, $filePath);

            $programsCount = \App\Models\Program::count();
            $this->command->info("  ✓ Programmes importés: {$programsCount}");

            $this->command->info("✅ Import terminé avec succès!");

        } catch (\Exception $e) {
            $this->command->error("❌ Erreur lors de l'import: " . $e->getMessage());
            throw $e;
        }
    }
}
