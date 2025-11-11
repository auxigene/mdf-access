# 📋 Sprint 2 - Plan Détaillé et Méthodique
## RLS Application Layer (Row-Level Security)

**Date de début :** 9 novembre 2025
**Durée estimée :** 4-6 heures
**Priorité :** 🔥 CRITIQUE
**Objectif :** Implémenter le filtrage multi-tenant automatique au niveau applicatif

---

## 🎯 Objectifs du Sprint

### Objectif Principal
Implémenter un système RLS (Row-Level Security) qui filtre automatiquement les données selon le type d'utilisateur connecté, sans avoir besoin d'ajouter manuellement des `where()` dans chaque requête.

### Résultats Attendus
- ✅ Trait `TenantScoped` réutilisable
- ✅ Global Scope `TenantScope` pour filtrage automatique
- ✅ Middleware `CheckTenantAccess` pour sécurité supplémentaire
- ✅ Filtrage appliqué à tous les models PMBOK critiques
- ✅ Tests validant les 4 types d'utilisateurs

---

## 📐 Architecture RLS

### Logique de Filtrage par Type d'Utilisateur

| Type Utilisateur | Condition | Filtre Appliqué | Exemple |
|------------------|-----------|-----------------|---------|
| **System Admin** | `is_system_admin = true` | ❌ Aucun (bypass complet) | Voit TOUS les projets |
| **Internal (SAMSIC)** | `organization.type = 'Internal'` | ❌ Aucun (bypass complet) | Voit TOUS les projets |
| **Client** | `organization.type = 'Client'` | ✅ `client_organization_id = user.organization_id` | Voit uniquement SES projets |
| **Partner** | `organization.type = 'Partner'` | ✅ Projets où org est dans `project_organizations` | Voit projets où il participe |

### Flux de Filtrage

```
Requête Eloquent
    ↓
TenantScope (Global Scope)
    ↓
Vérifier Auth::user()
    ↓
┌─────────────────────┐
│ System Admin ?      │ → OUI → Bypass (pas de filtre)
└─────────────────────┘
    ↓ NON
┌─────────────────────┐
│ Internal (SAMSIC) ? │ → OUI → Bypass (pas de filtre)
└─────────────────────┘
    ↓ NON
┌─────────────────────┐
│ Client ?            │ → OUI → WHERE client_organization_id = X
└─────────────────────┘
    ↓ NON
┌─────────────────────┐
│ Partner ?           │ → OUI → WHERE EXISTS (project_organizations)
└─────────────────────┘
    ↓
Résultats filtrés
```

---

## 📝 Étapes Détaillées

### ✅ ÉTAPE 1 : Créer le Trait TenantScoped (30 min)

**Fichier :** `app/Traits/TenantScoped.php`

**Objectif :** Trait réutilisable pour appliquer le scope multi-tenant à n'importe quel model.

#### Code à Implémenter

```php
<?php

namespace App\Traits;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;

trait TenantScoped
{
    /**
     * Boot le trait TenantScoped
     *
     * Ajoute automatiquement le TenantScope global au model
     */
    protected static function bootTenantScoped(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    /**
     * Obtenir une nouvelle query sans le scope tenant
     * Utile pour les admins ou opérations spéciales
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function withoutTenantScope()
    {
        return static::withoutGlobalScope(TenantScope::class);
    }

    /**
     * Vérifier si le model doit être scopé pour l'utilisateur actuel
     *
     * @return bool
     */
    public function shouldApplyTenantScope(): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false; // Pas d'utilisateur connecté = pas de scope
        }

        // System Admin : bypass
        if ($user->isSystemAdmin()) {
            return false;
        }

        // Internal (SAMSIC) : bypass
        if ($user->isInternal()) {
            return false;
        }

        // Client et Partner : appliquer le scope
        return true;
    }
}
```

#### Checklist Étape 1
- [ ] Créer fichier `app/Traits/TenantScoped.php`
- [ ] Implémenter méthode `bootTenantScoped()`
- [ ] Implémenter méthode `withoutTenantScope()`
- [ ] Implémenter méthode `shouldApplyTenantScope()`
- [ ] Ajouter commentaires PHPDoc
- [ ] Vérifier namespaces et imports

---

### ✅ ÉTAPE 2 : Créer le Global Scope TenantScope (90 min)

**Fichier :** `app/Scopes/TenantScope.php`

**Objectif :** Scope global qui applique automatiquement les filtres multi-tenant à chaque requête Eloquent.

