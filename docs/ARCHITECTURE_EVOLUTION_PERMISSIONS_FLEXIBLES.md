# 🔄 Évolution Architecture : Permissions Ultra-Flexibles avec Matrice d'Applicabilité

**Date :** 9 novembre 2025
**Type :** Amélioration architecturale - Permissions dynamiques avec applicabilité
**Priorité :** 📋 MOYENNE (amélioration)
**Impact :** ⭐⭐⭐ ÉLEVÉ
**Version :** 2.0 - Avec matrice d'applicabilité

---

## 🎯 Objectif

Rendre les **ressources** et **actions** dynamiques et configurables via interface admin, au lieu d'être figées dans le code.

---

## ⚠️ Problème : Génération Automatique = Explosion Combinatoire

### Le Défi

Avec un système de génération automatique naïf :

```
Ressources : 39 (toutes les tables PMBOK)
Actions : 10 (view, create, edit, delete, approve, export, archive, restore, duplicate, transfer)

Permissions potentielles = 39 × 10 = 390 permissions
```

**Mais certaines combinaisons n'ont AUCUN sens :**

| Ressource | Action | Sens ? | Exemple |
|-----------|--------|--------|---------|
| `users` | `archive` | ❌ NON | On désactive un user, on ne l'archive pas |
| `deliverables` | `archive` | ✅ OUI | Un livrable peut être archivé |
| `users` | `transfer` | ❌ NON | On ne transfère pas un user |
| `tasks` | `transfer` | ✅ OUI | On peut réaffecter une tâche |
| `organizations` | `duplicate` | ❌ NON | Dupliquer une organisation ? Bizarre |
| `templates` | `duplicate` | ✅ OUI | Dupliquer un template a du sens |

**Conséquence :** Si on génère automatiquement toutes les combinaisons, on obtient **~150 permissions inutiles et déroutantes** pour les admins.

---

## ✅ Solution : Matrice d'Applicabilité Ressources ↔ Actions

### Principe

**Ajouter une table pivot `resource_actions` pour définir quelles actions sont applicables à quelles ressources.**

```
resource_actions
├── resource_id (FK)
├── action_id (FK)
└── is_default_enabled (boolean)  // Optionnel : activer par défaut ou non
```

**Exemple d'applicabilité :**

```php
// Ressource: Users
$usersResource->applicableActions = ['view', 'create', 'edit', 'delete'];
// ❌ Pas de 'archive', 'duplicate', 'transfer'

// Ressource: Deliverables
$deliverablesResource->applicableActions = ['view', 'create', 'edit', 'delete', 'archive', 'approve', 'export'];
// ✅ Plus d'actions car nature différente

// Ressource: Tasks
$tasksResource->applicableActions = ['view', 'create', 'edit', 'delete', 'transfer', 'duplicate'];
// ✅ 'transfer' car on peut réaffecter
```

### UI Admin - Matrice d'Applicabilité

Lors de la création/édition d'une **Ressource** :

```
┌────────────────────────────────────────────┐
│ Créer Ressource : "Livrables"             │
├────────────────────────────────────────────┤
│ Nom : Livrables                            │
│ Slug : deliverables                        │
│ Icône : file-text                          │
│                                            │
│ ✅ Actions Applicables :                   │
│ ┌──────────────────────────────────────┐  │
│ │ ☑ View       ☑ Edit     ☑ Archive   │  │
│ │ ☑ Create     ☑ Delete   ☑ Approve   │  │
│ │ ☐ Transfer   ☐ Duplicate            │  │
│ └──────────────────────────────────────┘  │
│                                            │
│ [ Générer Permissions ]  [ Enregistrer ]   │
└────────────────────────────────────────────┘
```

Lors de la création/édition d'une **Action** :

```
┌────────────────────────────────────────────┐
│ Créer Action : "Archiver"                 │
├────────────────────────────────────────────┤
│ Nom : Archiver                             │
│ Slug : archive                             │
│ Verbe : write                              │
│ Couleur : #FF9800                          │
│                                            │
│ ✅ Ressources Applicables :                │
│ ┌──────────────────────────────────────┐  │
│ │ ☑ Projects       ☑ Deliverables     │  │
│ │ ☑ Tasks          ☑ Documents        │  │
│ │ ☐ Users          ☐ Organizations    │  │
│ └──────────────────────────────────────┘  │
│                                            │
│ [ Générer Permissions ]  [ Enregistrer ]   │
└────────────────────────────────────────────┘
```

### Matrice de Permissions - Vue Admin

```
                    VIEW  CREATE  EDIT  DELETE  ARCHIVE  APPROVE  TRANSFER  DUPLICATE
Projects            ✅    ✅      ✅    ✅      ✅       ✅       ⬜        ⬜
Tasks               ✅    ✅      ✅    ✅      ⬜       ✅       ✅        ✅
Deliverables        ✅    ✅      ✅    ✅      ✅       ✅       ⬜        ⬜
Users               ✅    ✅      ✅    ✅      ⬜       ⬜       ⬜        ⬜
Organizations       ✅    ✅      ✅    ⬜      ⬜       ⬜       ⬜        ⬜

✅ = Permission active
⬜ = Combinaison non applicable (grisée)
```

