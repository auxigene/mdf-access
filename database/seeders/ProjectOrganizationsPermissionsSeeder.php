<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectOrganizationsPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info("Ajout des permissions pour project_organizations...");

        // Nouvelles permissions pour la gestion des organisations dans les projets
        $permissions = [
            // Project Organizations (Rôles des organisations dans les projets)
            ['resource' => 'project_organizations', 'action' => 'view', 'name' => 'Voir les organisations d\'un projet', 'slug' => 'view_project_organizations'],
            ['resource' => 'project_organizations', 'action' => 'create', 'name' => 'Ajouter des organisations à un projet', 'slug' => 'create_project_organizations'],
            ['resource' => 'project_organizations', 'action' => 'edit', 'name' => 'Modifier les organisations d\'un projet', 'slug' => 'edit_project_organizations'],
            ['resource' => 'project_organizations', 'action' => 'delete', 'name' => 'Retirer des organisations d\'un projet', 'slug' => 'delete_project_organizations'],
        ];

        $created = 0;
        $skipped = 0;

        DB::beginTransaction();

        try {
            foreach ($permissions as $permission) {
                // Vérifier si la permission existe déjà
                $exists = DB::table('permissions')
                    ->where('slug', $permission['slug'])
                    ->exists();

                if ($exists) {
                    $this->command->warn("⊘ {$permission['name']} existe déjà, ignorée.");
                    $skipped++;
                    continue;
                }

                DB::table('permissions')->insert([
                    'name' => $permission['name'],
                    'slug' => $permission['slug'],
                    'description' => $permission['name'],
                    'resource' => $permission['resource'],
                    'action' => $permission['action'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $created++;
                $this->command->info("✓ {$permission['name']} créée.");
            }

            DB::commit();

            $this->command->info("========================================");
            $this->command->info("Permissions project_organizations ajoutées !");
            $this->command->info("✓ Permissions créées: {$created}");
            $this->command->warn("⊘ Permissions ignorées: {$skipped}");
            $this->command->info("========================================");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("Erreur lors de l'ajout des permissions: " . $e->getMessage());
        }
    }
}
