<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClientMoaRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Ajoute les rôles d'approbation manquants pour :
     * - Client (Sponsor) : Approbations stratégiques
     * - MOA (Maître d'Ouvrage) : Validation qualité et approbation livrables
     */
    public function run(): void
    {
        $this->command->info("Ajout des rôles d'approbation Client et MOA...");

        // Définition des nouveaux rôles
        $roles = [
            // ============================================
            // RÔLES CLIENT (avec approbations)
            // ============================================

            [
                'name' => 'Client Sponsor',
                'slug' => 'client_sponsor',
                'description' => 'Sponsor côté client avec pouvoirs d\'approbation stratégique (budget, projet, livrables, changements)',
                'scope' => 'organization',
                'organization_id' => null,
                'permissions' => [
                    // Projets
                    'view_projects', 'approve_projects',

                    // Budgets
                    'view_budgets', 'approve_budgets',

                    // Livrables
                    'view_deliverables', 'approve_deliverables',

                    // Changements
                    'view_change_requests', 'approve_change_requests',

                    // Consultation
                    'view_tasks', 'view_wbs_elements',
                    'view_risks', 'view_issues',
                    'view_documents', 'view_reports', 'export_reports',
                    'view_project_status_reports', 'view_kpis',
                    'view_milestones', 'view_schedules',
                    'view_stakeholders',

                    // Organisations du projet
                    'view_project_organizations',
                ],
            ],

            // ============================================
            // RÔLES MOA (Maître d'Ouvrage)
            // ============================================

            [
                'name' => 'Responsable MOA',
                'slug' => 'moa_manager',
                'description' => 'Responsable Maître d\'Ouvrage - Maîtrise du scope, validation qualité, approbation livrables et changements',
                'scope' => 'project',
                'organization_id' => null,
                'permissions' => [
                    // Projets et scope
                    'view_projects', 'edit_projects',
                    'view_project_phases', 'edit_project_phases',

                    // WBS et scope
                    'view_wbs_elements', 'create_wbs_elements', 'edit_wbs_elements',

                    // Livrables (CRITIQUE pour MOA)
                    'view_deliverables', 'create_deliverables', 'edit_deliverables', 'approve_deliverables',

                    // Qualité (CRITIQUE pour MOA)
                    'view_quality_metrics', 'create_quality_metrics', 'edit_quality_metrics',
                    'view_quality_audits', 'create_quality_audits', 'edit_quality_audits',

                    // Changements (CRITIQUE pour MOA)
                    'view_change_requests', 'create_change_requests', 'edit_change_requests', 'approve_change_requests',

                    // Parties prenantes
                    'view_stakeholders', 'create_stakeholders', 'edit_stakeholders',
                    'view_stakeholder_engagement', 'create_stakeholder_engagement', 'edit_stakeholder_engagement',

                    // Documents
                    'view_documents', 'create_documents', 'edit_documents', 'approve_documents',

                    // Consultation
                    'view_tasks', 'view_budgets', 'view_expenses',
                    'view_risks', 'create_risks',
                    'view_issues', 'create_issues', 'edit_issues',
                    'view_milestones', 'view_schedules',
                    'view_project_status_reports', 'create_project_status_reports',
                    'view_reports', 'export_reports',

                    // Organisations du projet
                    'view_project_organizations', 'create_project_organizations', 'edit_project_organizations',
                ],
            ],

            [
                'name' => 'Contrôleur Qualité MOA',
                'slug' => 'moa_quality_controller',
                'description' => 'Contrôleur qualité côté MOA - Focus validation et conformité des livrables',
                'scope' => 'project',
                'organization_id' => null,
                'permissions' => [
                    // Projets (consultation)
                    'view_projects',

                    // Livrables (validation et approbation)
                    'view_deliverables', 'approve_deliverables',

                    // Qualité (CORE RESPONSIBILITY)
                    'view_quality_metrics', 'create_quality_metrics', 'edit_quality_metrics', 'delete_quality_metrics',
                    'view_quality_audits', 'create_quality_audits', 'edit_quality_audits', 'delete_quality_audits',

                    // Leçons apprises
                    'view_lessons_learned', 'create_lessons_learned', 'edit_lessons_learned',

                    // Documents (validation)
                    'view_documents', 'create_documents', 'edit_documents', 'approve_documents',

                    // Issues qualité
                    'view_issues', 'create_issues', 'edit_issues',

                    // Consultation
                    'view_wbs_elements', 'view_tasks',
                    'view_change_requests',
                    'view_project_status_reports',

                    // Organisations du projet
                    'view_project_organizations',
                ],
            ],

            [
                'name' => 'Assistant MOA',
                'slug' => 'moa_assistant',
                'description' => 'Assistant Maître d\'Ouvrage - Support à la maîtrise du scope et suivi qualité',
                'scope' => 'project',
                'organization_id' => null,
                'permissions' => [
                    // Projets
                    'view_projects',

                    // WBS et scope
                    'view_wbs_elements', 'create_wbs_elements', 'edit_wbs_elements',

                    // Livrables (pas d'approbation)
                    'view_deliverables', 'create_deliverables', 'edit_deliverables',

                    // Qualité
                    'view_quality_metrics', 'create_quality_metrics', 'edit_quality_metrics',
                    'view_quality_audits', 'create_quality_audits',

                    // Changements (création et suivi, pas d'approbation)
                    'view_change_requests', 'create_change_requests', 'edit_change_requests',

                    // Parties prenantes
                    'view_stakeholders', 'edit_stakeholders',
                    'view_stakeholder_engagement', 'edit_stakeholder_engagement',

                    // Documents
                    'view_documents', 'create_documents', 'edit_documents',

                    // Consultation
                    'view_tasks', 'view_budgets',
                    'view_risks', 'create_risks',
                    'view_issues', 'create_issues',
                    'view_milestones', 'view_schedules',
                    'view_project_status_reports', 'create_project_status_reports',

                    // Organisations du projet
                    'view_project_organizations',
                ],
            ],
        ];

        $created = 0;
        $skipped = 0;

        DB::beginTransaction();

        try {
            foreach ($roles as $roleData) {
                // Vérifier si le rôle existe déjà
                $exists = DB::table('roles')
                    ->where('slug', $roleData['slug'])
                    ->exists();

                if ($exists) {
                    $this->command->warn("⊘ {$roleData['name']} existe déjà, ignoré.");
                    $skipped++;
                    continue;
                }

                // Créer le rôle
                $roleId = DB::table('roles')->insertGetId([
                    'name' => $roleData['name'],
                    'slug' => $roleData['slug'],
                    'description' => $roleData['description'],
                    'scope' => $roleData['scope'],
                    'organization_id' => $roleData['organization_id'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Attacher les permissions
                $permissionsAdded = 0;
                foreach ($roleData['permissions'] as $permissionSlug) {
                    $permission = DB::table('permissions')
                        ->where('slug', $permissionSlug)
                        ->first();

                    if ($permission) {
                        DB::table('role_permission')->insert([
                            'role_id' => $roleId,
                            'permission_id' => $permission->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $permissionsAdded++;
                    } else {
                        $this->command->warn("  ⊘ Permission '{$permissionSlug}' introuvable pour {$roleData['name']}");
                    }
                }

                $created++;
                $this->command->info("✓ {$roleData['name']} créé avec {$permissionsAdded} permissions.");
            }

            DB::commit();

            $this->command->info("========================================");
            $this->command->info("Rôles d'approbation Client et MOA ajoutés !");
            $this->command->info("✓ Rôles créés: {$created}");
            $this->command->warn("⊘ Rôles ignorés: {$skipped}");
            $this->command->info("========================================");
            $this->command->info("");
            $this->command->info("Nouveaux rôles disponibles :");
            $this->command->info("  • Client Sponsor (approbations stratégiques)");
            $this->command->info("  • Responsable MOA (maîtrise scope + qualité)");
            $this->command->info("  • Contrôleur Qualité MOA (validation livrables)");
            $this->command->info("  • Assistant MOA (support MOA)");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("Erreur lors de la création des rôles: " . $e->getMessage());
            $this->command->error("Trace: " . $e->getTraceAsString());
        }
    }
}
