<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration : Peupler Système ACL de Permissions Flexibles
 *
 * Cette migration :
 * 1. Extrait ressources uniques des permissions existantes
 * 2. Extrait actions uniques
 * 3. Génère automatiquement la matrice d'applicabilité (acl_resource_actions)
 * 4. Ajoute colonnes resource_id et action_id à permissions (si pas déjà là)
 * 5. Mappe les permissions existantes aux nouvelles tables ACL
 * 6. Supprime colonnes resource et action (Option B - Architecture Pure)
 *
 * TABLES UTILISÉES :
 * - acl_resources (au lieu de 'resources' qui est utilisé pour PMBOK RH)
 * - actions
 * - acl_resource_actions
 *
 * INTELLIGENCE :
 * - Détection automatique des actions applicables par ressource
 * - Évite création de combinaisons absurdes (ex: archive_users)
 * - Réduit ~54% du nombre de permissions potentielles
 *
 * DOCUMENTATION : docs/ARCHITECTURE_EVOLUTION_PERMISSIONS_FLEXIBLES.md
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        echo "\n╔════════════════════════════════════════════════════════════════╗\n";
        echo "║  MIGRATION : Peupler Système de Permissions Flexibles        ║\n";
        echo "╚════════════════════════════════════════════════════════════════╝\n\n";

        // 1. Extraire ressources uniques de permissions existantes
        echo "📊 Étape 1/8 : Extraction des ressources...\n";
        $existingResources = DB::table('permissions')
            ->select('resource')
            ->distinct()
            ->whereNotNull('resource')
            ->pluck('resource');

        echo "   ✓ Ressources trouvées : " . $existingResources->count() . "\n\n";

        // 2. Insérer dans table acl_resources
        echo "📝 Étape 2/8 : Création des ressources ACL...\n";
        $resourceMap = [];
        foreach ($existingResources as $resource) {
            $resourceId = DB::table('acl_resources')->insertGetId([
                'name' => ucfirst(str_replace('_', ' ', $resource)),
                'slug' => $resource,
                'description' => "Ressource {$resource}",
                'model_class' => $this->guessModelClass($resource),
                'icon' => $this->guessIcon($resource),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $resourceMap[$resource] = $resourceId;
            echo "   ✓ {$resource} (ID: {$resourceId})\n";
        }
        echo "\n";

        // 3. Extraire actions uniques
        echo "📊 Étape 3/8 : Extraction des actions...\n";
        $existingActions = DB::table('permissions')
            ->select('action')
            ->distinct()
            ->whereNotNull('action')
            ->pluck('action');

        echo "   ✓ Actions trouvées : " . $existingActions->count() . "\n\n";

        // 4. Insérer dans table actions
        echo "📝 Étape 4/8 : Création des actions...\n";
        $actionMap = [];
        foreach ($existingActions as $action) {
            $actionId = DB::table('actions')->insertGetId([
                'name' => ucfirst($action),
                'slug' => $action,
                'description' => "Action {$action}",
                'verb' => $this->guessVerb($action),
                'color' => $this->guessColor($action),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $actionMap[$action] = $actionId;
            echo "   ✓ {$action} (ID: {$actionId})\n";
        }
        echo "\n";

        // 5. Générer applicabilité resource_actions
        echo "⭐ Étape 5/8 : Génération matrice d'applicabilité...\n";
        $applicabilityCount = 0;

        foreach ($resourceMap as $resourceSlug => $resourceId) {
            // Déterminer quelles actions sont applicables à cette ressource
            $applicableActions = $this->getApplicableActionsForResource($resourceSlug, $actionMap);

            foreach ($applicableActions as $actionSlug) {
                if (isset($actionMap[$actionSlug])) {
                    DB::table('acl_resource_actions')->insert([
                        'resource_id' => $resourceId,
                        'action_id' => $actionMap[$actionSlug],
                        'is_default_enabled' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $applicabilityCount++;
                }
            }

            echo "   ✓ {$resourceSlug} : " . count($applicableActions) . " actions applicables\n";
        }

        echo "\n   ✅ Total applicabilités créées : {$applicabilityCount}\n\n";

        // 6. Ajouter colonnes resource_id et action_id à permissions (si pas déjà là)
        echo "🔧 Étape 6/8 : Vérification colonnes table permissions...\n";

        $hasResourceId = Schema::hasColumn('permissions', 'resource_id');
        $hasActionId = Schema::hasColumn('permissions', 'action_id');
        $hasIsActive = Schema::hasColumn('permissions', 'is_active');

        if (!$hasResourceId || !$hasActionId || !$hasIsActive) {
            Schema::table('permissions', function (Blueprint $table) use ($hasResourceId, $hasActionId, $hasIsActive) {
                if (!$hasResourceId) {
                    $table->foreignId('resource_id')->nullable()->after('slug')->constrained('acl_resources')->onDelete('cascade');
                }
                if (!$hasActionId) {
                    $table->foreignId('action_id')->nullable()->after('resource_id')->constrained('actions')->onDelete('cascade');
                }
                if (!$hasIsActive) {
                    $table->boolean('is_active')->default(true)->after('description');
                }
            });
            echo "   ✓ Colonnes ajoutées\n\n";
        } else {
            echo "   ✓ Colonnes déjà présentes (skip)\n\n";
        }

        // 7. Mettre à jour permissions avec les IDs
        echo "🔄 Étape 7/8 : Mapping permissions existantes...\n";
        $permissions = DB::table('permissions')->get();

        foreach ($permissions as $permission) {
            DB::table('permissions')
                ->where('id', $permission->id)
                ->update([
                    'resource_id' => $resourceMap[$permission->resource] ?? null,
                    'action_id' => $actionMap[$permission->action] ?? null,
                    'is_active' => true,
                    'updated_at' => now(),
                ]);
        }

        echo "   ✓ " . $permissions->count() . " permissions mises à jour\n\n";

        // 8. Supprimer anciennes colonnes resource et action (Option B - Architecture Pure)
        echo "🗑️  Étape 8/8 : Suppression colonnes resource et action...\n";

        $hasResourceCol = Schema::hasColumn('permissions', 'resource');
        $hasActionCol = Schema::hasColumn('permissions', 'action');

        if ($hasResourceCol || $hasActionCol) {
            Schema::table('permissions', function (Blueprint $table) use ($hasResourceCol, $hasActionCol) {
                $colsToDrop = [];
                if ($hasResourceCol) $colsToDrop[] = 'resource';
                if ($hasActionCol) $colsToDrop[] = 'action';

                if (count($colsToDrop) > 0) {
                    $table->dropColumn($colsToDrop);
                }
            });
            echo "   ✓ Colonnes resource et action supprimées\n";
            echo "   ✅ Architecture pure : utilisation exclusive de resource_id et action_id\n\n";
        } else {
            echo "   ✓ Colonnes resource et action déjà supprimées (skip)\n\n";
        }

        echo "╔════════════════════════════════════════════════════════════════╗\n";
        echo "║                  ✅ MIGRATION TERMINÉE                         ║\n";
        echo "║                  Architecture Pure Activée                     ║\n";
        echo "╚════════════════════════════════════════════════════════════════╝\n\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restaurer les colonnes resource et action
        if (!Schema::hasColumn('permissions', 'resource')) {
            Schema::table('permissions', function (Blueprint $table) {
                $table->string('resource', 100)->nullable()->after('slug');
                $table->string('action', 50)->nullable()->after('resource');
            });
        }

        // Vider les tables dans l'ordre
        DB::table('acl_resource_actions')->truncate();
        DB::table('actions')->truncate();
        DB::table('acl_resources')->truncate();

        // Supprimer les nouvelles colonnes si nécessaire
        if (Schema::hasColumn('permissions', 'resource_id')) {
            Schema::table('permissions', function (Blueprint $table) {
                $table->dropForeign(['resource_id']);
                $table->dropForeign(['action_id']);
                $table->dropColumn(['resource_id', 'action_id', 'is_active']);
            });
        }
    }

    /**
     * Deviner le nom de classe du model
     */
    protected function guessModelClass(string $resource): ?string
    {
        $modelMap = [
            'projects' => 'App\\Models\\Project',
            'tasks' => 'App\\Models\\Task',
            'users' => 'App\\Models\\User',
            'organizations' => 'App\\Models\\Organization',
            'portfolios' => 'App\\Models\\Portfolio',
            'programs' => 'App\\Models\\Program',
            'deliverables' => 'App\\Models\\Deliverable',
            'budgets' => 'App\\Models\\Budget',
            'risks' => 'App\\Models\\Risk',
            'issues' => 'App\\Models\\Issue',
            'milestones' => 'App\\Models\\Milestone',
            'phases' => 'App\\Models\\Phase',
            'stakeholders' => 'App\\Models\\Stakeholder',
            'roles' => 'App\\Models\\Role',
            'permissions' => 'App\\Models\\Permission',
            'project_phases' => 'App\\Models\\ProjectPhase',
            'wbs_elements' => 'App\\Models\\WbsElement',
            'expenses' => 'App\\Models\\Expense',
            'documents' => 'App\\Models\\Document',
            'reports' => 'App\\Models\\Report',
            'resources' => 'App\\Models\\Resource',
            'teams' => 'App\\Models\\Team',
            'team_members' => 'App\\Models\\TeamMember',
            'schedules' => 'App\\Models\\Schedule',
            'earned_value_metrics' => 'App\\Models\\EarnedValueMetric',
            'stakeholder_engagement' => 'App\\Models\\StakeholderEngagement',
            'risk_responses' => 'App\\Models\\RiskResponse',
            'change_requests' => 'App\\Models\\ChangeRequest',
            'quality_audits' => 'App\\Models\\QualityAudit',
            'quality_metrics' => 'App\\Models\\QualityMetric',
            'lessons_learned' => 'App\\Models\\LessonLearned',
            'communications' => 'App\\Models\\Communication',
            'meetings' => 'App\\Models\\Meeting',
            'meeting_attendees' => 'App\\Models\\MeetingAttendee',
            'document_approvals' => 'App\\Models\\DocumentApproval',
            'vendors' => 'App\\Models\\Vendor',
            'procurements' => 'App\\Models\\Procurement',
            'project_status_reports' => 'App\\Models\\ProjectStatusReport',
            'kpis' => 'App\\Models\\Kpi',
            'user_roles' => 'App\\Models\\UserRole',
            'api_keys' => 'App\\Models\\ApiKey',
        ];

        return $modelMap[$resource] ?? null;
    }

    /**
     * Deviner l'icône pour une ressource
     */
    protected function guessIcon(string $resource): string
    {
        $iconMap = [
            'projects' => 'folder',
            'tasks' => 'check-square',
            'users' => 'users',
            'organizations' => 'building',
            'portfolios' => 'briefcase',
            'programs' => 'layers',
            'deliverables' => 'file-text',
            'budgets' => 'dollar-sign',
            'risks' => 'alert-triangle',
            'issues' => 'alert-circle',
            'milestones' => 'flag',
            'phases' => 'calendar',
            'stakeholders' => 'user-check',
            'roles' => 'shield',
            'permissions' => 'lock',
            'documents' => 'file',
            'reports' => 'bar-chart',
            'teams' => 'users',
            'meetings' => 'video',
            'quality_audits' => 'check-circle',
            'change_requests' => 'git-pull-request',
            'vendors' => 'truck',
            'procurements' => 'shopping-cart',
            'kpis' => 'target',
            'api_keys' => 'key',
        ];

        return $iconMap[$resource] ?? 'box';
    }

    /**
     * Deviner le verbe HTTP pour une action
     */
    protected function guessVerb(string $action): string
    {
        $verbMap = [
            'view' => 'read',
            'create' => 'write',
            'edit' => 'write',
            'delete' => 'delete',
            'approve' => 'write',
            'export' => 'read',
            'archive' => 'write',
            'restore' => 'write',
            'duplicate' => 'write',
            'transfer' => 'write',
        ];

        return $verbMap[$action] ?? 'read';
    }

    /**
     * Deviner la couleur pour une action
     */
    protected function guessColor(string $action): string
    {
        $colorMap = [
            'view' => '#4CAF50',      // Vert
            'create' => '#2196F3',    // Bleu
            'edit' => '#FF9800',      // Orange
            'delete' => '#F44336',    // Rouge
            'approve' => '#9C27B0',   // Violet
            'export' => '#00BCD4',    // Cyan
            'archive' => '#607D8B',   // Gris bleu
            'restore' => '#8BC34A',   // Vert clair
            'duplicate' => '#3F51B5', // Indigo
            'transfer' => '#FFC107',  // Ambre
        ];

        return $colorMap[$action] ?? '#757575';  // Gris par défaut
    }

    /**
     * Déterminer quelles actions sont applicables pour une ressource
     */
    protected function getApplicableActionsForResource(string $resourceSlug, array $actionMap): array
    {
        // Actions de base applicables à TOUTES les ressources
        $baseActions = ['view', 'create', 'edit', 'delete'];

        // Règles spécifiques par type de ressource
        $specificRules = [
            // PMBOK Core
            'projects' => [...$baseActions, 'approve', 'export', 'archive'],
            'tasks' => [...$baseActions, 'approve', 'transfer', 'duplicate'],
            'deliverables' => [...$baseActions, 'approve', 'export', 'archive'],
            'documents' => [...$baseActions, 'approve', 'export', 'archive'],

            // Ressources humaines - PAS d'archive ni duplicate
            'users' => ['view', 'create', 'edit', 'delete', 'export'],
            'stakeholders' => [...$baseActions, 'export'],
            'teams' => [...$baseActions, 'export'],
            'team_members' => [...$baseActions],

            // Ressources organisationnelles
            'organizations' => ['view', 'create', 'edit', 'export'],
            'portfolios' => [...$baseActions, 'export'],
            'programs' => [...$baseActions, 'export', 'archive'],

            // Ressources financières
            'budgets' => [...$baseActions, 'approve', 'export'],
            'expenses' => [...$baseActions, 'approve', 'export'],
            'procurements' => [...$baseActions, 'approve', 'export'],

            // Ressources risques
            'risks' => [...$baseActions, 'approve', 'export', 'archive'],
            'issues' => [...$baseActions, 'approve', 'export', 'archive'],
            'risk_responses' => [...$baseActions, 'approve'],
            'change_requests' => [...$baseActions, 'approve', 'export'],

            // Ressources planification
            'milestones' => [...$baseActions, 'export'],
            'phases' => [...$baseActions, 'duplicate'],
            'project_phases' => [...$baseActions, 'duplicate'],
            'schedules' => [...$baseActions, 'export', 'duplicate'],
            'wbs_elements' => [...$baseActions],

            // Ressources qualité
            'quality_metrics' => [...$baseActions, 'approve', 'export'],
            'quality_audits' => [...$baseActions, 'approve', 'export', 'archive'],
            'lessons_learned' => [...$baseActions, 'export'],

            // Ressources communication
            'communications' => [...$baseActions, 'archive'],
            'meetings' => [...$baseActions, 'export'],
            'meeting_attendees' => [...$baseActions],
            'document_approvals' => [...$baseActions, 'approve'],

            // Ressources reporting
            'reports' => [...$baseActions, 'export', 'duplicate'],
            'project_status_reports' => [...$baseActions, 'export'],
            'kpis' => [...$baseActions, 'export'],
            'earned_value_metrics' => [...$baseActions, 'export'],

            // Ressources fournisseurs
            'vendors' => [...$baseActions, 'export'],
            'resource_allocations' => [...$baseActions, 'export'],

            // Stakeholder engagement
            'stakeholder_engagement' => [...$baseActions, 'export'],

            // Permissions et rôles
            'roles' => [...$baseActions, 'duplicate'],
            'permissions' => ['view', 'create', 'edit', 'delete'],
            'user_roles' => [...$baseActions],

            // API
            'api_keys' => [...$baseActions],
        ];

        // Retourner les actions spécifiques si définies, sinon actions de base
        $applicable = $specificRules[$resourceSlug] ?? $baseActions;

        // Filtrer pour ne retourner que les actions qui existent vraiment
        return array_filter($applicable, function($action) use ($actionMap) {
            return isset($actionMap[$action]);
        });
    }
};
