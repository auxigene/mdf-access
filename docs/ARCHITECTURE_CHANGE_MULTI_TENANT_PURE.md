# 🔄 Changement Architectural Majeur : Multi-Tenant Pur

**Date :** 9 novembre 2025
**Type :** Migration architecturale majeure
**Priorité :** 🔥 CRITIQUE
**Statut :** 📋 PLANIFICATION

---

## 📊 Contexte et Motivation

### Architecture Actuelle (à remplacer)

```
organizations
├── id
├── name
├── type → 'Internal' | 'Client' | 'Partner'  ← À SUPPRIMER
└── ...

Logique :
- Une organisation a UN TYPE FIXE
- SAMSIC = Internal (propriétaire plateforme)
- Clients = Client
- Partenaires = Partner
- Le type détermine les permissions et filtres RLS
```

**Problème :**
- ❌ Rigide : Une organisation ne peut pas changer de rôle
- ❌ Irréaliste : SAMSIC peut être MOE sur certains projets, cliente sur d'autres
- ❌ Complexe : Notion de "propriétaire de plateforme" artificielle
- ❌ Limitant : Ne permet pas la flexibilité business

---

## 🎯 Nouvelle Architecture (Multi-Tenant Pur)

### Principe

**Le rôle d'une organisation est CONTEXTUEL et défini par projet via `project_organizations`**

```
organizations
├── id
├── name
├── status → 'active' | 'inactive' | 'archived'
└── ...
(Plus de colonne type)

project_organizations
├── project_id
├── organization_id
├── role → 'sponsor' | 'moa' | 'moe' | 'subcontractor'  ← C'est ICI le rôle
├── status → 'active' | 'inactive'
└── ...
```

**Nouvelle logique :**
- ✅ Une organisation peut être **Cliente** sur Projet A
- ✅ La même organisation peut être **MOE** sur Projet B
- ✅ Et **Sous-traitant** sur Projet C
- ✅ Le rôle est déterminé dynamiquement selon le projet

---

## 📋 Impact sur l'Architecture

### 1. Table `organizations`

**AVANT :**
```sql
CREATE TABLE organizations (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255),
    type VARCHAR(50) CHECK (type IN ('Internal', 'Client', 'Partner')),  ← À SUPPRIMER
    status VARCHAR(50),
    ...
);
```

**APRÈS :**
```sql
CREATE TABLE organizations (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255),
    status VARCHAR(50) CHECK (status IN ('active', 'inactive', 'archived')),
    ...
);
-- Plus de colonne type
```

**Migration nécessaire :**
- Supprimer colonne `type`
- Vérifier que ça ne casse rien

---

### 2. Model `Organization`

**AVANT :**
```php
class Organization extends Model {
    protected $fillable = ['name', 'type', 'status', ...];

    // Helpers basés sur type fixe
    public function isInternal(): bool {
        return $this->type === 'Internal';  // ← Ne marche plus
    }

    public function isClient(): bool {
        return $this->type === 'Client';  // ← Ne marche plus
    }
}
```

**APRÈS :**
```php
class Organization extends Model {
    protected $fillable = ['name', 'status', ...];  // Pas de type

    // Nouveaux helpers contextuels
    public function isClientForProject(int $projectId): bool {
        return $this->participations()
            ->where('project_id', $projectId)
            ->where('role', 'sponsor')  // ou vérifier client_organization_id
            ->exists();
    }

    public function isMoeForProject(int $projectId): bool {
        return $this->participations()
            ->where('project_id', $projectId)
            ->where('role', 'moe')
            ->where('status', 'active')
            ->exists();
    }

    public function getRoleForProject(int $projectId): ?string {
        $participation = $this->participations()
            ->where('project_id', $projectId)
            ->where('status', 'active')
            ->first();

        return $participation?->role;
    }

    // Helpers globaux (tous projets)
    public function getProjectsWhereClient() {
        return $this->participations()
            ->where('role', 'sponsor')
            ->where('status', 'active')
            ->with('project')
            ->get()
            ->pluck('project');
    }

    public function getProjectsWhereMoe() {
        return $this->participations()
            ->where('role', 'moe')
            ->where('status', 'active')
            ->with('project')
            ->get()
            ->pluck('project');
    }
}
```