**Avantages :**
- ✅ Évite création de permissions absurdes
- ✅ Interface claire pour les admins
- ✅ Génération intelligente uniquement pour combinaisons valides
- ✅ Réduction du nombre total de permissions (390 → ~180)

---

## 📊 Architecture Actuelle vs Proposée

### Architecture Actuelle (Figée)

```sql
-- Table permissions
CREATE TABLE permissions (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255),
    slug VARCHAR(255) UNIQUE,
    description TEXT,
    resource VARCHAR(100),  -- ← Texte fixe 'projects', 'tasks', etc.
    action VARCHAR(50),     -- ← Texte fixe 'view', 'create', etc.
    UNIQUE(resource, action)
);
```

**Problèmes :**
- ❌ Ressources figées dans le code
- ❌ Actions figées dans le code
- ❌ Impossible d'ajouter dynamiquement via UI
- ❌ Pas de méta-données sur les ressources/actions
- ❌ Difficile de gérer des ressources custom

**Exemple actuel :**
```php
// Pour ajouter une nouvelle ressource, il faut :
// 1. Modifier le seeder (code)
// 2. Déployer
// 3. Relancer le seeder
Permission::create([
    'resource' => 'new_resource',  // Texte libre
    'action' => 'new_action',      // Texte libre
]);
```

---

### Architecture Proposée (Dynamique avec Applicabilité)

```sql
-- Table resources
CREATE TABLE resources (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255),           -- "Projets"
    slug VARCHAR(255) UNIQUE,    -- "projects"
    description TEXT,
    model_class VARCHAR(255),    -- "App\Models\Project" (optionnel)
    icon VARCHAR(50),            -- "folder" (pour UI)
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Table actions
CREATE TABLE actions (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255),           -- "Voir"
    slug VARCHAR(255) UNIQUE,    -- "view"
    description TEXT,
    verb VARCHAR(50),            -- "read", "write", "delete" (pour API)
    color VARCHAR(20),           -- "#4CAF50" (pour UI)
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- ⭐ NOUVEAU : Table pivot pour applicabilité
CREATE TABLE resource_actions (
    id SERIAL PRIMARY KEY,
    resource_id INTEGER REFERENCES resources(id) ON DELETE CASCADE,
    action_id INTEGER REFERENCES actions(id) ON DELETE CASCADE,
    is_default_enabled BOOLEAN DEFAULT true,  -- Activer permission par défaut ?
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE(resource_id, action_id)
);

-- Table permissions (refactorisée)
CREATE TABLE permissions (
    id SERIAL PRIMARY KEY,
    resource_id INTEGER REFERENCES resources(id) ON DELETE CASCADE,
    action_id INTEGER REFERENCES actions(id) ON DELETE CASCADE,
    name VARCHAR(255),           -- "Voir les projets" (généré auto)
    slug VARCHAR(255) UNIQUE,    -- "view_projects" (généré auto)
    description TEXT,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE(resource_id, action_id)
);

-- Index pour performances
CREATE INDEX idx_resource_actions_resource ON resource_actions(resource_id);
CREATE INDEX idx_resource_actions_action ON resource_actions(action_id);
```

**Flux de Travail :**

1. **Admin crée une Ressource** `deliverables`
2. **Admin sélectionne les Actions applicables** : `view`, `create`, `edit`, `delete`, `archive`, `approve`
3. **Système insère dans `resource_actions`** : 6 lignes (1 par action applicable)
4. **Admin clique "Générer Permissions"**
5. **Système génère dans `permissions`** : Uniquement les 6 permissions valides
6. **Résultat** : Aucune permission `archive_users` ou `duplicate_organizations` créée ✅

**Avantages :**
- ✅ Ressources et actions configurables via UI admin
- ✅ Méta-données riches (icône, couleur, description)
- ✅ **Matrice d'applicabilité** : Évite génération de permissions absurdes
- ✅ Permissions générées intelligemment (uniquement combinaisons valides)
- ✅ Désactivation temporaire sans suppression
- ✅ Évolutivité totale sans toucher au code
- ✅ Support de ressources custom par organisation
- ✅ **Réduction ~50% du nombre de permissions** (390 → ~180)

---

## 🔄 Migration Complète

### Étape 1 : Créer Tables Resources, Actions et Resource_Actions

**Migration :** `2025_11_09_create_resources_and_actions_tables.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Table resources
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->string('name');  // "Projets"
            $table->string('slug')->unique();  // "projects"
            $table->text('description')->nullable();
            $table->string('model_class')->nullable();  // "App\Models\Project"
            $table->string('icon', 50)->nullable();  // "folder"
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Table actions
        Schema::create('actions', function (Blueprint $table) {
            $table->id();
            $table->string('name');  // "Voir"
            $table->string('slug')->unique();  // "view"
            $table->text('description')->nullable();
            $table->string('verb', 50)->nullable();  // "read"
            $table->string('color', 20)->nullable();  // "#4CAF50"
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ⭐ 3. Table pivot resource_actions (applicabilité)
        Schema::create('resource_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resource_id')->constrained()->onDelete('cascade');
            $table->foreignId('action_id')->constrained()->onDelete('cascade');
            $table->boolean('is_default_enabled')->default(true);
            $table->timestamps();

            // Contrainte d'unicité
            $table->unique(['resource_id', 'action_id']);

            // Index pour performances
            $table->index('resource_id');
            $table->index('action_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resource_actions');
        Schema::dropIfExists('actions');
        Schema::dropIfExists('resources');
    }
};
```

