<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info("Création des rôles...");

        // Définition des rôles
        $roles = [
            // Rôles SAMSIC internes (scope global)
            [
                'name' => 'Super Administrateur',
                'slug' => 'super_admin',
                'description' => 'Accès complet à toutes les fonctionnalités du système',
                'scope' => 'global',
                'organization_id' => null,
                'permissions' => 'all', // Toutes les permissions
            ],
            [
                'name' => 'Directeur PMO',
                'slug' => 'pmo_director',
                'description' => 'Responsable du PMO, vision transversale de tous les projets',
                'scope' => 'global',
                'organization_id' => null,
                'permissions' => [
                    'view_portfolios', 'view_programs', 'view_projects', 'approve_projects',
                    'view_budgets', 'approve_budgets', 'view_reports', 'export_reports',
                    'view_risks', 'view_issues', 'view_resources', 'view_documents',
                ],
            ],
            [
                'name' => 'Manager PMO',
                'slug' => 'pmo_manager',
                'description' => 'Gestionnaire PMO avec accès étendu aux projets',
                'scope' => 'global',
                'organization_id' => null,
                'permissions' => [
                    'view_portfolios', 'view_programs', 'view_projects', 'create_projects', 'edit_projects',
                    'view_budgets', 'view_reports', 'create_reports', 'export_reports',
                    'view_risks', 'create_risks', 'view_issues', 'create_issues',
                    'view_resources', 'view_documents',
                ],
            ],

            // Rôles de gestion de portefeuille
            [
                'name' => 'Directeur de Portfolio',
                'slug' => 'portfolio_director',
                'description' => 'Responsable d\'un portfolio de projets',
                'scope' => 'organization',
                'organization_id' => null,
                'permissions' => [
                    'view_portfolios', 'edit_portfolios',
                    'view_programs', 'create_programs', 'edit_programs',
                    'view_projects', 'create_projects', 'edit_projects', 'approve_projects',
                    'view_budgets', 'approve_budgets', 'view_reports', 'export_reports',
                ],
            ],

            // Rôles de gestion de programme
            [
                'name' => 'Manager de Programme',
                'slug' => 'program_manager',
                'description' => 'Responsable d\'un programme',
                'scope' => 'project',
                'organization_id' => null,
                'permissions' => [
                    'view_programs', 'edit_programs',
                    'view_projects', 'create_projects', 'edit_projects',
                    'view_tasks', 'create_tasks', 'edit_tasks',
                    'view_budgets', 'edit_budgets',
                    'view_risks', 'create_risks', 'edit_risks',
                    'view_issues', 'create_issues', 'edit_issues',
                    'view_reports', 'create_reports',
                ],
            ],

            // Rôles de gestion de projet
            [
                'name' => 'Chef de Projet',
                'slug' => 'project_manager',
                'description' => 'Responsable d\'un projet spécifique',
                'scope' => 'project',
                'organization_id' => null,
                'permissions' => [
                    'view_projects', 'edit_projects',
                    'view_tasks', 'create_tasks', 'edit_tasks', 'delete_tasks',
                    'view_budgets', 'edit_budgets',
                    'view_expenses', 'create_expenses', 'edit_expenses',
                    'view_risks', 'create_risks', 'edit_risks',
                    'view_issues', 'create_issues', 'edit_issues',
                    'view_documents', 'create_documents', 'edit_documents',
                    'view_resources', 'create_resources', 'edit_resources',
                    'view_reports', 'create_reports',
                ],
            ],
            [
                'name' => 'Coordinateur de Projet',
                'slug' => 'project_coordinator',
                'description' => 'Assistance au chef de projet',
                'scope' => 'project',
                'organization_id' => null,
                'permissions' => [
                    'view_projects',
                    'view_tasks', 'create_tasks', 'edit_tasks',
                    'view_budgets', 'view_expenses',
                    'view_risks', 'create_risks',
                    'view_issues', 'create_issues',
                    'view_documents', 'create_documents',
                    'view_reports',
                ],
            ],

            // Rôles métiers
            [
                'name' => 'Responsable Achats',
                'slug' => 'procurement_manager',
                'description' => 'Gestion des achats et approvisionnements',
                'scope' => 'global',
                'organization_id' => null,
                'permissions' => [
                    'view_projects', 'view_budgets', 'view_expenses',
                    'create_expenses', 'edit_expenses',
                ],
            ],
            [
                'name' => 'Responsable Facturation',
                'slug' => 'billing_manager',
                'description' => 'Gestion de la facturation',
                'scope' => 'global',
                'organization_id' => null,
                'permissions' => [
                    'view_projects', 'view_budgets', 'view_expenses',
                    'view_reports', 'export_reports',
                ],
            ],

            // Rôles clients
            [
                'name' => 'Client Administrateur',
                'slug' => 'client_admin',
                'description' => 'Administrateur côté client',
                'scope' => 'organization',
                'organization_id' => null, // Sera spécifique à chaque organisation client
                'permissions' => [
                    'view_projects', 'view_tasks',
                    'view_budgets', 'view_expenses',
                    'view_risks', 'view_issues', 'create_issues',
                    'view_documents', 'view_reports', 'export_reports',
                ],
            ],
            [
                'name' => 'Client Lecteur',
                'slug' => 'client_viewer',
                'description' => 'Visualisation uniquement pour les clients',
                'scope' => 'organization',
                'organization_id' => null,
                'permissions' => [
                    'view_projects', 'view_tasks',
                    'view_budgets',
                    'view_risks', 'view_issues',
                    'view_documents', 'view_reports',
                ],
            ],

            // Rôles techniques
            [
                'name' => 'Membre d\'Équipe',
                'slug' => 'team_member',
                'description' => 'Membre d\'équipe projet',
                'scope' => 'project',
                'organization_id' => null,
                'permissions' => [
                    'view_projects', 'view_tasks', 'edit_tasks',
                    'view_documents', 'view_issues', 'create_issues',
                ],
            ],
            [
                'name' => 'Chef d\'Équipe',
                'slug' => 'team_lead',
                'description' => 'Responsable d\'une équipe technique',
                'scope' => 'project',
                'organization_id' => null,
                'permissions' => [
                    'view_projects', 'view_tasks', 'create_tasks', 'edit_tasks', 'delete_tasks',
                    'view_teams', 'edit_teams', 'view_team_members', 'create_team_members', 'edit_team_members',
                    'view_resource_allocations', 'create_resource_allocations', 'edit_resource_allocations',
                    'view_documents', 'create_documents', 'view_issues', 'create_issues', 'edit_issues',
                ],
            ],

            // Rôles PMBOK spécialisés
            [
                'name' => 'Sponsor de Projet',
                'slug' => 'project_sponsor',
                'description' => 'Sponsor et décideur stratégique du projet',
                'scope' => 'project',
                'organization_id' => null,
                'permissions' => [
                    'view_projects', 'approve_projects',
                    'view_budgets', 'approve_budgets',
                    'view_change_requests', 'approve_change_requests',
                    'view_deliverables', 'approve_deliverables',
                    'view_project_status_reports', 'view_kpis',
                    'view_risks', 'view_issues',
                ],
            ],
            [
                'name' => 'Analyste d\'Affaires',
                'slug' => 'business_analyst',
                'description' => 'Analyse des besoins et exigences métiers',
                'scope' => 'project',
                'organization_id' => null,
                'permissions' => [
                    'view_projects', 'edit_projects',
                    'view_wbs_elements', 'create_wbs_elements', 'edit_wbs_elements',
                    'view_deliverables', 'create_deliverables', 'edit_deliverables',
                    'view_stakeholders', 'create_stakeholders', 'edit_stakeholders',
                    'view_stakeholder_engagement', 'create_stakeholder_engagement', 'edit_stakeholder_engagement',
                    'view_change_requests', 'create_change_requests', 'edit_change_requests',
                    'view_documents', 'create_documents', 'edit_documents',
                ],
            ],
            [
                'name' => 'Responsable Qualité',
                'slug' => 'quality_manager',
                'description' => 'Gestion et assurance qualité',
                'scope' => 'project',
                'organization_id' => null,
                'permissions' => [
                    'view_projects',
                    'view_quality_metrics', 'create_quality_metrics', 'edit_quality_metrics',
                    'view_quality_audits', 'create_quality_audits', 'edit_quality_audits',
                    'view_deliverables', 'approve_deliverables',
                    'view_documents', 'create_documents', 'edit_documents', 'approve_documents',
                    'view_lessons_learned', 'create_lessons_learned', 'edit_lessons_learned',
                    'view_issues', 'create_issues', 'edit_issues',
                ],
            ],
            [
                'name' => 'Gestionnaire des Risques',
                'slug' => 'risk_manager',
                'description' => 'Gestion des risques projet',
                'scope' => 'project',
                'organization_id' => null,
                'permissions' => [
                    'view_projects',
                    'view_risks', 'create_risks', 'edit_risks', 'delete_risks',
                    'view_risk_responses', 'create_risk_responses', 'edit_risk_responses', 'delete_risk_responses',
                    'view_issues', 'create_issues', 'edit_issues',
                    'view_documents', 'create_documents',
                    'view_project_status_reports', 'create_project_status_reports',
                ],
            ],
            [
                'name' => 'Gestionnaire des Ressources',
                'slug' => 'resource_manager',
                'description' => 'Gestion et allocation des ressources',
                'scope' => 'global',
                'organization_id' => null,
                'permissions' => [
                    'view_projects', 'view_programs',
                    'view_resources', 'create_resources', 'edit_resources', 'delete_resources',
                    'view_resource_allocations', 'create_resource_allocations', 'edit_resource_allocations', 'delete_resource_allocations',
                    'view_teams', 'create_teams', 'edit_teams', 'delete_teams',
                    'view_team_members', 'create_team_members', 'edit_team_members', 'delete_team_members',
                    'view_schedules', 'edit_schedules',
                ],
            ],
            [
                'name' => 'Planificateur',
                'slug' => 'planner',
                'description' => 'Planification et ordonnancement',
                'scope' => 'project',
                'organization_id' => null,
                'permissions' => [
                    'view_projects',
                    'view_project_phases', 'create_project_phases', 'edit_project_phases',
                    'view_wbs_elements', 'create_wbs_elements', 'edit_wbs_elements',
                    'view_tasks', 'create_tasks', 'edit_tasks',
                    'view_milestones', 'create_milestones', 'edit_milestones',
                    'view_schedules', 'create_schedules', 'edit_schedules',
                    'view_resource_allocations', 'create_resource_allocations', 'edit_resource_allocations',
                    'view_deliverables',
                ],
            ],
            [
                'name' => 'Contrôleur de Gestion',
                'slug' => 'controller',
                'description' => 'Contrôle de gestion et suivi budgétaire',
                'scope' => 'global',
                'organization_id' => null,
                'permissions' => [
                    'view_portfolios', 'view_programs', 'view_projects',
                    'view_budgets', 'edit_budgets',
                    'view_expenses', 'edit_expenses',
                    'view_earned_value_metrics', 'create_earned_value_metrics', 'edit_earned_value_metrics',
                    'view_kpis', 'create_kpis', 'edit_kpis',
                    'view_project_status_reports', 'view_reports', 'create_reports', 'export_reports',
                ],
            ],
            [
                'name' => 'Membre CCB',
                'slug' => 'ccb_member',
                'description' => 'Membre du Change Control Board',
                'scope' => 'project',
                'organization_id' => null,
                'permissions' => [
                    'view_projects',
                    'view_change_requests', 'approve_change_requests',
                    'view_budgets', 'view_schedules',
                    'view_risks', 'view_issues',
                    'view_documents',
                ],
            ],

            // Rôles métiers SAMSIC (départements internes)
            [
                'name' => 'Responsable Méthodes',
                'slug' => 'methods_manager',
                'description' => 'Responsable des méthodes et processus',
                'scope' => 'global',
                'organization_id' => null,
                'permissions' => [
                    'view_projects', 'view_programs',
                    'view_quality_metrics', 'create_quality_metrics', 'edit_quality_metrics',
                    'view_quality_audits', 'create_quality_audits', 'edit_quality_audits',
                    'view_lessons_learned', 'create_lessons_learned', 'edit_lessons_learned',
                    'view_documents', 'create_documents', 'edit_documents',
                    'view_processes', 'create_processes', 'edit_processes',
                    'view_reports', 'create_reports', 'export_reports',
                ],
            ],
            [
                'name' => 'Gestionnaire Stock',
                'slug' => 'stock_manager',
                'description' => 'Gestion des stocks et inventaires',
                'scope' => 'global',
                'organization_id' => null,
                'permissions' => [
                    'view_projects',
                    'view_resources', 'create_resources', 'edit_resources',
                    'view_procurements', 'view_vendors',
                    'view_expenses',
                ],
            ],
            [
                'name' => 'Expert Métier',
                'slug' => 'subject_matter_expert',
                'description' => 'Expert dans un domaine spécifique (SME)',
                'scope' => 'project',
                'organization_id' => null,
                'permissions' => [
                    'view_projects', 'view_tasks',
                    'view_deliverables', 'edit_deliverables',
                    'view_documents', 'create_documents', 'edit_documents',
                    'view_quality_metrics', 'view_quality_audits',
                    'view_issues', 'create_issues',
                    'view_lessons_learned', 'create_lessons_learned',
                ],
            ],

            // Rôles communication et reporting
            [
                'name' => 'Responsable Communication',
                'slug' => 'communications_manager',
                'description' => 'Gestion de la communication projet',
                'scope' => 'project',
                'organization_id' => null,
                'permissions' => [
                    'view_projects',
                    'view_communications', 'create_communications', 'edit_communications', 'delete_communications',
                    'view_meetings', 'create_meetings', 'edit_meetings', 'delete_meetings',
                    'view_meeting_attendees', 'create_meeting_attendees', 'edit_meeting_attendees',
                    'view_stakeholders', 'view_stakeholder_engagement', 'edit_stakeholder_engagement',
                    'view_documents', 'create_documents',
                    'view_project_status_reports', 'create_project_status_reports',
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
                if ($roleData['permissions'] === 'all') {
                    // Toutes les permissions
                    $allPermissions = DB::table('permissions')->pluck('id');
                    foreach ($allPermissions as $permissionId) {
                        DB::table('role_permission')->insert([
                            'role_id' => $roleId,
                            'permission_id' => $permissionId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                } else {
                    // Permissions spécifiques
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
                        }
                    }
                }

                $created++;
                $this->command->info("✓ {$roleData['name']} créé avec " .
                    ($roleData['permissions'] === 'all' ? 'toutes les permissions' : count($roleData['permissions']) . ' permissions') . ".");
            }

            DB::commit();

            $this->command->info("========================================");
            $this->command->info("Rôles créés avec succès!");
            $this->command->info("✓ Rôles créés: {$created}");
            $this->command->warn("⊘ Rôles ignorés: {$skipped}");
            $this->command->info("========================================");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("Erreur lors de la création des rôles: " . $e->getMessage());
            $this->command->error("Trace: " . $e->getTraceAsString());
        }
    }
}