---

### 3. Model `User`

**AVANT :**
```php
class User extends Authenticatable {
    // Helpers basés sur organization.type
    public function isInternal(): bool {
        return $this->organization?->type === 'Internal';  // ← Ne marche plus
    }

    public function isClient(): bool {
        return $this->organization?->type === 'Client';  // ← Ne marche plus
    }

    public function isPartner(): bool {
        return $this->organization?->type === 'Partner';  // ← Ne marche plus
    }
}
```

**APRÈS :**
```php
class User extends Authenticatable {
    // Nouveaux helpers contextuels
    public function isClientForProject(int $projectId): bool {
        return $this->organization?->isClientForProject($projectId) ?? false;
    }

    public function isMoeForProject(int $projectId): bool {
        return $this->organization?->isMoeForProject($projectId) ?? false;
    }

    public function getRoleForProject(int $projectId): ?string {
        return $this->organization?->getRoleForProject($projectId);
    }

    // Helpers globaux
    public function getProjectsWhereClient() {
        return $this->organization?->getProjectsWhereClient() ?? collect();
    }

    public function getProjectsWhereMoe() {
        return $this->organization?->getProjectsWhereMoe() ?? collect();
    }

    // Tous les projets accessibles
    public function getAccessibleProjects() {
        if ($this->isSystemAdmin()) {
            return Project::all();
        }

        // Tous les projets où l'organisation participe
        return $this->organization?->allProjects() ?? collect();
    }
}
```

---

### 4. Logique RLS (Row-Level Security)

**AVANT (complexe avec 4 types) :**
```php
// TenantScope
if ($user->isSystemAdmin()) {
    return; // Bypass
}

if ($user->isInternal()) {
    return; // Bypass (SAMSIC voit tout)
}

if ($user->isClient()) {
    $builder->where('client_organization_id', $user->organization_id);
}

if ($user->isPartner()) {
    $builder->whereExists(...project_organizations...);
}
```

**APRÈS (simple avec 2 cas) :**
```php
// TenantScope
if ($user->isSystemAdmin()) {
    return; // Bypass (seul cas de bypass)
}

// Tous les autres : filtre sur participations
$builder->whereHas('projectOrganizations', function($query) use ($user) {
    $query->where('organization_id', $user->organization_id)
          ->where('status', 'active');
});

// OU pour la table projects directement :
$builder->whereExists(function($query) use ($user) {
    $query->select(DB::raw(1))
          ->from('project_organizations')
          ->whereColumn('project_organizations.project_id', 'projects.id')
          ->where('project_organizations.organization_id', $user->organization_id)
          ->where('project_organizations.status', 'active');
});
```

**Avantages :**
- ✅ **Plus simple** : Un seul cas de filtrage (participations)
- ✅ **Plus flexible** : Le rôle change selon le projet
- ✅ **Plus réaliste** : Reflète la vraie vie business
- ✅ **Pas de notion artificielle** de "propriétaire plateforme"

---

## 🔄 Plan de Migration

### Phase 1 : Analyse d'Impact (30 min)

**Identifier tous les endroits utilisant `organization.type` :**

```bash
# Rechercher dans le code
grep -r "organization.*type" app/
grep -r "isInternal\|isClient\|isPartner" app/
grep -r "'Internal'\|'Client'\|'Partner'" app/
```

**Fichiers impactés :**
- [ ] `app/Models/Organization.php`
- [ ] `app/Models/User.php`
- [ ] `app/Scopes/TenantScope.php` (Sprint 2 - pas encore créé)
- [ ] Seeders utilisant `type`
- [ ] Tests utilisant `type`

---

### Phase 2 : Créer Migration (15 min)