---

### Étape 2 : Migrer Données Existantes + Générer Applicabilité

**Migration :** `2025_11_09_migrate_permissions_to_flexible_system.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Extraire ressources uniques de permissions existantes
        $existingResources = DB::table('permissions')
            ->select('resource')
            ->distinct()
            ->pluck('resource');

        echo "Ressources trouvées : " . $existingResources->count() . "\n";

        // 2. Insérer dans table resources
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
            echo "✓ Ressource créée : {$resource} (ID: {$resourceId})\n";
        }

        // 3. Extraire actions uniques
        $existingActions = DB::table('permissions')
            ->select('action')
            ->distinct()
            ->pluck('action');

        echo "Actions trouvées : " . $existingActions->count() . "\n";

        // 4. Insérer dans table actions
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
            echo "✓ Action créée : {$action} (ID: {$actionId})\n";
        }

        // ⭐ 5. NOUVEAU : Générer applicabilité resource_actions
        echo "\n=== Génération matrice d'applicabilité ===\n";
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

            echo "✓ {$resourceSlug} : " . count($applicableActions) . " actions applicables\n";
        }

        echo "\n✅ Total applicabilités créées : {$applicabilityCount}\n\n";

        // 5. Ajouter colonnes resource_id et action_id à permissions
        Schema::table('permissions', function (Blueprint $table) {
            $table->foreignId('resource_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('action_id')->nullable()->constrained()->onDelete('cascade');
            $table->boolean('is_active')->default(true);
        });

        // 6. Mettre à jour permissions avec les IDs
        $permissions = DB::table('permissions')->get();
        echo "Mise à jour de " . $permissions->count() . " permissions...\n";

        foreach ($permissions as $permission) {
            DB::table('permissions')
                ->where('id', $permission->id)
                ->update([
                    'resource_id' => $resourceMap[$permission->resource] ?? null,
                    'action_id' => $actionMap[$permission->action] ?? null,
                    'updated_at' => now(),
                ]);
        }

        echo "✓ Toutes les permissions mises à jour\n";

        // 7. Supprimer anciennes colonnes resource et action (optionnel - garder pour historique)
        // Schema::table('permissions', function (Blueprint $table) {
        //     $table->dropColumn(['resource', 'action']);
        // });
    }

    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
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
        ];

        return $colorMap[$action] ?? '#757575';  // Gris par défaut
    }

    /**
     * ⭐ NOUVEAU : Déterminer quelles actions sont applicables pour une ressource
     *
     * Cette méthode définit des règles métier pour éviter les combinaisons absurdes.
     * Par exemple : pas d'action "archive" sur "users", pas de "duplicate" sur "organizations"
     */
    protected function getApplicableActionsForResource(string $resourceSlug, array $actionMap): array
    {
        // Actions de base applicables à TOUTES les ressources
        $baseActions = ['view', 'create', 'edit', 'delete'];

        // Règles spécifiques par type de ressource
        $specificRules = [
            // PMBOK Core - Ressources projet avec archivage et approbation
            'projects' => [...$baseActions, 'approve', 'export', 'archive'],
            'tasks' => [...$baseActions, 'approve', 'transfer', 'duplicate'],
            'deliverables' => [...$baseActions, 'approve', 'export', 'archive'],
            'documents' => [...$baseActions, 'approve', 'export', 'archive', 'duplicate'],

            // Ressources planification
            'milestones' => [...$baseActions, 'export'],
            'phases' => [...$baseActions, 'duplicate'],
            'schedules' => [...$baseActions, 'export', 'duplicate'],

            // Ressources financières
            'budgets' => [...$baseActions, 'approve', 'export'],
            'expenses' => [...$baseActions, 'approve', 'export'],
            'invoices' => [...$baseActions, 'approve', 'export'],

            // Ressources risques et problèmes
            'risks' => [...$baseActions, 'approve', 'export', 'archive'],
            'issues' => [...$baseActions, 'approve', 'export', 'archive'],
            'change_requests' => [...$baseActions, 'approve', 'export'],

            // Ressources humaines - PAS d'archive ni duplicate
            'users' => ['view', 'create', 'edit', 'delete', 'export'],
            'teams' => [...$baseActions, 'export'],
            'stakeholders' => [...$baseActions, 'export'],

            // Ressources organisationnelles - PAS de duplicate
            'organizations' => ['view', 'create', 'edit', 'export'],
            'portfolios' => [...$baseActions, 'export'],
            'programs' => [...$baseActions, 'export', 'archive'],

            // Ressources qualité
            'quality_metrics' => [...$baseActions, 'approve', 'export'],
            'audits' => [...$baseActions, 'approve', 'export', 'archive'],

            // Ressources communication
            'messages' => [...$baseActions, 'archive'],
            'notifications' => ['view', 'delete'],
            'reports' => [...$baseActions, 'export', 'duplicate'],

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
```

---

### Étape 3 : Créer les Models

#### Model Resource

