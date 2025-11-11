<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TestDataMasterSeeder extends Seeder
{
    /**
     * Master Seeder pour importer toutes les données de test depuis Excel
     *
     * ORDRE D'EXÉCUTION (respecte les dépendances):
     * 1. Users (base)
     * 2. UserRoles (dépend de: Users, Roles)
     * 3. Portfolios & Programs (dépend de: Users)
     * 4. Projects (dépend de: Programs, Users, Organizations)
     * 5. ProjectOrganizations (dépend de: Projects, Organizations)
     * 6. Phases & Tasks (dépend de: Projects, WbsElements, Users, Organizations)
     * 7. WBS & Deliverables (dépend de: Projects, Organizations, Users)
     * 8. Risks & Issues (dépend de: Projects, Users)
     * 9. Milestones & ChangeRequests (dépend de: Projects, Users)
     * 10. Resources & ResourceAllocations (dépend de: Users, Projects, Tasks)
     */
    public function run(): void
    {
        $this->command->info("╔═══════════════════════════════════════════════════════════════╗");
        $this->command->info("║  🚀 IMPORT DES DONNÉES DE TEST DEPUIS EXCEL                  ║");
        $this->command->info("╚═══════════════════════════════════════════════════════════════╝");
        $this->command->newLine();

        // Afficher les données existantes
        $this->displayCurrentData();
        $this->command->newLine();

        $startTime = microtime(true);

        try {
            // 1. IMPORT UTILISATEURS
            $this->command->info("🔹 [1/11] Import Utilisateurs...");
            $this->call(UsersFromExcelSeeder::class);
            $this->command->newLine();

            // 2. IMPORT RÔLES UTILISATEURS
            $this->command->info("🔹 [2/11] Import Rôles Utilisateurs...");
            $this->call(UserRolesFromExcelSeeder::class);
            $this->command->newLine();

            // 3. IMPORT PORTFOLIOS & PROGRAMMES
            $this->command->info("🔹 [3/11] Import Portfolios & Programmes...");
            $this->call(PortfoliosProgramsFromExcelSeeder::class);
            $this->command->newLine();

            // 4. IMPORT PROJETS
            $this->command->info("🔹 [4/11] Import Projets...");
            $this->call(ProjectsFromExcelSeeder::class);
            $this->command->newLine();

            // 5. IMPORT PARTICIPATIONS ORGANISATIONS
            $this->command->info("🔹 [5/11] Import Participations Organisations...");
            $this->call(ProjectOrganizationsFromExcelSeeder::class);
            $this->command->newLine();

            // 6. IMPORT PHASES & TÂCHES
            $this->command->info("🔹 [6/11] Import Phases & Tâches...");
            $this->call(PhasesTasksFromExcelSeeder::class);
            $this->command->newLine();

            // 7. IMPORT WBS & LIVRABLES
            $this->command->info("🔹 [7/11] Import WBS & Livrables...");
            $this->call(WbsDeliverablesFromExcelSeeder::class);
            $this->command->newLine();

            // 8. IMPORT RISQUES & PROBLÈMES
            $this->command->info("🔹 [8/11] Import Risques & Problèmes...");
            $this->call(RisksIssuesFromExcelSeeder::class);
            $this->command->newLine();

            // 9. IMPORT JALONS & DEMANDES DE CHANGEMENT
            $this->command->info("🔹 [9/11] Import Jalons & Demandes de Changement...");
            $this->call(MilestonesChangeRequestsFromExcelSeeder::class);
            $this->command->newLine();

            // 10. IMPORT RESSOURCES & ALLOCATIONS
            $this->command->info("🔹 [10/11] Import Ressources & Allocations...");
            $this->call(ResourcesFromExcelSeeder::class);
            $this->command->newLine();

            $executionTime = round(microtime(true) - $startTime, 2);

            // AFFICHER LE RÉSUMÉ FINAL
            $this->displayFinalSummary($executionTime);

        } catch (\Exception $e) {
            $this->command->newLine();
            $this->command->error("╔═══════════════════════════════════════════════════════════════╗");
            $this->command->error("║  ❌ ÉCHEC DE L'IMPORT                                        ║");
            $this->command->error("╚═══════════════════════════════════════════════════════════════╝");
            $this->command->error("Erreur: " . $e->getMessage());
            $this->command->newLine();
            $this->command->warn("💡 Vérifiez que tous les fichiers Excel sont présents dans:");
            $this->command->warn("   storage/app/excel/data/");
            $this->command->newLine();
            throw $e;
        }
    }

    /**
     * Afficher l'état actuel des données
     */
    private function displayCurrentData(): void
    {
        $this->command->info("📊 État actuel de la base de données:");
        $this->command->table(
            ['Table', 'Nombre d\'enregistrements'],
            [
                ['Organizations', \App\Models\Organization::count()],
                ['Users', \App\Models\User::count()],
                ['Roles', \App\Models\Role::count()],
                ['Permissions', \App\Models\Permission::count()],
                ['Portfolios', \App\Models\Portfolio::count()],
                ['Programs', \App\Models\Program::count()],
                ['Projects', \App\Models\Project::count()],
            ]
        );
    }

    /**
     * Afficher le résumé final après import
     */
    private function displayFinalSummary(float $executionTime): void
    {
        $this->command->newLine();
        $this->command->info("╔═══════════════════════════════════════════════════════════════╗");
        $this->command->info("║  ✅ IMPORT TERMINÉ AVEC SUCCÈS                               ║");
        $this->command->info("╚═══════════════════════════════════════════════════════════════╝");
        $this->command->newLine();

        $this->command->info("📊 Résumé des données importées:");
        $this->command->newLine();

        // Données de base
        $this->command->info("🔷 DONNÉES DE BASE:");
        $this->command->table(
            ['Entité', 'Nombre'],
            [
                ['Utilisateurs', \App\Models\User::count()],
                ['Rôles utilisateurs', \App\Models\UserRole::count()],
                ['Organisations', \App\Models\Organization::count()],
            ]
        );

        // Structure hiérarchique
        $this->command->info("🔷 STRUCTURE HIÉRARCHIQUE:");
        $this->command->table(
            ['Entité', 'Nombre'],
            [
                ['Portfolios', \App\Models\Portfolio::count()],
                ['Programmes', \App\Models\Program::count()],
                ['Projets', \App\Models\Project::count()],
                ['Participations Organisations', \App\Models\ProjectOrganization::count()],
            ]
        );

        // Gestion de projet
        $this->command->info("🔷 GESTION DE PROJET:");
        $this->command->table(
            ['Entité', 'Nombre'],
            [
                ['Phases', \App\Models\Phase::count()],
                ['Tâches', \App\Models\Task::count()],
                ['Éléments WBS', \App\Models\WbsElement::count()],
                ['Livrables', \App\Models\Deliverable::count()],
                ['Jalons', \App\Models\Milestone::count()],
            ]
        );

        // Risques et changements
        $this->command->info("🔷 RISQUES & CHANGEMENTS:");
        $this->command->table(
            ['Entité', 'Nombre'],
            [
                ['Risques', \App\Models\Risk::count()],
                ['Problèmes', \App\Models\Issue::count()],
                ['Demandes de changement', \App\Models\ChangeRequest::count()],
            ]
        );

        // Ressources
        $this->command->info("🔷 RESSOURCES:");
        $this->command->table(
            ['Entité', 'Nombre'],
            [
                ['Ressources', \App\Models\Resource::count()],
                ['Allocations', \App\Models\ResourceAllocation::count()],
            ]
        );

        $this->command->newLine();
        $this->command->info("⏱️  Temps d'exécution: {$executionTime} secondes");
        $this->command->newLine();
        $this->command->info("✨ Vous pouvez maintenant tester vos modèles avec des données réelles!");
        $this->command->newLine();
    }
}