**Fichier :** `database/migrations/YYYY_MM_DD_remove_type_column_from_organizations_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            // Supprimer la colonne type
            $table->dropColumn('type');
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            // Restaurer la colonne type en rollback
            $table->string('type', 50)->nullable()->after('name');
        });
    }
};
```

---

### Phase 3 : Modifier Models (60 min)

#### 3.1 Model Organization

**Actions :**
- [ ] Supprimer `'type'` de `$fillable`
- [ ] Supprimer `isInternal()`, `isClient()`, `isPartner()`
- [ ] Ajouter helpers contextuels : `isClientForProject()`, `isMoeForProject()`, etc.
- [ ] Ajouter helpers globaux : `getProjectsWhereClient()`, etc.

#### 3.2 Model User

**Actions :**
- [ ] Supprimer `isInternal()`, `isClient()`, `isPartner()`
- [ ] Ajouter helpers contextuels déléguant à Organization
- [ ] Ajouter `getAccessibleProjects()`

---

### Phase 4 : Adapter Sprint 2 - RLS (90 min)

**Nouvelle logique simplifiée :**

```php
// app/Scopes/TenantScope.php

public function apply(Builder $builder, Model $model): void
{
    $user = Auth::user();

    if (!$user) {
        return; // Pas d'utilisateur = pas de filtre
    }

    // System Admin : SEUL bypass
    if ($user->isSystemAdmin()) {
        return;
    }

    // Tous les autres : filtre sur participations
    $tableName = $builder->getModel()->getTable();

    if ($tableName === 'projects') {
        // Filtre direct sur projects
        $builder->whereExists(function($query) use ($user) {
            $query->select(DB::raw(1))
                  ->from('project_organizations')
                  ->whereColumn('project_organizations.project_id', 'projects.id')
                  ->where('project_organizations.organization_id', $user->organization_id)
                  ->where('project_organizations.status', 'active');
        });
    }
    elseif ($this->hasColumn($tableName, 'project_id')) {
        // Tables liées aux projets (tasks, deliverables, etc.)
        $builder->whereHas('project', function($query) use ($user) {
            $query->whereExists(function($subQuery) use ($user) {
                $subQuery->select(DB::raw(1))
                         ->from('project_organizations')
                         ->whereColumn('project_organizations.project_id', 'projects.id')
                         ->where('project_organizations.organization_id', $user->organization_id)
                         ->where('project_organizations.status', 'active');
            });
        });
    }
    else {
        // Sécurité : ne rien afficher
        $builder->whereRaw('1 = 0');
    }
}
```

**Avantages :**
- ✅ Code plus simple (moins de cas)
- ✅ Un seul type de filtre (participations)
- ✅ Plus de notion "Internal" vs "Client" vs "Partner"

---

### Phase 5 : Mettre à Jour Seeders (30 min)

**Fichiers à modifier :**
- [ ] `database/seeders/OrganizationsSeeder.php`

**AVANT :**
```php
Organization::create([
    'name' => 'SAMSIC MAINTENANCE MAROC',
    'type' => 'Internal',  // ← Supprimer
    'status' => 'active',
]);
```

**APRÈS :**
```php
Organization::create([
    'name' => 'SAMSIC MAINTENANCE MAROC',
    'status' => 'active',  // Pas de type
]);
```

---

### Phase 6 : Mettre à Jour Documentation (45 min)

**Documents à modifier :**
- [ ] `MULTI_TENANT_ARCHITECTURE.md`
- [ ] `MULTI_TENANT_MULTI_ORGANISATIONS.md`
- [ ] `ROADMAP_CURRENT_STATUS.md`
- [ ] `SPRINT2_PLAN_DETAILLE.md`

**Changements :**
- Supprimer toute référence à `type` d'organisation
- Mettre à jour schémas et exemples
- Expliquer le nouveau système contextuel

---

### Phase 7 : Tests (60 min)

**Script de test :** `test_architecture_change.php`