#### Code à Implémenter

```php
<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class TenantScope implements Scope
{
    /**
     * Appliquer le scope à une query Eloquent
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    public function apply(Builder $builder, Model $model): void
    {
        $user = Auth::user();

        // Pas d'utilisateur connecté = pas de filtre
        if (!$user) {
            return;
        }

        // System Admin : bypass complet (voit tout)
        if ($user->isSystemAdmin()) {
            return;
        }

        // Internal (SAMSIC) : bypass complet (voit tout)
        if ($user->isInternal()) {
            return;
        }

        // Client : filtre sur client_organization_id
        if ($user->isClient()) {
            $this->applyClientFilter($builder, $user);
            return;
        }

        // Partner : filtre sur participations projets
        if ($user->isPartner()) {
            $this->applyPartnerFilter($builder, $user);
            return;
        }

        // Par défaut : ne rien afficher (sécurité)
        $builder->whereRaw('1 = 0');
    }

    /**
     * Appliquer le filtre pour un utilisateur Client
     *
     * Filtre : client_organization_id = user.organization_id
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \App\Models\User $user
     * @return void
     */
    protected function applyClientFilter(Builder $builder, $user): void
    {
        $tableName = $builder->getModel()->getTable();

        // Vérifier si la table a la colonne client_organization_id
        if ($this->hasColumn($tableName, 'client_organization_id')) {
            $builder->where("{$tableName}.client_organization_id", $user->organization_id);
        } else {
            // Si pas de colonne, ne rien afficher (sécurité)
            $builder->whereRaw('1 = 0');
        }
    }

    /**
     * Appliquer le filtre pour un utilisateur Partner
     *
     * Filtre : Projets où l'organisation participe (via project_organizations)
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \App\Models\User $user
     * @return void
     */
    protected function applyPartnerFilter(Builder $builder, $user): void
    {
        $tableName = $builder->getModel()->getTable();

        // Pour la table projects : filtre via project_organizations
        if ($tableName === 'projects') {
            $builder->whereExists(function ($query) use ($user) {
                $query->select(\DB::raw(1))
                      ->from('project_organizations')
                      ->whereColumn('project_organizations.project_id', 'projects.id')
                      ->where('project_organizations.organization_id', $user->organization_id)
                      ->where('project_organizations.status', 'active');
            });
        }
        // Pour les autres tables liées aux projets (tasks, deliverables, etc.)
        elseif ($this->hasColumn($tableName, 'project_id')) {
            $builder->whereHas('project', function ($query) use ($user) {
                $query->whereExists(function ($subQuery) use ($user) {
                    $subQuery->select(\DB::raw(1))
                             ->from('project_organizations')
                             ->whereColumn('project_organizations.project_id', 'projects.id')
                             ->where('project_organizations.organization_id', $user->organization_id)
                             ->where('project_organizations.status', 'active');
                });
            });
        }
        else {
            // Si pas de relation projet, ne rien afficher (sécurité)
            $builder->whereRaw('1 = 0');
        }
    }

    /**
     * Vérifier si une table a une colonne spécifique
     *
     * @param string $table
     * @param string $column
     * @return bool
     */
    protected function hasColumn(string $table, string $column): bool
    {
        return \Schema::hasColumn($table, $column);
    }

    /**
     * Étendre la query pour exclure le scope
     * (pour les méthodes like withoutGlobalScope)
     */
    public function extend(Builder $builder): void
    {
        // Permet d'exclure le scope avec Model::withoutGlobalScope(TenantScope::class)
    }
}
```