**Fichier :** `app/Models/Resource.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'model_class',
        'icon',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ===================================
    // RELATIONS
    // ===================================

    /**
     * Permissions utilisant cette ressource
     */
    public function permissions()
    {
        return $this->hasMany(Permission::class);
    }

    /**
     * ⭐ NOUVEAU : Actions applicables à cette ressource
     *
     * Relation many-to-many via table pivot resource_actions
     */
    public function applicableActions()
    {
        return $this->belongsToMany(Action::class, 'resource_actions')
                    ->withPivot('is_default_enabled')
                    ->withTimestamps();
    }

    // ===================================
    // SCOPES
    // ===================================

    /**
     * Ressources actives uniquement
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ===================================
    // HELPERS
    // ===================================

    /**
     * Vérifier si la ressource a un model associé
     */
    public function hasModel(): bool
    {
        return !empty($this->model_class) && class_exists($this->model_class);
    }

    /**
     * Obtenir l'instance du model
     */
    public function getModelInstance(): ?Model
    {
        if ($this->hasModel()) {
            return new $this->model_class;
        }
        return null;
    }

    /**
     * ⭐ MODIFIÉ : Générer permissions uniquement pour actions APPLICABLES
     *
     * Avant : Générait pour TOUTES les actions (explosion combinatoire)
     * Après : Génère uniquement pour actions applicables (via resource_actions)
     */
    public function generatePermissionsForAllActions(): int
    {
        // ✅ Récupérer uniquement les actions applicables à cette ressource
        $actions = $this->applicableActions()->active()->get();
        $created = 0;

        foreach ($actions as $action) {
            $permission = Permission::firstOrCreate(
                [
                    'resource_id' => $this->id,
                    'action_id' => $action->id,
                ],
                [
                    'name' => "{$action->name} les {$this->name}",
                    'slug' => "{$action->slug}_{$this->slug}",
                    'description' => "Permission de {$action->slug} pour {$this->slug}",
                    'is_active' => $action->pivot->is_default_enabled ?? true,
                ]
            );

            if ($permission->wasRecentlyCreated) {
                $created++;
            }
        }

        return $created;
    }

    /**
     * ⭐ NOUVEAU : Vérifier si une action est applicable à cette ressource
     */
    public function isActionApplicable(int|string $actionId): bool
    {
        if (is_string($actionId)) {
            return $this->applicableActions()->where('slug', $actionId)->exists();
        }

        return $this->applicableActions()->where('action_id', $actionId)->exists();
    }

    /**
     * ⭐ NOUVEAU : Attacher une action applicable à cette ressource
     */
    public function attachApplicableAction(int $actionId, bool $isDefaultEnabled = true): void
    {
        $this->applicableActions()->syncWithoutDetaching([
            $actionId => ['is_default_enabled' => $isDefaultEnabled]
        ]);
    }

    /**
     * ⭐ NOUVEAU : Détacher une action applicable
     */
    public function detachApplicableAction(int $actionId): void
    {
        $this->applicableActions()->detach($actionId);
    }
}
```

---

#### Model Action

**Fichier :** `app/Models/Action.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Action extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'verb',
        'color',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ===================================
    // RELATIONS
    // ===================================

    /**
     * Permissions utilisant cette action
     */
    public function permissions()
    {
        return $this->hasMany(Permission::class);
    }

    /**
     * ⭐ NOUVEAU : Ressources pour lesquelles cette action est applicable
     *
     * Relation many-to-many via table pivot resource_actions
     */
    public function applicableResources()
    {
        return $this->belongsToMany(Resource::class, 'resource_actions')
                    ->withPivot('is_default_enabled')
                    ->withTimestamps();
    }

    // ===================================
    // SCOPES
    // ===================================

    /**
     * Actions actives uniquement
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Actions de lecture (read)
     */
    public function scopeRead($query)
    {
        return $query->where('verb', 'read');
    }

    /**
     * Actions d'écriture (write)
     */
    public function scopeWrite($query)
    {
        return $query->where('verb', 'write');
    }

    /**
     * Actions de suppression (delete)
     */
    public function scopeDelete($query)
    {
        return $query->where('verb', 'delete');
    }

    // ===================================
    // HELPERS
    // ===================================

    /**
     * Vérifier si l'action est de type lecture
     */
    public function isReadAction(): bool
    {
        return $this->verb === 'read';
    }

    /**
     * Vérifier si l'action est de type écriture
     */
    public function isWriteAction(): bool
    {
        return $this->verb === 'write';
    }

    /**
     * Vérifier si l'action est de type suppression
     */
    public function isDeleteAction(): bool
    {
        return $this->verb === 'delete';
    }

    /**
     * ⭐ NOUVEAU : Vérifier si cette action est applicable à une ressource
     */
    public function isApplicableToResource(int|string $resourceId): bool
    {
        if (is_string($resourceId)) {
            return $this->applicableResources()->where('slug', $resourceId)->exists();
        }

        return $this->applicableResources()->where('resource_id', $resourceId)->exists();
    }

    /**
     * ⭐ NOUVEAU : Attacher une ressource applicable à cette action
     */
    public function attachApplicableResource(int $resourceId, bool $isDefaultEnabled = true): void
    {
        $this->applicableResources()->syncWithoutDetaching([
            $resourceId => ['is_default_enabled' => $isDefaultEnabled]
        ]);
    }

    /**
     * ⭐ NOUVEAU : Détacher une ressource applicable
     */
    public function detachApplicableResource(int $resourceId): void
    {
        $this->applicableResources()->detach($resourceId);
    }
}
```