```php
// Test 1 : Organisation sans type
$org = Organization::create(['name' => 'Test Org', 'status' => 'active']);
assert(!isset($org->type));  // ✅ Pas de type

// Test 2 : Helpers contextuels
$project = Project::first();
$org->participations()->create([
    'project_id' => $project->id,
    'role' => 'moe',
    'status' => 'active',
]);

assert($org->isMoeForProject($project->id) === true);  // ✅
assert($org->isClientForProject($project->id) === false);  // ✅

// Test 3 : User helpers
$user = User::first();
assert($user->getRoleForProject($project->id) !== null);  // ✅

// Test 4 : RLS fonctionne
Auth::login($user);
$accessibleProjects = Project::all();  // Devrait être filtré
assert($accessibleProjects->count() > 0);  // ✅
```

---

## 📊 Avantages du Nouveau Système

| Aspect | Avant | Après | Amélioration |
|--------|-------|-------|--------------|
| **Flexibilité** | ❌ Type fixe | ✅ Rôle contextuel | 🚀 100% |
| **Réalisme** | ⚠️ Artificiel | ✅ Reflète business | 🎯 |
| **Complexité RLS** | ⚠️ 4 cas | ✅ 2 cas | 📉 50% |
| **Code** | ⚠️ isInternal/Client/Partner | ✅ getRoleForProject | 📝 Plus clair |
| **Évolutivité** | ❌ Limitée | ✅ Infinie | 🔮 |

---

## ⚠️ Risques et Mitigation

### Risque 1 : Seeders cassés
**Impact :** Moyen
**Mitigation :** Modifier seeders avant de lancer migration

### Risque 2 : Données existantes
**Impact :** Faible (colonne type sera supprimée)
**Mitigation :** Backup DB avant migration

### Risque 3 : Tests cassés
**Impact :** Moyen
**Mitigation :** Mettre à jour tous les tests utilisant `type`

---

## 📋 Checklist Complète

### Préparation
- [ ] Backup de la base de données
- [ ] Identifier tous les usages de `organization.type`
- [ ] Lire cette documentation complète

### Migration Base de Données
- [ ] Créer migration suppression colonne `type`
- [ ] Exécuter migration
- [ ] Vérifier que la colonne est supprimée

### Modification Code
- [ ] Modifier Model Organization (supprimer helpers type)
- [ ] Ajouter nouveaux helpers contextuels Organization
- [ ] Modifier Model User (supprimer helpers type)
- [ ] Ajouter nouveaux helpers contextuels User

### Sprint 2 Adapté
- [ ] Créer Trait TenantScoped (simplifié)
- [ ] Créer Global Scope TenantScope (simplifié - 2 cas au lieu de 4)
- [ ] Créer Middleware CheckTenantAccess (simplifié)
- [ ] Appliquer aux models

### Tests
- [ ] Tests helpers contextuels Organization
- [ ] Tests helpers contextuels User
- [ ] Tests RLS simplifié
- [ ] Tests bout-en-bout

### Documentation
- [ ] Mettre à jour MULTI_TENANT_ARCHITECTURE.md
- [ ] Mettre à jour SPRINT2_PLAN_DETAILLE.md
- [ ] Créer ARCHITECTURE_CHANGE_SUMMARY.md

---

## 🚀 Ordre d'Exécution

**Durée totale estimée :** 4-5 heures

1. **Phase 1** : Analyse (30 min)
2. **Phase 2** : Migration DB (15 min)
3. **Phase 3** : Models (60 min)
4. **Phase 4** : RLS adapté (90 min)
5. **Phase 5** : Seeders (30 min)
6. **Phase 6** : Documentation (45 min)
7. **Phase 7** : Tests (60 min)

---

## 💡 Prochaines Actions Immédiates

1. ✅ Valider cette approche avec l'équipe
2. ✅ Faire backup de la DB
3. ✅ Commencer Phase 1 : Analyse d'impact
4. ✅ Créer migration suppression `type`

---

**Document créé :** 9 novembre 2025
**Version :** 1.0
**Impact :** 🔥 MAJEUR - Changement architectural fondamental
**Status :** 📋 Approuvé - Prêt pour implémentation
