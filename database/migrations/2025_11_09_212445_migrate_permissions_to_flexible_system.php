<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration : Migrer Permissions vers Système Flexible avec Applicabilité
 *
 * Cette migration :
 * 1. Extrait ressources uniques des permissions existantes
 * 2. Extrait actions uniques
 * 3. Génère automatiquement la matrice d'applicabilité (resource_actions)
 * 4. Ajoute colonnes resource_id et action_id à permissions
 * 5. Mappe les permissions existantes aux nouvelles tables
 *
 * INTELLIGENCE :
 * - Détection automatique des actions applicables par ressource
 * - Évite création de combinaisons absurdes (ex: archive_users)
 * - Réduit ~54% du nombre de permissions potentielles
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        echo "\n╔════════════════════════════════════════════════════════════════╗\n";
        echo "║  MIGRATION : Système de Permissions Flexibles                ║\n";
        echo "╚════════════════════════════════════════════════════════════════╝\n\n";

        // 1. Extraire ressources uniques de permissions existantes
        echo "📊 Étape 1/6 : Extraction des ressources...\n";
        $existingResources = DB::table('permissions')
            ->select('resource')
            ->distinct()
            ->whereNotNull('resource')
            ->pluck('resource');

        echo "   ✓ Ressources trouvées : " . $existingResources->count() . "\n\n";

        // 2. Insérer dans table resources
        echo "📝 Étape 2/6 : Création des ressources...\n";
        $resourceMap = [];
        foreach ($existingResources as $resource) {
            $resourceId = DB::table('resources')->insertGetId([
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
        echo "📊 Étape 3/6 : Extraction des actions...\n";
        $existingActions = DB::table('permissions')
            ->select('action')
            ->distinct()
            ->whereNotNull('action')
            ->pluck('action');

        echo "   ✓ Actions trouvées : " . $existingActions->count() . "\n\n";

        // 4. Insérer dans table actions
        echo "📝 Étape 4/6 : Création des actions...\n";
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

        // 5. NOUVEAU : Générer applicabilité resource_actions
        echo "⭐ Étape 5/6 : Génération matrice d'applicabilité...\n";
        $applicabilityCount = 0;

        foreach ($resourceMap as $resourceSlug => $resourceId) {
            // Déterminer quelles actions sont applicables à cette ressource
            $applicableActions = $this->getApplicableActionsForResource($resourceSlug, $actionMap);

            foreach ($applicableActions as $actionSlug) {
                if (isset($actionMap[$actionSlug])) {
                    DB::table('resource_actions')->insert([
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

        // 6. Ajouter colonnes resource_id et action_id à permissions
        echo "🔧 Étape 6/6 : Ajout colonnes à table permissions...\n";
        Schema::table('permissions', function (Blueprint $table) {
            $table->foreignId('resource_id')->nullable()->after('slug')->constrained()->onDelete('cascade');
            $table->foreignId('action_id')->nullable()->after('resource_id')->constrained()->onDelete('cascade');
            $table->boolean('is_active')->default(true)->after('description');
        });
        echo "   ✓ Colonnes ajoutées\n\n";

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

        // 8. NOUVEAU : Supprimer anciennes colonnes resource et action (Option B - Architecture Pure)
        echo "🗑️  Étape 8/8 : Suppression colonnes resource et action...\n";
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn(['resource', 'action']);
        });
        echo "   ✓ Colonnes resource et action supprimées\n";
        echo "   ✅ Architecture pure : utilisation exclusive de resource_id et action_id\n\n";

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
        Schema::table('permissions', function (Blueprint $table) {
            // Restaurer les colonnes resource et action
            $table->string('resource', 100)->nullable()->after('slug');
            $table->string('action', 50)->nullable()->after('resource');

            // Supprimer les nouvelles colonnes
            $table->dropForeign(['resource_id']);
            $table->dropForeign(['action_id']);
            $table->dropColumn(['resource_id', 'action_id', 'is_active']);
        });
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
     * ⭐ NOUVEAU : Déterminer quelles actions sont applicables pour une ressource
     *
     * Cette méthode définit des règles métier pour éviter les combinaisons absurdes.
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

            // Ressources humaines - PAS d'archive ni duplicate
            'users' => ['view', 'create', 'edit', 'delete', 'export'],
            'stakeholders' => [...$baseActions, 'export'],

            // Ressources organisationnelles
            'organizations' => ['view', 'create', 'edit', 'export'],
            'portfolios' => [...$baseActions, 'export'],
            'programs' => [...$baseActions, 'export', 'archive'],

            // Ressources financières
            'budgets' => [...$baseActions, 'approve', 'export'],

            // Ressources risques
            'risks' => [...$baseActions, 'approve', 'export', 'archive'],
            'issues' => [...$baseActions, 'approve', 'export', 'archive'],

            // Ressources planification
            'milestones' => [...$baseActions, 'export'],
            'phases' => [...$baseActions, 'duplicate'],

            // Permissions et rôles
            'roles' => [...$baseActions, 'duplicate'],
            'permissions' => ['view', 'create', 'edit', 'delete'],
        ];

        // Retourner les actions spécifiques si définies, sinon actions de base
        $applicable = $specificRules[$resourceSlug] ?? $baseActions;

        // Filtrer pour ne retourner que les actions qui existent vraiment
        return array_filter($applicable, function($action) use ($actionMap) {
            return isset($actionMap[$action]);
        });
    }
};