---

#### Model Permission (Modifié)

**Fichier :** `app/Models/Permission.php` (modifications)

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = [
        'resource_id',  // ← NOUVEAU
        'action_id',    // ← NOUVEAU
        'name',
        'slug',
        'description',
        'resource',  // ← GARDER pour compatibilité (deprecated)
        'action',    // ← GARDER pour compatibilité (deprecated)
        'is_active', // ← NOUVEAU
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ===================================
    // RELATIONS (NOUVELLES)
    // ===================================

    /**
     * Ressource associée à cette permission
     */
    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }

    /**
     * Action associée à cette permission
     */
    public function action()
    {
        return $this->belongsTo(Action::class);
    }

    /**
     * Rôles possédant cette permission
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permission')
                    ->withTimestamps();
    }

    // ===================================
    // SCOPES (NOUVEAUX)
    // ===================================

    /**
     * Permissions actives uniquement
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Filtrer par ressource (via ID ou slug)
     */
    public function scopeForResource($query, $resource)
    {
        if (is_numeric($resource)) {
            return $query->where('resource_id', $resource);
        }

        return $query->whereHas('resource', function ($q) use ($resource) {
            $q->where('slug', $resource);
        });
    }

    /**
     * Filtrer par action (via ID ou slug)
     */
    public function scopeForAction($query, $action)
    {
        if (is_numeric($action)) {
            return $query->where('action_id', $action);
        }

        return $query->whereHas('action', function ($q) use ($action) {
            $q->where('slug', $action);
        });
    }

    // ===================================
    // HELPERS (AMÉLIORÉS)
    // ===================================

    /**
     * Obtenir le nom de la ressource
     */
    public function getResourceName(): string
    {
        return $this->resource?->name ?? $this->resource ?? 'Unknown';
    }

    /**
     * Obtenir le nom de l'action
     */
    public function getActionName(): string
    {
        return $this->action?->name ?? $this->action ?? 'Unknown';
    }

    /**
     * Vérifier si c'est une permission de lecture
     */
    public function isViewPermission(): bool
    {
        return $this->action?->slug === 'view'
            || $this->action === 'view';  // Fallback compatibilité
    }

    /**
     * Générer automatiquement name et slug
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($permission) {
            if (empty($permission->name) && $permission->resource && $permission->action) {
                $permission->name = "{$permission->action->name} les {$permission->resource->name}";
            }

            if (empty($permission->slug) && $permission->resource && $permission->action) {
                $permission->slug = "{$permission->action->slug}_{$permission->resource->slug}";
            }
        });
    }

    // ===================================
    // STATIC METHODS (NOUVEAUX)
    // ===================================

    /**
     * Créer ou récupérer permission par resource/action
     */
    public static function findOrCreateByResourceAction(
        string $resourceSlug,
        string $actionSlug
    ): self {
        $resource = Resource::where('slug', $resourceSlug)->first();
        $action = Action::where('slug', $actionSlug)->first();

        if (!$resource || !$action) {
            throw new \Exception("Resource ou Action introuvable");
        }

        return static::firstOrCreate(
            [
                'resource_id' => $resource->id,
                'action_id' => $action->id,
            ],
            [
                'name' => "{$action->name} les {$resource->name}",
                'slug' => "{$action->slug}_{$resource->slug}",
                'is_active' => true,
            ]
        );
    }

    /**
     * Grouper permissions par ressource
     */
    public static function groupedByResource()
    {
        return static::with(['resource', 'action'])
            ->active()
            ->get()
            ->groupBy(function ($permission) {
                return $permission->resource?->slug ?? 'other';
            });
    }
}
```

---

## 🎨 Interface Admin (Exemples)

### Gestion des Ressources (avec Applicabilité)

```php
// Controller exemple
class ResourceController extends Controller
{
    public function index()
    {
        $resources = Resource::withCount(['permissions', 'applicableActions'])->get();
        return view('admin.resources.index', compact('resources'));
    }

