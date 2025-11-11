<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder complet pour créer tous les rôles du système
 *
 * ARCHITECTURE MULTI-TENANT PURE (sans type d'organisation):
 * ============================================================
 * - Organizations n'ont PLUS de colonne "type" (Internal/Client/Partner)
 * - Le rôle d'une organisation est CONTEXTUEL via project_organizations.role
 * - Une organisation peut être sponsor sur Projet A, moe sur Projet B, moa sur Projet C
 * - RLS: Tous les utilisateurs (sauf system_admin) sont filtrés via project_organizations
 * - Seul bypass: is_system_admin = true
 *
 * RÔLES D'UTILISATEURS (user_roles):
 * ===================================
 * - Rôles peuvent être scopés: global, organization, portfolio, program, project
 * - Un utilisateur peut avoir plusieurs rôles avec différents scopes
 * - Les permissions sont vérifiées via RBAC (pas via type d'organisation)
 *
 * Basé sur PMBOK + besoins spécifiques SAMSIC
 */
class ComprehensiveRolesSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();

        try {
            $this->command->info('🚀 Création des rôles (architecture multi-tenant pure)...');

            // 1. Rôles administratifs système
            $this->createSystemAdminRoles();

            // 2. Rôles de gestion de portefeuille et programmes
            $this->createPortfolioProgramRoles();

            // 3. Rôles de gestion de projet
            $this->createProjectManagementRoles();

            // 4. Rôles métiers transversaux
            $this->createBusinessRoles();

            // 5. Rôles de participation au projet (basés sur project_organizations.role)
            $this->createProjectParticipationRoles();

            // 6. Rôles PMBOK spécialisés
            $this->createPMBOKSpecializedRoles();

            // 7. Rôles de gouvernance
            $this->createGovernanceRoles();

            // 8. Rôles techniques
            $this->createTechnicalRoles();

            DB::commit();

            $totalRoles = Role::count();
            $this->command->info("✅ {$totalRoles} rôles créés avec succès!");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Erreur: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 1. Rôles administratifs système
     * Scope: global - Aucun filtrage
     */
    private function createSystemAdminRoles(): void
    {
        $this->command->info('  👑 Rôles administratifs...');

        // Super Admin - Seul rôle avec bypass total
        $superAdmin = $this->createRole(
            'super_admin',
            'Super Administrateur',
            'global',
            'Accès complet système. Utilisé avec is_system_admin=true pour bypass RLS.'
        );
        $superAdmin->permissions()->sync(Permission::all()->pluck('id'));

        // Directeur PMO - Vue transversale + approbations
        $pmoDirector = $this->createRole(
            'pmo_director',
            'Directeur PMO',
            'global',
            'Vision transversale, approbations stratégiques sur tous les projets accessibles.'
        );
        $this->attachPermissions($pmoDirector, [
            'view_*', 'approve_projects', 'approve_budgets', 'approve_change_requests',
            'export_*',
        ]);

        // Manager PMO - Gestion opérationnelle
        $pmoManager = $this->createRole(
            'pmo_manager',
            'Manager PMO',
            'global',
            'Gestion opérationnelle PMO: processus, support, standardisation.'
        );
        $this->attachPermissions($pmoManager, [
            'view_*', 'create_*', 'edit_*',
            'approve_deliverables', 'approve_documents',
            'export_*',
        ], ['delete_projects', 'delete_programs', 'delete_portfolios']);
    }

    /**
     * 2. Rôles de gestion de portefeuille/programmes
     */
    private function createPortfolioProgramRoles(): void
    {
        $this->command->info('  📊 Rôles portfolio/programme...');

        $portfolioDirector = $this->createRole(
            'portfolio_director',
            'Directeur de Portfolio',
            'organization',
            'Responsable portfolio, supervise stratégie et allocation ressources.'
        );
        $this->attachPermissions($portfolioDirector, [
            'view_*', 'edit_*',
            '*_portfolios', '*_programs', 'view_projects', 'approve_projects', 'approve_budgets',
            'export_*',
        ]);

        $programManager = $this->createRole(
            'program_manager',
            'Manager de Programme',
            'project',
            'Coordonne plusieurs projets interdépendants d\'un programme.'
        );
        $this->attachPermissions($programManager, [
            'view_*', 'create_*', 'edit_*',
            '*_programs', '*_projects', '*_tasks', '*_milestones', '*_risks', '*_issues',
        ]);
    }

    /**
     * 3. Rôles de gestion de projet
     */
    private function createProjectManagementRoles(): void
    {
        $this->command->info('  📋 Rôles gestion de projet...');

        $projectManager = $this->createRole(
            'project_manager',
            'Chef de Projet',
            'project',
            'Responsabilité complète planification, exécution, clôture d\'un projet.'
        );
        $this->attachPermissions($projectManager, [
            'view_*', 'create_*', 'edit_*',
            '*_tasks', '*_deliverables', '*_milestones', '*_budgets', '*_risks', '*_issues',
            '*_resources', '*_resource_allocations', '*_teams', '*_documents',
            'approve_deliverables',
        ], ['delete_projects', 'delete_budgets']);

        $projectCoordinator = $this->createRole(
            'project_coordinator',
            'Coordinateur de Projet',
            'project',
            'Support administratif et suivi opérationnel au chef de projet.'
        );
        $this->attachPermissions($projectCoordinator, [
            'view_*', 'create_tasks', 'edit_tasks', 'create_issues',
            'view_deliverables', 'view_budgets', 'view_risks', 'view_documents',
            'create_communications', 'edit_communications',
        ]);

        $projectSponsor = $this->createRole(
            'project_sponsor',
            'Sponsor de Projet',
            'project',
            'Sponsor exécutif, approuve décisions stratégiques et budgets majeurs.'
        );
        $this->attachPermissions($projectSponsor, [
            'view_projects', 'view_budgets', 'view_milestones', 'view_deliverables',
            'approve_projects', 'approve_budgets', 'approve_change_requests', 'approve_deliverables',
            'export_*',
        ]);
    }

    /**
     * 4. Rôles métiers transversaux
     * Pour utilisateurs qui travaillent sur plusieurs projets
     */
    private function createBusinessRoles(): void
    {
        $this->command->info('  🏢 Rôles métiers transversaux...');

        $procurementManager = $this->createRole(
            'procurement_manager',
            'Responsable Achats',
            'global',
            'Gestion achats, fournisseurs et approvisionnements multi-projets.'
        );
        $this->attachPermissions($procurementManager, [
            'view_projects', 'view_budgets',
            '*_vendors', '*_procurements', '*_expenses',
        ]);

        $billingManager = $this->createRole(
            'billing_manager',
            'Responsable Facturation',
            'global',
            'Facturation client et suivi financier multi-projets.'
        );
        $this->attachPermissions($billingManager, [
            'view_projects', 'view_budgets', 'view_expenses', 'view_earned_value_metrics',
            'export_*',
        ]);

        $controller = $this->createRole(
            'controller',
            'Contrôleur de Gestion',
            'global',
            'Contrôle budgétaire, analyse coûts, métriques performance.'
        );
        $this->attachPermissions($controller, [
            'view_*',
            '*_budgets', '*_expenses', '*_earned_value_metrics', '*_kpis',
            'export_*',
        ]);

        $methodsManager = $this->createRole(
            'methods_manager',
            'Responsable Méthodes',
            'global',
            'Processus, standards qualité, capitalisation bonnes pratiques.'
        );
        $this->attachPermissions($methodsManager, [
            'view_*',
            '*_quality_metrics', '*_quality_audits', '*_lessons_learned',
            'export_*',
        ]);

        $resourceManager = $this->createRole(
            'resource_manager',
            'Gestionnaire Ressources',
            'global',
            'Allocation ressources humaines et matérielles multi-projets.'
        );
        $this->attachPermissions($resourceManager, [
            'view_projects', 'view_tasks',
            '*_resources', '*_resource_allocations', '*_teams', '*_team_members',
        ]);

        $stockManager = $this->createRole(
            'stock_manager',
            'Gestionnaire Stock',
            'global',
            'Gestion stocks, inventaires et équipements.'
        );
        $this->attachPermissions($stockManager, [
            'view_projects', 'view_procurements', 'view_vendors', 'view_expenses',
            '*_resources',
        ]);
    }

    /**
     * 5. Rôles de participation projet
     * Correspondent aux rôles project_organizations.role: sponsor, moa, moe, subcontractor
     */
    private function createProjectParticipationRoles(): void
    {
        $this->command->info('  🤝 Rôles participation projet...');

        // SPONSOR (organization.role = sponsor dans project_organizations)
        // Généralement le client qui finance
        $sponsorAdmin = $this->createRole(
            'sponsor_admin',
            'Administrateur Sponsor',
            'organization',
            'Représentant organisation sponsor avec pouvoirs d\'approbation stratégique.'
        );
        $this->attachPermissions($sponsorAdmin, [
            'view_*',
            'approve_projects', 'approve_budgets', 'approve_deliverables',
            'approve_change_requests', 'approve_documents',
            'create_issues', 'create_change_requests',
            'export_*',
        ]);

        $sponsorViewer = $this->createRole(
            'sponsor_viewer',
            'Observateur Sponsor',
            'organization',
            'Visualisation projets de l\'organisation sponsor.'
        );
        $this->attachPermissions($sponsorViewer, [
            'view_projects', 'view_milestones', 'view_deliverables',
            'view_project_status_reports', 'view_documents',
            'export_reports',
        ]);

        // MOA (organization.role = moa dans project_organizations)
        // Maîtrise du scope, définition livrables, validation qualité
        $moaManager = $this->createRole(
            'moa_manager',
            'Responsable MOA',
            'project',
            'Maître d\'Ouvrage: maîtrise scope, définition livrables, validation qualité.'
        );
        $this->attachPermissions($moaManager, [
            'view_*', 'create_*', 'edit_*',
            'approve_deliverables', 'approve_change_requests', 'approve_documents',
            '*_wbs_elements', '*_deliverables', '*_change_requests',
            '*_quality_metrics', '*_quality_audits',
            '*_stakeholders', '*_communications',
        ]);

        $moaQualityController = $this->createRole(
            'moa_quality_controller',
            'Contrôleur Qualité MOA',
            'project',
            'Validation conformité livrables aux exigences MOA.'
        );
        $this->attachPermissions($moaQualityController, [
            'view_*',
            'approve_deliverables', 'approve_documents',
            '*_quality_metrics', '*_quality_audits', '*_lessons_learned',
            'create_issues',
        ]);

        $moaAssistant = $this->createRole(
            'moa_assistant',
            'Assistant MOA',
            'project',
            'Support maîtrise scope et suivi qualité MOA.'
        );
        $this->attachPermissions($moaAssistant, [
            'view_*',
            'create_deliverables', 'edit_deliverables',
            'create_change_requests', 'edit_change_requests',
            'create_quality_metrics', 'edit_quality_metrics',
            'create_communications',
        ]);

        // MOE/SUBCONTRACTOR (organization.role = moe ou subcontractor)
        // Exécution/production des livrables
        $moeTeamLead = $this->createRole(
            'moe_team_lead',
            'Chef d\'Équipe MOE',
            'project',
            'Gestion opérationnelle équipes exécution/production.'
        );
        $this->attachPermissions($moeTeamLead, [
            'view_*',
            '*_tasks', '*_deliverables', '*_teams', '*_team_members',
            '*_resource_allocations', '*_issues',
            'create_documents',
        ]);

        $moeTeamMember = $this->createRole(
            'moe_team_member',
            'Membre Équipe MOE',
            'project',
            'Exécution tâches assignées.'
        );
        $this->attachPermissions($moeTeamMember, [
            'view_projects', 'view_tasks', 'view_deliverables', 'view_documents',
            'edit_tasks', 'create_issues', 'view_communications',
        ]);
    }

    /**
     * 6. Rôles PMBOK spécialisés
     */
    private function createPMBOKSpecializedRoles(): void
    {
        $this->command->info('  🎓 Rôles PMBOK spécialisés...');

        $businessAnalyst = $this->createRole(
            'business_analyst',
            'Analyste d\'Affaires',
            'project',
            'Analyse besoins, spécifications, WBS, exigences parties prenantes.'
        );
        $this->attachPermissions($businessAnalyst, [
            'view_*',
            '*_wbs_elements', '*_deliverables', '*_change_requests',
            '*_stakeholders', '*_stakeholder_engagement', '*_documents',
        ]);

        $qualityManager = $this->createRole(
            'quality_manager',
            'Responsable Qualité',
            'project',
            'Métriques, audits qualité, approbation livrables.'
        );
        $this->attachPermissions($qualityManager, [
            'view_*',
            '*_quality_metrics', '*_quality_audits', '*_lessons_learned',
            'approve_deliverables', 'approve_documents',
        ]);

        $riskManager = $this->createRole(
            'risk_manager',
            'Gestionnaire Risques',
            'project',
            'Identification, analyse et gestion risques projet.'
        );
        $this->attachPermissions($riskManager, [
            'view_*',
            '*_risks', '*_risk_responses', '*_issues', '*_documents',
        ]);

        $planner = $this->createRole(
            'planner',
            'Planificateur',
            'project',
            'Planification WBS, tâches, jalons, schedules, allocations.'
        );
        $this->attachPermissions($planner, [
            'view_*',
            '*_project_phases', '*_wbs_elements', '*_tasks', '*_milestones',
            '*_schedules', '*_resource_allocations',
        ]);

        $communicationsManager = $this->createRole(
            'communications_manager',
            'Responsable Communication',
            'project',
            'Communications, réunions, engagement parties prenantes.'
        );
        $this->attachPermissions($communicationsManager, [
            'view_*',
            '*_communications', '*_meetings', '*_meeting_attendees',
            '*_stakeholders', '*_stakeholder_engagement', '*_documents',
        ]);

        $sme = $this->createRole(
            'subject_matter_expert',
            'Expert Métier',
            'project',
            'Expertise domaine, conseil et validation technique.'
        );
        $this->attachPermissions($sme, [
            'view_*',
            'edit_deliverables', 'edit_quality_metrics', 'edit_quality_audits',
            'create_lessons_learned', 'edit_lessons_learned',
        ]);
    }

    /**
     * 7. Rôles de gouvernance
     */
    private function createGovernanceRoles(): void
    {
        $this->command->info('  ⚖️  Rôles gouvernance...');

        $ccbMember = $this->createRole(
            'ccb_member',
            'Membre CCB',
            'project',
            'Change Control Board: évalue et approuve demandes de changement.'
        );
        $this->attachPermissions($ccbMember, [
            'view_projects', 'view_budgets', 'view_schedules',
            'view_change_requests', 'approve_change_requests',
            'view_risks', 'view_issues',
        ]);
    }

    /**
     * 8. Rôles techniques
     */
    private function createTechnicalRoles(): void
    {
        $this->command->info('  🔨 Rôles techniques...');

        $technician = $this->createRole(
            'technician',
            'Technicien Spécialisé',
            'project',
            'Tâches techniques spécifiques.'
        );
        $this->attachPermissions($technician, [
            'view_projects', 'view_tasks', 'view_deliverables', 'view_resources',
            'edit_tasks', 'create_issues', 'view_documents',
        ]);
    }

    private function createRole(string $slug, string $name, string $scope, string $description): Role
    {
        return Role::firstOrCreate(
            ['slug' => $slug],
            [
                'name' => $name,
                'scope' => $scope,
                'description' => $description,
            ]
        );
    }

    private function attachPermissions(Role $role, array $patterns, array $exclude = []): void
    {
        $permissions = collect();

        foreach ($patterns as $pattern) {
            if ($pattern === 'view_*') {
                $permissions = $permissions->merge(Permission::where('slug', 'like', 'view_%')->get());
            } elseif ($pattern === 'create_*') {
                $permissions = $permissions->merge(Permission::where('slug', 'like', 'create_%')->get());
            } elseif ($pattern === 'edit_*') {
                $permissions = $permissions->merge(Permission::where('slug', 'like', 'edit_%')->get());
            } elseif ($pattern === 'delete_*') {
                $permissions = $permissions->merge(Permission::where('slug', 'like', 'delete_%')->get());
            } elseif ($pattern === 'approve_*') {
                $permissions = $permissions->merge(Permission::where('slug', 'like', 'approve_%')->get());
            } elseif ($pattern === 'export_*') {
                $permissions = $permissions->merge(Permission::where('slug', 'like', 'export_%')->get());
            } elseif (str_starts_with($pattern, '*_')) {
                $resource = substr($pattern, 2);
                $permissions = $permissions->merge(Permission::where('slug', 'like', '%_' . $resource)->get());
            } else {
                $permission = Permission::where('slug', $pattern)->first();
                if ($permission) {
                    $permissions->push($permission);
                }
            }
        }

        foreach ($exclude as $excludePattern) {
            if (str_contains($excludePattern, '*')) {
                $pattern = str_replace('*', '%', $excludePattern);
                $permissions = $permissions->reject(function ($permission) use ($pattern) {
                    return \Illuminate\Support\Str::is($pattern, $permission->slug);
                });
            } else {
                $permissions = $permissions->reject(function ($permission) use ($excludePattern) {
                    return $permission->slug === $excludePattern;
                });
            }
        }

        $role->permissions()->syncWithoutDetaching($permissions->unique('id')->pluck('id'));
    }
}