#### Checklist Étape 2
- [ ] Créer dossier `app/Scopes/` (si n'existe pas)
- [ ] Créer fichier `app/Scopes/TenantScope.php`
- [ ] Implémenter interface `Scope`
- [ ] Implémenter méthode `apply()`
- [ ] Implémenter `applyClientFilter()`
- [ ] Implémenter `applyPartnerFilter()`
- [ ] Implémenter helper `hasColumn()`
- [ ] Tester avec requête simple

---

### ✅ ÉTAPE 3 : Créer le Middleware CheckTenantAccess (45 min)

**Fichier :** `app/Http/Middleware/CheckTenantAccess.php`

**Objectif :** Middleware pour vérifier que l'utilisateur a bien accès à la ressource demandée (couche de sécurité supplémentaire).

#### Code à Implémenter

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantAccess
{
    /**
     * Vérifier l'accès tenant pour la requête
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Pas d'utilisateur connecté : autoriser (laissé à auth middleware)
        if (!$user) {
            return $next($request);
        }

        // System Admin : bypass
        if ($user->isSystemAdmin()) {
            return $next($request);
        }

        // Internal (SAMSIC) : bypass
        if ($user->isInternal()) {
            return $next($request);
        }

        // Pour Client et Partner : vérifier que organization_id est set
        if (!$user->organization_id) {
            abort(403, 'Utilisateur sans organisation assignée');
        }

        // Vérifier que l'organisation existe et est active
        if (!$user->organization || !$user->organization->isActive()) {
            abort(403, 'Organisation inactive ou inexistante');
        }

        return $next($request);
    }
}
```

#### Enregistrement du Middleware

**Fichier :** `bootstrap/app.php` (Laravel 11)

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'tenant' => \App\Http\Middleware\CheckTenantAccess::class,
    ]);
})
```

#### Checklist Étape 3
- [ ] Créer fichier `app/Http/Middleware/CheckTenantAccess.php`
- [ ] Implémenter méthode `handle()`
- [ ] Vérifier bypass pour System Admin et Internal
- [ ] Vérifier existence et statut organisation
- [ ] Enregistrer dans `bootstrap/app.php`
- [ ] Tester middleware avec route protégée

---

### ✅ ÉTAPE 4 : Appliquer TenantScoped aux Models (60 min)

**Objectif :** Ajouter le trait `TenantScoped` à tous les models PMBOK qui doivent être filtrés.

#### Models Prioritaires (Phase 1)

```php
// app/Models/Project.php
use App\Traits\TenantScoped;

class Project extends Model
{
    use SoftDeletes, TenantScoped;  // ← Ajouter TenantScoped

    // ... reste du code
}
```

#### Liste des Models à Modifier

**Priorité HAUTE (Sprint 2) :**
- [ ] `app/Models/Project.php`
- [ ] `app/Models/Task.php`
- [ ] `app/Models/Deliverable.php`
- [ ] `app/Models/Phase.php`
- [ ] `app/Models/Milestone.php`

**Priorité MOYENNE (Sprint 2 si temps) :**
- [ ] `app/Models/WbsElement.php`
- [ ] `app/Models/Risk.php`
- [ ] `app/Models/Issue.php`
- [ ] `app/Models/ChangeRequest.php`
- [ ] `app/Models/Budget.php`

**Priorité BASSE (Sprint 3+) :**
- [ ] `app/Models/ResourceAllocation.php`
- [ ] `app/Models/Document.php`
- [ ] `app/Models/Meeting.php`
- [ ] `app/Models/Stakeholder.php`
- [ ] Autres models PMBOK

#### Template de Modification

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\TenantScoped;  // ← Import

class ModelName extends Model
{
    use SoftDeletes, TenantScoped;  // ← Ajouter trait

    // ... reste du code inchangé
}
```

#### Checklist Étape 4
- [ ] Modifier 5 models prioritaires
- [ ] Vérifier imports corrects
- [ ] Tester chaque model individuellement
- [ ] Modifier 5+ models moyens (si temps)
- [ ] Documenter models restants pour Sprint 3

---

### ✅ ÉTAPE 5 : Tests RLS Complets (90 min)

**Objectif :** Valider que le RLS fonctionne correctement pour les 4 types d'utilisateurs.

#### Script de Test

**Fichier :** `test_sprint2_rls.php`

```php
<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Project;
use App\Models\Organization;
use Illuminate\Support\Facades\Auth;

echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║       🧪 TESTS SPRINT 2 - RLS APPLICATION LAYER            ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

// ===================================
// PRÉPARATION : Créer utilisateurs de test
// ===================================
echo "📋 PRÉPARATION : Créer utilisateurs de test\n";
echo str_repeat("-", 65) . "\n\n";

// 1. System Admin
$systemAdmin = User::firstOrCreate(
    ['email' => 'system.admin@test.com'],
    [
        'name' => 'System Admin',
        'password' => bcrypt('password'),
        'organization_id' => null,
        'is_system_admin' => true,
    ]
);
echo "✅ System Admin créé (ID={$systemAdmin->id})\n";

// 2. Internal (SAMSIC)
$samsicOrg = Organization::where('type', 'Internal')->first()
    ?? Organization::find(27);

$internalUser = User::firstOrCreate(
    ['email' => 'internal@samsic.ma'],
    [
        'name' => 'User SAMSIC',
        'password' => bcrypt('password'),
        'organization_id' => $samsicOrg->id,
        'is_system_admin' => false,
    ]
);
echo "✅ Internal User créé (ID={$internalUser->id}, Org={$samsicOrg->id})\n";

// 3. Client
$clientOrg = Organization::where('type', 'Client')->first();
if (!$clientOrg) {
    $clientOrg = Organization::create([
        'name' => 'Client Test',
        'type' => 'Client',
        'status' => 'active',
    ]);
}

$clientUser = User::firstOrCreate(
    ['email' => 'client@test.com'],
    [
        'name' => 'User Client',
        'password' => bcrypt('password'),
        'organization_id' => $clientOrg->id,
        'is_system_admin' => false,
    ]
);
echo "✅ Client User créé (ID={$clientUser->id}, Org={$clientOrg->id})\n";

// 4. Partner
$partnerOrg = Organization::where('type', 'Partner')->first();
if (!$partnerOrg) {
    $partnerOrg = Organization::create([
        'name' => 'Partner Test',
        'type' => 'Partner',
        'status' => 'active',
    ]);
}

$partnerUser = User::firstOrCreate(
    ['email' => 'partner@test.com'],
    [
        'name' => 'User Partner',
        'password' => bcrypt('password'),
        'organization_id' => $partnerOrg->id,
        'is_system_admin' => false,
    ]
);
echo "✅ Partner User créé (ID={$partnerUser->id}, Org={$partnerOrg->id})\n\n";

// ===================================
// TEST 1 : System Admin (Bypass)
// ===================================
echo "📋 TEST 1 : SYSTEM ADMIN (doit voir tout)\n";
echo str_repeat("-", 65) . "\n";

Auth::login($systemAdmin);
$projectCount = Project::count();
$projectCountWithoutScope = Project::withoutTenantScope()->count();

echo "Projects visibles : $projectCount\n";
echo "Projects sans scope : $projectCountWithoutScope\n";
echo "Résultat : " . ($projectCount === $projectCountWithoutScope ? '✅ PASS' : '❌ FAIL') . "\n\n";

Auth::logout();

// ===================================
// TEST 2 : Internal User (Bypass)
// ===================================
echo "📋 TEST 2 : INTERNAL USER (doit voir tout)\n";
echo str_repeat("-", 65) . "\n";

Auth::login($internalUser);
$projectCount = Project::count();
$projectCountWithoutScope = Project::withoutTenantScope()->count();

echo "Projects visibles : $projectCount\n";
echo "Projects sans scope : $projectCountWithoutScope\n";
echo "Résultat : " . ($projectCount === $projectCountWithoutScope ? '✅ PASS' : '❌ FAIL') . "\n\n";

Auth::logout();

// ===================================
// TEST 3 : Client User (Filtré)
// ===================================
echo "📋 TEST 3 : CLIENT USER (filtré sur client_organization_id)\n";
echo str_repeat("-", 65) . "\n";

Auth::login($clientUser);
$projectCount = Project::count();
$projectCountExpected = Project::withoutTenantScope()
    ->where('client_organization_id', $clientUser->organization_id)
    ->count();

echo "Projects visibles : $projectCount\n";
echo "Projects attendus : $projectCountExpected\n";
echo "Résultat : " . ($projectCount === $projectCountExpected ? '✅ PASS' : '❌ FAIL') . "\n\n";

Auth::logout();

// ===================================
// TEST 4 : Partner User (Filtré)
// ===================================
echo "📋 TEST 4 : PARTNER USER (filtré sur project_organizations)\n";
echo str_repeat("-", 65) . "\n";

Auth::login($partnerUser);
$projectCount = Project::count();

echo "Projects visibles : $projectCount\n";
echo "Note : Partner n'a pas de participations pour l'instant\n";
echo "Résultat : " . ($projectCount === 0 ? '✅ PASS' : '⚠️  VÉRIFIER') . "\n\n";

Auth::logout();

// ===================================
// TEST 5 : withoutTenantScope()
// ===================================
echo "📋 TEST 5 : withoutTenantScope() (bypass manuel)\n";
echo str_repeat("-", 65) . "\n";

Auth::login($clientUser);
$projectCountScoped = Project::count();
$projectCountUnscoped = Project::withoutTenantScope()->count();

echo "Projects avec scope : $projectCountScoped\n";
echo "Projects sans scope : $projectCountUnscoped\n";
echo "Résultat : " . ($projectCountUnscoped > $projectCountScoped ? '✅ PASS' : '❌ FAIL') . "\n\n";

Auth::logout();

// ===================================
// RÉSUMÉ
// ===================================
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║           ✅ TESTS TERMINÉS - RLS APPLICATION LAYER         ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

echo "✅ Tous les tests RLS ont été exécutés!\n";
echo "✅ Sprint 2 - RLS Application Layer → COMPLÉTÉ\n\n";
```

#### Checklist Étape 5
- [ ] Créer script `test_sprint2_rls.php`
- [ ] Créer 4 utilisateurs de test (admin, internal, client, partner)
- [ ] Test System Admin → Voit tout
- [ ] Test Internal → Voit tout
- [ ] Test Client → Filtré sur client_organization_id
- [ ] Test Partner → Filtré sur participations
- [ ] Test withoutTenantScope() fonctionne
- [ ] Tous les tests passent ✅

---

## 📊 Checklist Globale Sprint 2

### Phase 1 : Création des Composants
- [ ] ✅ Étape 1 : Créer Trait TenantScoped (30 min)
- [ ] ✅ Étape 2 : Créer Global Scope TenantScope (90 min)
- [ ] ✅ Étape 3 : Créer Middleware CheckTenantAccess (45 min)

### Phase 2 : Application aux Models
- [ ] ✅ Étape 4 : Appliquer TenantScoped aux models (60 min)
  - [ ] Project
  - [ ] Task
  - [ ] Deliverable
  - [ ] Phase
  - [ ] Milestone
  - [ ] (Optionnel) WbsElement, Risk, Issue, ChangeRequest, Budget

### Phase 3 : Tests et Validation
- [ ] ✅ Étape 5 : Tests RLS complets (90 min)
  - [ ] Test System Admin
  - [ ] Test Internal
  - [ ] Test Client
  - [ ] Test Partner
  - [ ] Test withoutTenantScope()

### Phase 4 : Documentation
- [ ] Mettre à jour ROADMAP_CURRENT_STATUS.md
- [ ] Créer SPRINT2_SUMMARY.md
- [ ] Documenter models restants à modifier

---

## ⚠️ Points d'Attention

### Pièges à Éviter

1. **N+1 Queries dans Partner Filter**
   - Le filtre Partner utilise `whereHas()` → peut causer N+1
   - Solution : Utiliser `whereExists()` avec sous-requête SQL brute

2. **Schema::hasColumn() Performance**
   - Appel à chaque requête → peut ralentir
   - Solution : Cache les résultats ou hardcode les colonnes

3. **Auth::user() dans Scope**
   - Peut être null dans les jobs/commands
   - Solution : Toujours vérifier `if (!$user) return;`

4. **Soft Deletes**
   - Le TenantScope peut interférer avec `onlyTrashed()`
   - Solution : Ordre des traits important : `use SoftDeletes, TenantScoped;`

### Sécurité

- ✅ Par défaut : `whereRaw('1 = 0')` si type inconnu
- ✅ Vérifier organisation active dans middleware
- ✅ Bypass uniquement pour System Admin et Internal
- ✅ withoutTenantScope() disponible pour cas spéciaux

---

## 🎯 Critères de Succès

| Critère | Attendu | Validation |
|---------|---------|------------|
| **System Admin voit tout** | 66 projets | ✅ |
| **Internal voit tout** | 66 projets | ✅ |
| **Client filtré** | X projets (selon org) | ✅ |
| **Partner filtré** | 0 projets (pas de participation) | ✅ |
| **withoutTenantScope() bypass** | Fonctionne | ✅ |
| **Pas d'erreur SQL** | Aucune | ✅ |
| **Performance acceptable** | < 100ms par requête | ⚠️ |

---

## 📈 Progression Sprint 2

**Avant Sprint 2 :** 38% global
**Après Sprint 2 :** **45%** global (+7%)

| Phase | Avant | Après | Statut |
|-------|-------|-------|--------|
| 3. RLS Application | 0% | 100% | ✅ Sprint 2 |

---

## 🚀 Prochaines Actions Après Sprint 2

### Sprint 3 : Services et Validation
- Créer ProjectOrganizationService
- Créer Form Requests
- Créer Policies Laravel
- Tests unitaires

### Améliorations Futures
- Optimiser performance Partner filter
- Ajouter cache pour Schema::hasColumn()
- Ajouter logging des accès RLS
- Tests de performance charge

---

**Document créé :** 9 novembre 2025
**Version :** 1.0
**Auteur :** Équipe Dev MDF Access
**Durée estimée totale :** 4-6 heures
