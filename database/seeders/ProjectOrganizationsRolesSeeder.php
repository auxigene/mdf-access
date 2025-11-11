<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectOrganizationsRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info("Ajout des permissions project_organizations aux rôles...");

        // Mapping: role_slug => permissions à ajouter
        $rolePermissions = [
            // Super Admin a déjà toutes les permissions automatiquement

            // Rôles SAMSIC internes avec accès complet
            'pmo_director' => [
                'view_project_organizations',
                'create_project_organizations',
                'edit_project_organizations',
                'delete_project_organizations',
            ],
            'pmo_manager' => [
                'view_project_organizations',
                'create_project_organizations',
                'edit_project_organizations',
                'delete_project_organizations',
            ],

            // Rôles de gestion de portefeuille
            'portfolio_director' => [
                'view_project_organizations',
                'create_project_organizations',
                'edit_project_organizations',
                'delete_project_organizations',
            ],

            // Rôles de gestion de programme
            'program_manager' => [
                'view_project_organizations',
                'create_project_organizations',
                'edit_project_organizations',
                'delete_project_organizations',
            ],

            // Rôles de gestion de projet (TRÈS IMPORTANT)
            'project_manager' => [
                'view_project_organizations',
                'create_project_organizations',
                'edit_project_organizations',
                'delete_project_organizations',
            ],
            'project_coordinator' => [
                'view_project_organizations',
                'create_project_organizations',
                'edit_project_organizations',
            ],

            // Rôles métiers avec accès
            'procurement_manager' => [
                'view_project_organizations',
            ],

            // Rôles clients (peuvent voir les organisations)
            'client_admin' => [
                'view_project_organizations',
            ],
            'client_viewer' => [
                'view_project_organizations',
            ],

            // Rôles PMBOK spécialisés
            'project_sponsor' => [
                'view_project_organizations',
            ],
            'business_analyst' => [
                'view_project_organizations',
            ],

            // Gestionnaire des ressources (doit voir et gérer les organisations)
            'resource_manager' => [
                'view_project_organizations',
                'create_project_organizations',
                'edit_project_organizations',
            ],

            // Planificateur (doit voir les organisations pour la planification)
            'planner' => [
                'view_project_organizations',
            ],

            // Contrôleur de gestion (doit voir les organisations pour le suivi)
            'controller' => [
                'view_project_organizations',
            ],

            // CCB (doit voir les organisations pour les change requests)
            'ccb_member' => [
                'view_project_organizations',
            ],

            // Chef d'équipe (peut voir les organisations)
            'team_lead' => [
                'view_project_organizations',
            ],
        ];

        $updated = 0;
        $skipped = 0;
        $errors = 0;

        DB::beginTransaction();

        try {
            foreach ($rolePermissions as $roleSlug => $permissionSlugs) {
                // Récupérer le rôle
                $role = DB::table('roles')
                    ->where('slug', $roleSlug)
                    ->first();

                if (!$role) {
                    $this->command->warn("⊘ Rôle '{$roleSlug}' introuvable, ignoré.");
                    $skipped++;
                    continue;
                }

                $addedCount = 0;

                foreach ($permissionSlugs as $permissionSlug) {
                    // Récupérer la permission
                    $permission = DB::table('permissions')
                        ->where('slug', $permissionSlug)
                        ->first();

                    if (!$permission) {
                        $this->command->error("✗ Permission '{$permissionSlug}' introuvable.");
                        $errors++;
                        continue;
                    }

                    // Vérifier si la relation existe déjà
                    $exists = DB::table('role_permission')
                        ->where('role_id', $role->id)
                        ->where('permission_id', $permission->id)
                        ->exists();

                    if ($exists) {
                        // Déjà existante, on ignore
                        continue;
                    }

                    // Ajouter la permission au rôle
                    DB::table('role_permission')->insert([
                        'role_id' => $role->id,
                        'permission_id' => $permission->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $addedCount++;
                }

                if ($addedCount > 0) {
                    $this->command->info("✓ {$role->name}: {$addedCount} permission(s) ajoutée(s).");
                    $updated++;
                } else {
                    $this->command->warn("⊘ {$role->name}: aucune nouvelle permission (déjà existantes).");
                }
            }

            DB::commit();

            $this->command->info("========================================");
            $this->command->info("Permissions project_organizations ajoutées aux rôles !");
            $this->command->info("✓ Rôles mis à jour: {$updated}");
            $this->command->warn("⊘ Rôles ignorés: {$skipped}");
            if ($errors > 0) {
                $this->command->error("✗ Erreurs: {$errors}");
            }
            $this->command->info("========================================");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("Erreur lors de l'ajout des permissions: " . $e->getMessage());
            $this->command->error("Trace: " . $e->getTraceAsString());
        }
    }
}