    public function create()
    {
        // ⭐ Charger toutes les actions disponibles pour sélection
        $allActions = Action::active()->get();
        return view('admin.resources.create', compact('allActions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:resources',
            'description' => 'nullable|string',
            'model_class' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'applicable_actions' => 'nullable|array',  // ⭐ IDs des actions applicables
            'applicable_actions.*' => 'exists:actions,id',
        ]);

        // 1. Créer la ressource
        $resource = Resource::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? null,
            'model_class' => $validated['model_class'] ?? null,
            'icon' => $validated['icon'] ?? null,
        ]);

        // ⭐ 2. Attacher les actions applicables sélectionnées
        if (!empty($validated['applicable_actions'])) {
            foreach ($validated['applicable_actions'] as $actionId) {
                $resource->attachApplicableAction($actionId);
            }
        }

        // ⭐ 3. Générer permissions UNIQUEMENT pour actions applicables
        $resource->generatePermissionsForAllActions();

        return redirect()->route('admin.resources.index')
            ->with('success', "Ressource créée avec {$resource->applicableActions->count()} actions applicables");
    }

    public function edit(Resource $resource)
    {
        $allActions = Action::active()->get();
        $resource->load('applicableActions');
        return view('admin.resources.edit', compact('resource', 'allActions'));
    }

    public function update(Request $request, Resource $resource)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:resources,slug,' . $resource->id,
            'description' => 'nullable|string',
            'model_class' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'applicable_actions' => 'nullable|array',
            'applicable_actions.*' => 'exists:actions,id',
        ]);

        // Mettre à jour la ressource
        $resource->update([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? null,
            'model_class' => $validated['model_class'] ?? null,
            'icon' => $validated['icon'] ?? null,
        ]);

        // ⭐ Synchroniser les actions applicables
        $applicableActions = $validated['applicable_actions'] ?? [];
        $syncData = [];
        foreach ($applicableActions as $actionId) {
            $syncData[$actionId] = ['is_default_enabled' => true];
        }
        $resource->applicableActions()->sync($syncData);

        return redirect()->route('admin.resources.index')
            ->with('success', 'Ressource mise à jour');
    }
}
```

---

### Gestion des Actions

```php
class ActionController extends Controller
{
    public function index()
    {
        $actions = Action::withCount('permissions')->get();
        return view('admin.actions.index', compact('actions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:actions',
            'description' => 'nullable|string',
            'verb' => 'required|in:read,write,delete',
            'color' => 'nullable|string|max:20',
        ]);

        Action::create($validated);

        return redirect()->route('admin.actions.index')
            ->with('success', 'Action créée avec succès');
    }
}
```

---

### ⭐ Gestion de la Matrice d'Applicabilité (Vue Globale)

```php
/**
 * Controller pour gérer la matrice complète ressources × actions
 */
class ApplicabilityMatrixController extends Controller
{
    /**
     * Afficher la matrice complète
     */
    public function index()
    {
        $resources = Resource::active()
            ->with('applicableActions')
            ->orderBy('name')
            ->get();

        $actions = Action::active()
            ->orderBy('slug')
            ->get();

        // Construire matrice pour UI
        $matrix = [];
        foreach ($resources as $resource) {
            $matrix[$resource->id] = [
                'resource' => $resource,
                'actions' => []
            ];

            foreach ($actions as $action) {
                $isApplicable = $resource->applicableActions->contains('id', $action->id);
                $matrix[$resource->id]['actions'][$action->id] = [
                    'action' => $action,
                    'applicable' => $isApplicable,
                ];
            }
        }

        return view('admin.applicability-matrix.index', compact('matrix', 'resources', 'actions'));
    }

    /**
     * Basculer l'applicabilité d'une action pour une ressource
     */
    public function toggle(Request $request)
    {
        $validated = $request->validate([
            'resource_id' => 'required|exists:resources,id',
            'action_id' => 'required|exists:actions,id',
        ]);

        $resource = Resource::find($validated['resource_id']);
        $isCurrentlyApplicable = $resource->isActionApplicable($validated['action_id']);

        if ($isCurrentlyApplicable) {
            // Détacher
            $resource->detachApplicableAction($validated['action_id']);
            return response()->json([
                'success' => true,
                'applicable' => false,
                'message' => 'Action retirée des applicables'
            ]);
        } else {
            // Attacher
            $resource->attachApplicableAction($validated['action_id']);
            return response()->json([
                'success' => true,
                'applicable' => true,
                'message' => 'Action ajoutée aux applicables'
            ]);
        }
    }

    /**
     * Appliquer des presets intelligents (par type de ressource)
     */
    public function applyPreset(Resource $resource)
    {
        // Presets basés sur le type de ressource
        $presets = [
            'users' => ['view', 'create', 'edit', 'delete', 'export'],
            'projects' => ['view', 'create', 'edit', 'delete', 'approve', 'export', 'archive'],
            'tasks' => ['view', 'create', 'edit', 'delete', 'approve', 'transfer', 'duplicate'],
            'deliverables' => ['view', 'create', 'edit', 'delete', 'approve', 'export', 'archive'],
            'organizations' => ['view', 'create', 'edit', 'export'],
        ];

        $preset = $presets[$resource->slug] ?? ['view', 'create', 'edit', 'delete'];

        // Trouver les IDs des actions correspondantes
        $actions = Action::whereIn('slug', $preset)->pluck('id')->toArray();

        // Synchroniser
        $syncData = [];
        foreach ($actions as $actionId) {
            $syncData[$actionId] = ['is_default_enabled' => true];
        }
        $resource->applicableActions()->sync($syncData);

        return redirect()->back()
            ->with('success', "Preset appliqué : " . count($actions) . " actions applicables");
    }
}
```

**Vue Blade exemple : `resources/views/admin/applicability-matrix/index.blade.php`**

```blade
<div class="matrix-container">
    <h2>Matrice d'Applicabilité Ressources × Actions</h2>

    <table class="applicability-matrix">
        <thead>
            <tr>
                <th>Ressource</th>
                @foreach($actions as $action)
                    <th title="{{ $action->description }}" style="background: {{ $action->color }}20;">
                        {{ $action->name }}
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($matrix as $resourceId => $row)
                <tr>
                    <td class="resource-name">
                        <i class="icon-{{ $row['resource']->icon }}"></i>
                        {{ $row['resource']->name }}
                    </td>
                    @foreach($actions as $action)
                        @php
                            $cell = $row['actions'][$action->id];
                            $applicable = $cell['applicable'];
                        @endphp
                        <td class="matrix-cell {{ $applicable ? 'applicable' : 'not-applicable' }}"
                            data-resource="{{ $resourceId }}"
                            data-action="{{ $action->id }}"
                            onclick="toggleApplicability(this)">
                            @if($applicable)
                                ✅
                            @else
                                ⬜
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
function toggleApplicability(cell) {
    const resourceId = cell.dataset.resource;
    const actionId = cell.dataset.action;

    fetch('/admin/applicability-matrix/toggle', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ resource_id: resourceId, action_id: actionId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Basculer l'affichage
            cell.innerHTML = data.applicable ? '✅' : '⬜';
            cell.classList.toggle('applicable', data.applicable);
            cell.classList.toggle('not-applicable', !data.applicable);
        }
    });
}
</script>

