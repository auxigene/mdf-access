<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info("Création des permissions...");

        // Définition des permissions par ressource
        $permissions = [
            // Portfolios
            ['resource' => 'portfolios', 'action' => 'view', 'name' => 'Voir les portfolios', 'slug' => 'view_portfolios'],
            ['resource' => 'portfolios', 'action' => 'create', 'name' => 'Créer des portfolios', 'slug' => 'create_portfolios'],
            ['resource' => 'portfolios', 'action' => 'edit', 'name' => 'Modifier des portfolios', 'slug' => 'edit_portfolios'],
            ['resource' => 'portfolios', 'action' => 'delete', 'name' => 'Supprimer des portfolios', 'slug' => 'delete_portfolios'],

            // Programs
            ['resource' => 'programs', 'action' => 'view', 'name' => 'Voir les programmes', 'slug' => 'view_programs'],
            ['resource' => 'programs', 'action' => 'create', 'name' => 'Créer des programmes', 'slug' => 'create_programs'],
            ['resource' => 'programs', 'action' => 'edit', 'name' => 'Modifier des programmes', 'slug' => 'edit_programs'],
            ['resource' => 'programs', 'action' => 'delete', 'name' => 'Supprimer des programmes', 'slug' => 'delete_programs'],

            // Projects
            ['resource' => 'projects', 'action' => 'view', 'name' => 'Voir les projets', 'slug' => 'view_projects'],
            ['resource' => 'projects', 'action' => 'create', 'name' => 'Créer des projets', 'slug' => 'create_projects'],
            ['resource' => 'projects', 'action' => 'edit', 'name' => 'Modifier des projets', 'slug' => 'edit_projects'],
            ['resource' => 'projects', 'action' => 'delete', 'name' => 'Supprimer des projets', 'slug' => 'delete_projects'],
            ['resource' => 'projects', 'action' => 'approve', 'name' => 'Approuver des projets', 'slug' => 'approve_projects'],

            // Tasks
            ['resource' => 'tasks', 'action' => 'view', 'name' => 'Voir les tâches', 'slug' => 'view_tasks'],
            ['resource' => 'tasks', 'action' => 'create', 'name' => 'Créer des tâches', 'slug' => 'create_tasks'],
            ['resource' => 'tasks', 'action' => 'edit', 'name' => 'Modifier des tâches', 'slug' => 'edit_tasks'],
            ['resource' => 'tasks', 'action' => 'delete', 'name' => 'Supprimer des tâches', 'slug' => 'delete_tasks'],

            // Budgets
            ['resource' => 'budgets', 'action' => 'view', 'name' => 'Voir les budgets', 'slug' => 'view_budgets'],
            ['resource' => 'budgets', 'action' => 'create', 'name' => 'Créer des budgets', 'slug' => 'create_budgets'],
            ['resource' => 'budgets', 'action' => 'edit', 'name' => 'Modifier des budgets', 'slug' => 'edit_budgets'],
            ['resource' => 'budgets', 'action' => 'delete', 'name' => 'Supprimer des budgets', 'slug' => 'delete_budgets'],
            ['resource' => 'budgets', 'action' => 'approve', 'name' => 'Approuver des budgets', 'slug' => 'approve_budgets'],

            // Expenses
            ['resource' => 'expenses', 'action' => 'view', 'name' => 'Voir les dépenses', 'slug' => 'view_expenses'],
            ['resource' => 'expenses', 'action' => 'create', 'name' => 'Créer des dépenses', 'slug' => 'create_expenses'],
            ['resource' => 'expenses', 'action' => 'edit', 'name' => 'Modifier des dépenses', 'slug' => 'edit_expenses'],
            ['resource' => 'expenses', 'action' => 'delete', 'name' => 'Supprimer des dépenses', 'slug' => 'delete_expenses'],

            // Risks
            ['resource' => 'risks', 'action' => 'view', 'name' => 'Voir les risques', 'slug' => 'view_risks'],
            ['resource' => 'risks', 'action' => 'create', 'name' => 'Créer des risques', 'slug' => 'create_risks'],
            ['resource' => 'risks', 'action' => 'edit', 'name' => 'Modifier des risques', 'slug' => 'edit_risks'],
            ['resource' => 'risks', 'action' => 'delete', 'name' => 'Supprimer des risques', 'slug' => 'delete_risks'],

            // Issues
            ['resource' => 'issues', 'action' => 'view', 'name' => 'Voir les problèmes', 'slug' => 'view_issues'],
            ['resource' => 'issues', 'action' => 'create', 'name' => 'Créer des problèmes', 'slug' => 'create_issues'],
            ['resource' => 'issues', 'action' => 'edit', 'name' => 'Modifier des problèmes', 'slug' => 'edit_issues'],
            ['resource' => 'issues', 'action' => 'delete', 'name' => 'Supprimer des problèmes', 'slug' => 'delete_issues'],

            // Documents
            ['resource' => 'documents', 'action' => 'view', 'name' => 'Voir les documents', 'slug' => 'view_documents'],
            ['resource' => 'documents', 'action' => 'create', 'name' => 'Créer des documents', 'slug' => 'create_documents'],
            ['resource' => 'documents', 'action' => 'edit', 'name' => 'Modifier des documents', 'slug' => 'edit_documents'],
            ['resource' => 'documents', 'action' => 'delete', 'name' => 'Supprimer des documents', 'slug' => 'delete_documents'],
            ['resource' => 'documents', 'action' => 'approve', 'name' => 'Approuver des documents', 'slug' => 'approve_documents'],

            // Reports
            ['resource' => 'reports', 'action' => 'view', 'name' => 'Voir les rapports', 'slug' => 'view_reports'],
            ['resource' => 'reports', 'action' => 'create', 'name' => 'Créer des rapports', 'slug' => 'create_reports'],
            ['resource' => 'reports', 'action' => 'export', 'name' => 'Exporter des rapports', 'slug' => 'export_reports'],

            // Resources (équipes/ressources)
            ['resource' => 'resources', 'action' => 'view', 'name' => 'Voir les ressources', 'slug' => 'view_resources'],
            ['resource' => 'resources', 'action' => 'create', 'name' => 'Créer des ressources', 'slug' => 'create_resources'],
            ['resource' => 'resources', 'action' => 'edit', 'name' => 'Modifier des ressources', 'slug' => 'edit_resources'],
            ['resource' => 'resources', 'action' => 'delete', 'name' => 'Supprimer des ressources', 'slug' => 'delete_resources'],

            // Users
            ['resource' => 'users', 'action' => 'view', 'name' => 'Voir les utilisateurs', 'slug' => 'view_users'],
            ['resource' => 'users', 'action' => 'create', 'name' => 'Créer des utilisateurs', 'slug' => 'create_users'],
            ['resource' => 'users', 'action' => 'edit', 'name' => 'Modifier des utilisateurs', 'slug' => 'edit_users'],
            ['resource' => 'users', 'action' => 'delete', 'name' => 'Supprimer des utilisateurs', 'slug' => 'delete_users'],

            // Organizations
            ['resource' => 'organizations', 'action' => 'view', 'name' => 'Voir les organisations', 'slug' => 'view_organizations'],
            ['resource' => 'organizations', 'action' => 'create', 'name' => 'Créer des organisations', 'slug' => 'create_organizations'],
            ['resource' => 'organizations', 'action' => 'edit', 'name' => 'Modifier des organisations', 'slug' => 'edit_organizations'],
            ['resource' => 'organizations', 'action' => 'delete', 'name' => 'Supprimer des organisations', 'slug' => 'delete_organizations'],

            // Project Phases
            ['resource' => 'project_phases', 'action' => 'view', 'name' => 'Voir les phases de projet', 'slug' => 'view_project_phases'],
            ['resource' => 'project_phases', 'action' => 'create', 'name' => 'Créer des phases de projet', 'slug' => 'create_project_phases'],
            ['resource' => 'project_phases', 'action' => 'edit', 'name' => 'Modifier des phases de projet', 'slug' => 'edit_project_phases'],
            ['resource' => 'project_phases', 'action' => 'delete', 'name' => 'Supprimer des phases de projet', 'slug' => 'delete_project_phases'],

            // WBS Elements
            ['resource' => 'wbs_elements', 'action' => 'view', 'name' => 'Voir les éléments WBS', 'slug' => 'view_wbs_elements'],
            ['resource' => 'wbs_elements', 'action' => 'create', 'name' => 'Créer des éléments WBS', 'slug' => 'create_wbs_elements'],
            ['resource' => 'wbs_elements', 'action' => 'edit', 'name' => 'Modifier des éléments WBS', 'slug' => 'edit_wbs_elements'],
            ['resource' => 'wbs_elements', 'action' => 'delete', 'name' => 'Supprimer des éléments WBS', 'slug' => 'delete_wbs_elements'],

            // Deliverables
            ['resource' => 'deliverables', 'action' => 'view', 'name' => 'Voir les livrables', 'slug' => 'view_deliverables'],
            ['resource' => 'deliverables', 'action' => 'create', 'name' => 'Créer des livrables', 'slug' => 'create_deliverables'],
            ['resource' => 'deliverables', 'action' => 'edit', 'name' => 'Modifier des livrables', 'slug' => 'edit_deliverables'],
            ['resource' => 'deliverables', 'action' => 'delete', 'name' => 'Supprimer des livrables', 'slug' => 'delete_deliverables'],
            ['resource' => 'deliverables', 'action' => 'approve', 'name' => 'Approuver des livrables', 'slug' => 'approve_deliverables'],

            // Resource Allocations
            ['resource' => 'resource_allocations', 'action' => 'view', 'name' => 'Voir les allocations de ressources', 'slug' => 'view_resource_allocations'],
            ['resource' => 'resource_allocations', 'action' => 'create', 'name' => 'Créer des allocations de ressources', 'slug' => 'create_resource_allocations'],
            ['resource' => 'resource_allocations', 'action' => 'edit', 'name' => 'Modifier des allocations de ressources', 'slug' => 'edit_resource_allocations'],
            ['resource' => 'resource_allocations', 'action' => 'delete', 'name' => 'Supprimer des allocations de ressources', 'slug' => 'delete_resource_allocations'],

            // Teams
            ['resource' => 'teams', 'action' => 'view', 'name' => 'Voir les équipes', 'slug' => 'view_teams'],
            ['resource' => 'teams', 'action' => 'create', 'name' => 'Créer des équipes', 'slug' => 'create_teams'],
            ['resource' => 'teams', 'action' => 'edit', 'name' => 'Modifier des équipes', 'slug' => 'edit_teams'],
            ['resource' => 'teams', 'action' => 'delete', 'name' => 'Supprimer des équipes', 'slug' => 'delete_teams'],

            // Team Members
            ['resource' => 'team_members', 'action' => 'view', 'name' => 'Voir les membres d\'équipe', 'slug' => 'view_team_members'],
            ['resource' => 'team_members', 'action' => 'create', 'name' => 'Ajouter des membres d\'équipe', 'slug' => 'create_team_members'],
            ['resource' => 'team_members', 'action' => 'edit', 'name' => 'Modifier des membres d\'équipe', 'slug' => 'edit_team_members'],
            ['resource' => 'team_members', 'action' => 'delete', 'name' => 'Retirer des membres d\'équipe', 'slug' => 'delete_team_members'],

            // Milestones
            ['resource' => 'milestones', 'action' => 'view', 'name' => 'Voir les jalons', 'slug' => 'view_milestones'],
            ['resource' => 'milestones', 'action' => 'create', 'name' => 'Créer des jalons', 'slug' => 'create_milestones'],
            ['resource' => 'milestones', 'action' => 'edit', 'name' => 'Modifier des jalons', 'slug' => 'edit_milestones'],
            ['resource' => 'milestones', 'action' => 'delete', 'name' => 'Supprimer des jalons', 'slug' => 'delete_milestones'],

            // Schedules
            ['resource' => 'schedules', 'action' => 'view', 'name' => 'Voir les plannings', 'slug' => 'view_schedules'],
            ['resource' => 'schedules', 'action' => 'create', 'name' => 'Créer des plannings', 'slug' => 'create_schedules'],
            ['resource' => 'schedules', 'action' => 'edit', 'name' => 'Modifier des plannings', 'slug' => 'edit_schedules'],
            ['resource' => 'schedules', 'action' => 'delete', 'name' => 'Supprimer des plannings', 'slug' => 'delete_schedules'],

            // Earned Value Metrics
            ['resource' => 'earned_value_metrics', 'action' => 'view', 'name' => 'Voir les métriques de valeur acquise', 'slug' => 'view_earned_value_metrics'],
            ['resource' => 'earned_value_metrics', 'action' => 'create', 'name' => 'Créer des métriques de valeur acquise', 'slug' => 'create_earned_value_metrics'],
            ['resource' => 'earned_value_metrics', 'action' => 'edit', 'name' => 'Modifier des métriques de valeur acquise', 'slug' => 'edit_earned_value_metrics'],
            ['resource' => 'earned_value_metrics', 'action' => 'delete', 'name' => 'Supprimer des métriques de valeur acquise', 'slug' => 'delete_earned_value_metrics'],

            // Stakeholders
            ['resource' => 'stakeholders', 'action' => 'view', 'name' => 'Voir les parties prenantes', 'slug' => 'view_stakeholders'],
            ['resource' => 'stakeholders', 'action' => 'create', 'name' => 'Créer des parties prenantes', 'slug' => 'create_stakeholders'],
            ['resource' => 'stakeholders', 'action' => 'edit', 'name' => 'Modifier des parties prenantes', 'slug' => 'edit_stakeholders'],
            ['resource' => 'stakeholders', 'action' => 'delete', 'name' => 'Supprimer des parties prenantes', 'slug' => 'delete_stakeholders'],

            // Stakeholder Engagement
            ['resource' => 'stakeholder_engagement', 'action' => 'view', 'name' => 'Voir l\'engagement des parties prenantes', 'slug' => 'view_stakeholder_engagement'],
            ['resource' => 'stakeholder_engagement', 'action' => 'create', 'name' => 'Créer des engagements de parties prenantes', 'slug' => 'create_stakeholder_engagement'],
            ['resource' => 'stakeholder_engagement', 'action' => 'edit', 'name' => 'Modifier l\'engagement des parties prenantes', 'slug' => 'edit_stakeholder_engagement'],
            ['resource' => 'stakeholder_engagement', 'action' => 'delete', 'name' => 'Supprimer l\'engagement des parties prenantes', 'slug' => 'delete_stakeholder_engagement'],

            // Risk Responses
            ['resource' => 'risk_responses', 'action' => 'view', 'name' => 'Voir les réponses aux risques', 'slug' => 'view_risk_responses'],
            ['resource' => 'risk_responses', 'action' => 'create', 'name' => 'Créer des réponses aux risques', 'slug' => 'create_risk_responses'],
            ['resource' => 'risk_responses', 'action' => 'edit', 'name' => 'Modifier des réponses aux risques', 'slug' => 'edit_risk_responses'],
            ['resource' => 'risk_responses', 'action' => 'delete', 'name' => 'Supprimer des réponses aux risques', 'slug' => 'delete_risk_responses'],

            // Change Requests
            ['resource' => 'change_requests', 'action' => 'view', 'name' => 'Voir les demandes de changement', 'slug' => 'view_change_requests'],
            ['resource' => 'change_requests', 'action' => 'create', 'name' => 'Créer des demandes de changement', 'slug' => 'create_change_requests'],
            ['resource' => 'change_requests', 'action' => 'edit', 'name' => 'Modifier des demandes de changement', 'slug' => 'edit_change_requests'],
            ['resource' => 'change_requests', 'action' => 'delete', 'name' => 'Supprimer des demandes de changement', 'slug' => 'delete_change_requests'],
            ['resource' => 'change_requests', 'action' => 'approve', 'name' => 'Approuver des demandes de changement', 'slug' => 'approve_change_requests'],

            // Quality Audits
            ['resource' => 'quality_audits', 'action' => 'view', 'name' => 'Voir les audits qualité', 'slug' => 'view_quality_audits'],
            ['resource' => 'quality_audits', 'action' => 'create', 'name' => 'Créer des audits qualité', 'slug' => 'create_quality_audits'],
            ['resource' => 'quality_audits', 'action' => 'edit', 'name' => 'Modifier des audits qualité', 'slug' => 'edit_quality_audits'],
            ['resource' => 'quality_audits', 'action' => 'delete', 'name' => 'Supprimer des audits qualité', 'slug' => 'delete_quality_audits'],

            // Quality Metrics
            ['resource' => 'quality_metrics', 'action' => 'view', 'name' => 'Voir les métriques qualité', 'slug' => 'view_quality_metrics'],
            ['resource' => 'quality_metrics', 'action' => 'create', 'name' => 'Créer des métriques qualité', 'slug' => 'create_quality_metrics'],
            ['resource' => 'quality_metrics', 'action' => 'edit', 'name' => 'Modifier des métriques qualité', 'slug' => 'edit_quality_metrics'],
            ['resource' => 'quality_metrics', 'action' => 'delete', 'name' => 'Supprimer des métriques qualité', 'slug' => 'delete_quality_metrics'],

            // Lessons Learned
            ['resource' => 'lessons_learned', 'action' => 'view', 'name' => 'Voir les leçons apprises', 'slug' => 'view_lessons_learned'],
            ['resource' => 'lessons_learned', 'action' => 'create', 'name' => 'Créer des leçons apprises', 'slug' => 'create_lessons_learned'],
            ['resource' => 'lessons_learned', 'action' => 'edit', 'name' => 'Modifier des leçons apprises', 'slug' => 'edit_lessons_learned'],
            ['resource' => 'lessons_learned', 'action' => 'delete', 'name' => 'Supprimer des leçons apprises', 'slug' => 'delete_lessons_learned'],

            // Communications
            ['resource' => 'communications', 'action' => 'view', 'name' => 'Voir les communications', 'slug' => 'view_communications'],
            ['resource' => 'communications', 'action' => 'create', 'name' => 'Créer des communications', 'slug' => 'create_communications'],
            ['resource' => 'communications', 'action' => 'edit', 'name' => 'Modifier des communications', 'slug' => 'edit_communications'],
            ['resource' => 'communications', 'action' => 'delete', 'name' => 'Supprimer des communications', 'slug' => 'delete_communications'],

            // Meetings
            ['resource' => 'meetings', 'action' => 'view', 'name' => 'Voir les réunions', 'slug' => 'view_meetings'],
            ['resource' => 'meetings', 'action' => 'create', 'name' => 'Créer des réunions', 'slug' => 'create_meetings'],
            ['resource' => 'meetings', 'action' => 'edit', 'name' => 'Modifier des réunions', 'slug' => 'edit_meetings'],
            ['resource' => 'meetings', 'action' => 'delete', 'name' => 'Supprimer des réunions', 'slug' => 'delete_meetings'],

            // Meeting Attendees
            ['resource' => 'meeting_attendees', 'action' => 'view', 'name' => 'Voir les participants aux réunions', 'slug' => 'view_meeting_attendees'],
            ['resource' => 'meeting_attendees', 'action' => 'create', 'name' => 'Ajouter des participants aux réunions', 'slug' => 'create_meeting_attendees'],
            ['resource' => 'meeting_attendees', 'action' => 'edit', 'name' => 'Modifier des participants aux réunions', 'slug' => 'edit_meeting_attendees'],
            ['resource' => 'meeting_attendees', 'action' => 'delete', 'name' => 'Retirer des participants aux réunions', 'slug' => 'delete_meeting_attendees'],

            // Document Approvals
            ['resource' => 'document_approvals', 'action' => 'view', 'name' => 'Voir les approbations de documents', 'slug' => 'view_document_approvals'],
            ['resource' => 'document_approvals', 'action' => 'create', 'name' => 'Créer des approbations de documents', 'slug' => 'create_document_approvals'],
            ['resource' => 'document_approvals', 'action' => 'edit', 'name' => 'Modifier des approbations de documents', 'slug' => 'edit_document_approvals'],
            ['resource' => 'document_approvals', 'action' => 'delete', 'name' => 'Supprimer des approbations de documents', 'slug' => 'delete_document_approvals'],
            ['resource' => 'document_approvals', 'action' => 'approve', 'name' => 'Approuver des documents', 'slug' => 'approve_document_approvals'],

            // Vendors
            ['resource' => 'vendors', 'action' => 'view', 'name' => 'Voir les fournisseurs', 'slug' => 'view_vendors'],
            ['resource' => 'vendors', 'action' => 'create', 'name' => 'Créer des fournisseurs', 'slug' => 'create_vendors'],
            ['resource' => 'vendors', 'action' => 'edit', 'name' => 'Modifier des fournisseurs', 'slug' => 'edit_vendors'],
            ['resource' => 'vendors', 'action' => 'delete', 'name' => 'Supprimer des fournisseurs', 'slug' => 'delete_vendors'],

            // Procurements
            ['resource' => 'procurements', 'action' => 'view', 'name' => 'Voir les achats', 'slug' => 'view_procurements'],
            ['resource' => 'procurements', 'action' => 'create', 'name' => 'Créer des achats', 'slug' => 'create_procurements'],
            ['resource' => 'procurements', 'action' => 'edit', 'name' => 'Modifier des achats', 'slug' => 'edit_procurements'],
            ['resource' => 'procurements', 'action' => 'delete', 'name' => 'Supprimer des achats', 'slug' => 'delete_procurements'],
            ['resource' => 'procurements', 'action' => 'approve', 'name' => 'Approuver des achats', 'slug' => 'approve_procurements'],

            // Project Status Reports
            ['resource' => 'project_status_reports', 'action' => 'view', 'name' => 'Voir les rapports de statut', 'slug' => 'view_project_status_reports'],
            ['resource' => 'project_status_reports', 'action' => 'create', 'name' => 'Créer des rapports de statut', 'slug' => 'create_project_status_reports'],
            ['resource' => 'project_status_reports', 'action' => 'edit', 'name' => 'Modifier des rapports de statut', 'slug' => 'edit_project_status_reports'],
            ['resource' => 'project_status_reports', 'action' => 'delete', 'name' => 'Supprimer des rapports de statut', 'slug' => 'delete_project_status_reports'],

            // KPIs
            ['resource' => 'kpis', 'action' => 'view', 'name' => 'Voir les KPIs', 'slug' => 'view_kpis'],
            ['resource' => 'kpis', 'action' => 'create', 'name' => 'Créer des KPIs', 'slug' => 'create_kpis'],
            ['resource' => 'kpis', 'action' => 'edit', 'name' => 'Modifier des KPIs', 'slug' => 'edit_kpis'],
            ['resource' => 'kpis', 'action' => 'delete', 'name' => 'Supprimer des KPIs', 'slug' => 'delete_kpis'],

            // Roles (système de permissions)
            ['resource' => 'roles', 'action' => 'view', 'name' => 'Voir les rôles', 'slug' => 'view_roles'],
            ['resource' => 'roles', 'action' => 'create', 'name' => 'Créer des rôles', 'slug' => 'create_roles'],
            ['resource' => 'roles', 'action' => 'edit', 'name' => 'Modifier des rôles', 'slug' => 'edit_roles'],
            ['resource' => 'roles', 'action' => 'delete', 'name' => 'Supprimer des rôles', 'slug' => 'delete_roles'],

            // Permissions (système de permissions)
            ['resource' => 'permissions', 'action' => 'view', 'name' => 'Voir les permissions', 'slug' => 'view_permissions'],
            ['resource' => 'permissions', 'action' => 'create', 'name' => 'Créer des permissions', 'slug' => 'create_permissions'],
            ['resource' => 'permissions', 'action' => 'edit', 'name' => 'Modifier des permissions', 'slug' => 'edit_permissions'],
            ['resource' => 'permissions', 'action' => 'delete', 'name' => 'Supprimer des permissions', 'slug' => 'delete_permissions'],

            // User Roles (attribution des rôles)
            ['resource' => 'user_roles', 'action' => 'view', 'name' => 'Voir les attributions de rôles', 'slug' => 'view_user_roles'],
            ['resource' => 'user_roles', 'action' => 'create', 'name' => 'Attribuer des rôles aux utilisateurs', 'slug' => 'create_user_roles'],
            ['resource' => 'user_roles', 'action' => 'edit', 'name' => 'Modifier les attributions de rôles', 'slug' => 'edit_user_roles'],
            ['resource' => 'user_roles', 'action' => 'delete', 'name' => 'Retirer des rôles aux utilisateurs', 'slug' => 'delete_user_roles'],

            // API Keys
            ['resource' => 'api_keys', 'action' => 'view', 'name' => 'Voir les clés API', 'slug' => 'view_api_keys'],
            ['resource' => 'api_keys', 'action' => 'create', 'name' => 'Créer des clés API', 'slug' => 'create_api_keys'],
            ['resource' => 'api_keys', 'action' => 'edit', 'name' => 'Modifier des clés API', 'slug' => 'edit_api_keys'],
            ['resource' => 'api_keys', 'action' => 'delete', 'name' => 'Supprimer des clés API', 'slug' => 'delete_api_keys'],
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
            $this->command->info("Permissions créées avec succès!");
            $this->command->info("✓ Permissions créées: {$created}");
            $this->command->warn("⊘ Permissions ignorées: {$skipped}");
            $this->command->info("========================================");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("Erreur lors de la création des permissions: " . $e->getMessage());
        }
    }
}
