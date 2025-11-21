<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Role;
use App\Models\Permission;

class ProjectRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates 8 project-scoped roles as defined in PROJECT_PERMISSIONS_CLARIFICATION_ANSWERS.md
     */
    public function run(): void
    {
        $this->command->info("Creating project-level roles...");

        $projectRoles = [
            // 1. Project Manager (Chef de Projet)
            [
                'name' => 'Chef de Projet',
                'name_en' => 'Project Manager',
                'slug' => 'project_manager',
                'description' => 'Responsabilité principale de la livraison du projet. Accès complet aux ressources du projet.',
                'description_en' => 'Primary responsibility for project delivery. Full access to project resources.',
                'scope' => 'project',
                'organization_id' => null,
                'permissions' => [
                    'view_projects', 'edit_projects',
                    'view_tasks', 'create_tasks', 'edit_tasks', 'delete_tasks', 'assign_tasks',
                    'view_wbs', 'create_wbs', 'edit_wbs', 'delete_wbs',
                    'view_deliverables', 'create_deliverables', 'edit_deliverables', 'delete_deliverables', 'approve_deliverables',
                    'view_budgets', 'edit_budgets',
                    'view_expenses', 'create_expenses', 'edit_expenses', 'approve_expenses',
                    'view_risks', 'create_risks', 'edit_risks', 'delete_risks',
                    'view_issues', 'create_issues', 'edit_issues', 'delete_issues',
                    'view_documents', 'upload_documents', 'edit_documents', 'delete_documents',
                    'view_reports', 'create_reports',
                    'view_team', 'manage_team', 'add_team_members', 'remove_team_members',
                    'view_milestones', 'create_milestones', 'edit_milestones',
                ],
            ],

            // 2. Technical Lead (Responsable Technique)
            [
                'name' => 'Responsable Technique',
                'name_en' => 'Technical Lead',
                'slug' => 'technical_lead',
                'description' => 'Supervision technique et architecture. Permissions techniques à l\'échelle du projet.',
                'description_en' => 'Technical oversight and architecture. Technical permissions project-wide.',
                'scope' => 'project',
                'organization_id' => null,
                'permissions' => [
                    'view_projects',
                    'view_tasks', 'create_tasks', 'edit_tasks', 'assign_tasks',
                    'view_wbs', 'create_wbs', 'edit_wbs',
                    'view_deliverables', 'create_deliverables', 'edit_deliverables',
                    'view_risks', 'create_risks', 'edit_risks',
                    'view_issues', 'create_issues', 'edit_issues',
                    'view_documents', 'upload_documents', 'edit_documents',
                    'view_reports',
                    'view_team',
                ],
            ],

            // 3. Project Technician / Contributor (Technicien de Projet / Contributeur)
            [
                'name' => 'Technicien de Projet',
                'name_en' => 'Project Technician',
                'slug' => 'project_technician',
                'description' => 'Exécute les tâches assignées. Crée des livrables.',
                'description_en' => 'Executes assigned tasks. Creates deliverables.',
                'scope' => 'project',
                'organization_id' => null,
                'permissions' => [
                    'view_projects',
                    'view_tasks', 'edit_tasks', 'complete_tasks',
                    'view_wbs',
                    'view_deliverables', 'create_deliverables', 'edit_deliverables',
                    'view_documents', 'upload_documents',
                    'view_issues', 'create_issues',
                    'view_team',
                ],
            ],

            // 4. Project Observer (Observateur)
            [
                'name' => 'Observateur de Projet',
                'name_en' => 'Project Observer',
                'slug' => 'project_observer',
                'description' => 'Accès en lecture seule pour les parties prenantes.',
                'description_en' => 'Read-only access for stakeholders.',
                'scope' => 'project',
                'organization_id' => null,
                'permissions' => [
                    'view_projects',
                    'view_tasks',
                    'view_wbs',
                    'view_deliverables',
                    'view_budgets',
                    'view_expenses',
                    'view_risks',
                    'view_issues',
                    'view_documents',
                    'view_reports',
                    'view_team',
                    'view_milestones',
                ],
            ],

            // 5. Budget Controller (Contrôleur de Gestion / Responsable Budget)
            [
                'name' => 'Contrôleur de Gestion',
                'name_en' => 'Budget Controller',
                'slug' => 'budget_controller',
                'description' => 'Gère les finances du projet. Approuve les dépenses.',
                'description_en' => 'Manages project financials. Approves expenses.',
                'scope' => 'project',
                'organization_id' => null,
                'permissions' => [
                    'view_projects',
                    'view_budgets', 'edit_budgets',
                    'view_expenses', 'create_expenses', 'edit_expenses', 'approve_expenses',
                    'view_reports', 'create_reports',
                    'view_team',
                ],
            ],

            // 6. Quality Manager (Responsable Qualité / Manager QA)
            [
                'name' => 'Responsable Qualité',
                'name_en' => 'Quality Manager',
                'slug' => 'quality_manager',
                'description' => 'Assurance qualité et autorité d\'approbation. Révise les livrables.',
                'description_en' => 'Quality assurance and approval authority. Reviews deliverables.',
                'scope' => 'project',
                'organization_id' => null,
                'permissions' => [
                    'view_projects',
                    'view_tasks',
                    'view_wbs',
                    'view_deliverables', 'edit_deliverables', 'approve_deliverables',
                    'view_documents', 'upload_documents',
                    'view_issues', 'create_issues', 'edit_issues',
                    'view_reports', 'create_reports',
                    'view_team',
                ],
            ],

            // 7. Project Coordinator (Coordinateur de Projet)
            [
                'name' => 'Coordinateur de Projet',
                'name_en' => 'Project Coordinator',
                'slug' => 'project_coordinator',
                'description' => 'Support administratif au chef de projet. Permissions limitées de gestion de projet.',
                'description_en' => 'Administrative support to PM. Limited project management permissions.',
                'scope' => 'project',
                'organization_id' => null,
                'permissions' => [
                    'view_projects', 'edit_projects',
                    'view_tasks', 'create_tasks', 'edit_tasks',
                    'view_wbs',
                    'view_deliverables', 'create_deliverables',
                    'view_documents', 'upload_documents', 'edit_documents',
                    'view_issues', 'create_issues', 'edit_issues',
                    'view_reports',
                    'view_team',
                    'view_milestones',
                ],
            ],

            // 8. Subcontractor Lead (Chef de Projet Sous-Traitant)
            [
                'name' => 'Chef de Projet Sous-Traitant',
                'name_en' => 'Subcontractor Lead',
                'slug' => 'subcontractor_lead',
                'description' => 'Pour les organisations sous-traitantes externes. Limité au périmètre du sous-traitant (élément WBS).',
                'description_en' => 'For external subcontractor organizations. Limited to subcontractor scope (WBS element).',
                'scope' => 'project',
                'organization_id' => null,
                'permissions' => [
                    'view_projects',
                    'view_tasks', 'edit_tasks', 'complete_tasks',
                    'view_wbs',
                    'view_deliverables', 'create_deliverables', 'edit_deliverables',
                    'view_documents', 'upload_documents',
                    'view_issues', 'create_issues',
                    'view_team',
                ],
            ],
        ];

        foreach ($projectRoles as $roleData) {
            $permissions = $roleData['permissions'];
            unset($roleData['permissions']);
            unset($roleData['name_en']);
            unset($roleData['description_en']);

            // Create or update role
            $role = Role::updateOrCreate(
                ['slug' => $roleData['slug']],
                $roleData
            );

            $this->command->info("  ✓ Role created/updated: {$role->name} ({$role->slug})");

            // Attach permissions
            $permissionIds = Permission::whereIn('slug', $permissions)->pluck('id')->toArray();

            if (empty($permissionIds)) {
                $this->command->warn("    ⚠ No permissions found for role: {$role->slug}");
                $this->command->warn("    → Make sure to run PermissionsSeeder first!");
            } else {
                $role->permissions()->sync($permissionIds);
                $this->command->info("    → Attached " . count($permissionIds) . " permissions");
            }
        }

        $this->command->info("\n✅ Project roles seeded successfully!");
        $this->command->info("Created 8 project-level roles:");
        $this->command->info("  1. Chef de Projet (Project Manager)");
        $this->command->info("  2. Responsable Technique (Technical Lead)");
        $this->command->info("  3. Technicien de Projet (Project Technician)");
        $this->command->info("  4. Observateur de Projet (Project Observer)");
        $this->command->info("  5. Contrôleur de Gestion (Budget Controller)");
        $this->command->info("  6. Responsable Qualité (Quality Manager)");
        $this->command->info("  7. Coordinateur de Projet (Project Coordinator)");
        $this->command->info("  8. Chef de Projet Sous-Traitant (Subcontractor Lead)");
    }
}