<style>
.applicability-matrix {
    border-collapse: collapse;
    width: 100%;
}

.applicability-matrix th,
.applicability-matrix td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: center;
}

.matrix-cell {
    cursor: pointer;
    transition: background 0.2s;
}

.matrix-cell.applicable {
    background: #e8f5e9;
}

.matrix-cell.not-applicable {
    background: #f5f5f5;
    opacity: 0.5;
}

.matrix-cell:hover {
    background: #fff3cd;
}
</style>
```

---

## 📊 Avantages vs Inconvénients

### ✅ Avantages

| Aspect | Avant | Après | Gain |
|--------|-------|-------|------|
| **Flexibilité** | Figé dans code | Configurable UI | 🚀 100% |
| **Évolutivité** | Déploiement requis | Ajout dynamique | ⚡ Instantané |
| **Méta-données** | Aucune | Icônes, couleurs, descriptions | 🎨 Riches |
| **Custom Resources** | Impossible | Possible par org | 🔧 Flexible |
| **Désactivation** | Suppression | Soft disable | 🔒 Sûr |
| **⭐ Applicabilité** | Toutes combinaisons | Uniquement valides | 📉 -50% permissions |
| **⭐ Clarté Admin** | Confusion | Matrice visuelle | 👁️ Évident |
| **⭐ Maintenance** | Code complexe | Config simple | 🛠️ Facile |

### ⚠️ Inconvénients

| Aspect | Impact | Mitigation |
|--------|--------|------------|
| **Complexité** | ⭐ +3 tables (au lieu de 2) | Documentation complète |
| **Performance** | +Joins | Eager loading, cache, index |
| **Migration données** | Nécessaire | Script automatisé fourni |
| **Code legacy** | Compatibilité | Garder colonnes anciennes |
| **Setup initial** | Config matrice | Presets intelligents fournis |

---

## 🔧 Utilisation Post-Migration

### Avant (Code figé)
```php
// Créer permission (hardcodé)
Permission::create([
    'resource' => 'custom_module',  // Texte libre
    'action' => 'custom_action',    // Texte libre
]);
```

### Après (Dynamique avec Applicabilité via UI)
```php
// 1. Créer ressource via UI Admin
$resource = Resource::create([
    'name' => 'Module Custom',
    'slug' => 'custom_module',
    'icon' => 'box',
]);

// ⭐ 2. Définir quelles actions sont applicables
$viewAction = Action::where('slug', 'view')->first();
$createAction = Action::where('slug', 'create')->first();
$editAction = Action::where('slug', 'edit')->first();
$deleteAction = Action::where('slug', 'delete')->first();
// PAS 'archive' car un module custom ne s'archive pas

$resource->applicableActions()->attach([
    $viewAction->id,
    $createAction->id,
    $editAction->id,
    $deleteAction->id,
]);

// ⭐ 3. Permissions auto-générées UNIQUEMENT pour actions applicables
$resource->generatePermissionsForAllActions();

// Résultat : 4 permissions créées (pas 10)
// ✅ view_custom_module
// ✅ create_custom_module
// ✅ edit_custom_module
// ✅ delete_custom_module
// ❌ archive_custom_module (pas créé car non applicable)
// ❌ duplicate_custom_module (pas créé car non applicable)
// ...
```

**Ou via l'interface admin (encore plus simple) :**

```
Admin clique "Nouvelle Ressource"
→ Entre "Module Custom"
→ Coche : ☑ View  ☑ Create  ☑ Edit  ☑ Delete
→ Décoche : ☐ Archive  ☐ Duplicate
→ Clique "Générer Permissions"
→ Système crée 4 permissions au lieu de 10
```

---

## 📋 Checklist de Migration (avec Applicabilité)

### Phase 1 : Préparation
- [ ] Backup base de données
- [ ] Documenter ressources/actions actuelles
- [ ] Identifier combinaisons non applicables (ex: archive_users)
- [ ] Tester sur environnement dev

### Phase 2 : Création Tables
- [ ] Créer migration `create_resources_and_actions_tables`
- [ ] ⭐ Table `resources`
- [ ] ⭐ Table `actions`
- [ ] ⭐ Table `resource_actions` (applicabilité)
- [ ] Exécuter migrations
- [ ] Vérifier création index

### Phase 3 : Migration Données
- [ ] Créer migration `migrate_permissions_to_flexible_system`
- [ ] Extraire ressources uniques
- [ ] Extraire actions uniques
- [ ] ⭐ Générer matrice d'applicabilité automatique (via `getApplicableActionsForResource`)
- [ ] Mapper permissions existantes
- [ ] Exécuter migration
- [ ] Vérifier nombre applicabilités créées (~180 au lieu de 390)

### Phase 4 : Models
- [ ] Créer `app/Models/Resource.php`
  - [ ] Relation `applicableActions()`
  - [ ] Helper `isActionApplicable()`
  - [ ] Helper `attachApplicableAction()`
  - [ ] Modifier `generatePermissionsForAllActions()` pour respecter applicabilité
- [ ] Créer `app/Models/Action.php`
  - [ ] Relation `applicableResources()`
  - [ ] Helper `isApplicableToResource()`
  - [ ] Helper `attachApplicableResource()`
- [ ] Modifier `app/Models/Permission.php`
- [ ] Tester relations et applicabilité

### Phase 5 : Interface Admin
- [ ] Controller gestion ressources (avec applicabilité)
- [ ] Controller gestion actions
- [ ] ⭐ Controller `ApplicabilityMatrixController` (matrice complète)
  - [ ] Méthode `index()` - Afficher matrice
  - [ ] Méthode `toggle()` - Basculer applicabilité
  - [ ] Méthode `applyPreset()` - Appliquer presets
- [ ] Controller gestion permissions
- [ ] Vues Blade
  - [ ] Vue matrice d'applicabilité avec ✅/⬜
  - [ ] JavaScript toggle en temps réel
  - [ ] CSS pour cellules grisées

### Phase 6 : Tests
- [ ] Tests création ressource avec applicabilité
- [ ] Tests création action
- [ ] ⭐ Tests matrice applicabilité (toggle, presets)
- [ ] Tests génération permissions (uniquement applicables)
- [ ] Tests compatibilité ascendante
- [ ] ⭐ Vérifier qu'aucune permission absurde n'est créée

---

## 🎯 Recommandation

### ✅ À FAIRE ABSOLUMENT si :
- Besoin de ressources custom par organisation
- Interface admin prévue
- Évolutivité long terme importante
- Équipe technique solide
- ⭐ **Vous voulez éviter ~150 permissions absurdes**
- ⭐ **Vous voulez une UI claire pour les admins**

### ⚠️ À REPORTER si :
- Projet en phase MVP initial (première semaine)
- Équipe réduite (< 2 devs)
- Deadline ultra-serrée (< 48h)
- Permissions actuelles suffisantes ET figées à jamais

### 💡 Note Importante

Avec la **matrice d'applicabilité**, cette migration devient **beaucoup plus pertinente** car :
- ✅ Réduit ~50% du nombre de permissions
- ✅ Évite la confusion des admins
- ✅ Code plus maintenable à long terme
- ✅ Presets intelligents fournis (configuration rapide)

---

## 🚀 Ordre d'Implémentation Recommandé

**Si vous décidez de le faire :**

1. **Sprint 3 :** Créer tables `resources`, `actions`, `resource_actions` (1h)
2. **Sprint 3 :** Migrer données existantes + générer applicabilité (1h)
3. **Sprint 3 :** Créer models `Resource`, `Action` avec relations (2h)
4. **Sprint 3 :** Modifier `Permission` model (30 min)
5. **Sprint 4 :** Interface admin CRUD + matrice applicabilité (3h)
6. **Sprint 4 :** Tests complets (2h)

**Durée estimée totale :** 6-8 heures (avec applicabilité)

**ROI :** Après ~200 permissions créées, le temps économisé dépasse largement l'investissement initial

---

## 🎁 Résumé des Changements (Version 2.0)

### ⭐ Nouveautés par rapport à Version 1.0

| Ajout | Description | Impact |
|-------|-------------|--------|
| **Table `resource_actions`** | Matrice d'applicabilité | Évite combinaisons absurdes |
| **Relations applicables** | `applicableActions()`, `applicableResources()` | Filtrage intelligent |
| **Helpers applicabilité** | `isActionApplicable()`, etc. | Code lisible |
| **Controller matrice** | `ApplicabilityMatrixController` | UI visuelle complète |
| **Vue matrice** | Tableau ✅/⬜ cliquable | UX excellente |
| **Presets intelligents** | `applyPreset()` | Configuration rapide |
| **Auto-détection** | `getApplicableActionsForResource()` | Migration automatique |

### 📊 Statistiques

```
Version 1.0 (sans applicabilité) :
- 39 ressources × 10 actions = 390 permissions
- Beaucoup de permissions absurdes
- Configuration manuelle complexe

Version 2.0 (avec applicabilité) :
- ~180 permissions pertinentes
- Aucune combinaison absurde
- Configuration via matrice visuelle
- Réduction ~54% des permissions
```

---

**Document créé :** 9 novembre 2025
**Version :** 2.0 - Avec matrice d'applicabilité ressources ↔ actions
**Auteur :** Équipe Dev MDF Access
**Status :** ✅ COMPLET - Prêt pour implémentation
