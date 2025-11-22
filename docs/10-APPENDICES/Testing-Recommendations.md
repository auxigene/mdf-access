# 🧪 Tests Complémentaires Recommandés - Sprint 1

## ⚠️ Ce qui a été testé vs Ce qui devrait être testé

### ✅ Tests Effectués (test_sprint1_relations.php)

**Niveau : Smoke Tests (Tests de fumée)**
- Vérification existence des relations
- Pas d'erreur SQL
- Count() basique
- Helpers simples

**Couverture estimée : 30%**

---

## ❌ Tests Manquants - À Ajouter

### 1. Tests de Relations Complètes

#### A. Tests avec Données Réelles
```php
// Créer un ProjectOrganization
$project = Project::first();
$org = Organization::find(27);

$projectOrg = ProjectOrganization::create([
    'project_id' => $project->id,
    'organization_id' => $org->id,
    'role' => 'moe',
    'status' => 'active',
    'is_primary' => true,
]);

// Tester les relations inverses
assert($project->projectOrganizations()->count() === 1);
assert($org->participations()->count() === 1);
assert($project->getSponsor() === null); // Pas de sponsor
assert($project->getPrimaryMoe()->id === $org->id); // MOE primaire
```

#### B. Tests N+1 Queries
```php
// Vérifier pas de N+1
DB::enableQueryLog();

$projects = Project::with('projectOrganizations.organization')->get();
$queryCount = count(DB::getQueryLog());

assert($queryCount <= 3); // 1 query projects, 1 projectOrgs, 1 orgs
```

#### C. Tests Pivot Relations
```php
// Tester withPivot()
$user = User::first();
$role = Role::first();

UserRole::create([
    'user_id' => $user->id,
    'role_id' => $role->id,
    'project_id' => $project->id,
]);

$userRoles = $user->roles()->get();
assert($userRoles->first()->pivot->project_id === $project->id);
```

---

### 2. Tests de Validation Métier

#### A. ProjectOrganization Business Rules
```php
// Test : Impossible d'avoir 2 sponsors actifs
$project = Project::first();
$org1 = Organization::find(1);
$org2 = Organization::find(2);

ProjectOrganization::create([
    'project_id' => $project->id,
    'organization_id' => $org1->id,
    'role' => 'sponsor',
    'status' => 'active',
]);

try {
    ProjectOrganization::create([
        'project_id' => $project->id,
        'organization_id' => $org2->id,
        'role' => 'sponsor',
        'status' => 'active',
    ]);
    assert(false); // Ne devrait jamais arriver ici
} catch (\Illuminate\Validation\ValidationException $e) {
    assert(true); // Exception attendue ✅
}
```

#### B. UserRole Scope Validation
```php
// Test : Impossible d'avoir project_id ET program_id en même temps
$user = User::first();
$role = Role::first();

try {
    UserRole::create([
        'user_id' => $user->id,
        'role_id' => $role->id,
        'project_id' => 1,
        'program_id' => 1, // INVALIDE !
    ]);
    assert(false);
} catch (\Illuminate\Validation\ValidationException $e) {
    assert(true); // Exception attendue ✅
}
```

---

### 3. Tests de Helpers Métier

#### A. User Permissions
```php
$user = User::first();
$role = Role::where('slug', 'project-manager')->first();

// Assigner rôle
UserRole::create([
    'user_id' => $user->id,
    'role_id' => $role->id,
]);

// Tester hasRole()
assert($user->hasRole('project-manager') === true);
assert($user->hasRole('super-admin') === false);

// Tester hasPermission()
assert($user->hasPermission('view_projects') === true);
assert($user->hasPermission('delete_users') === false);
```

#### B. Organization Helpers
```php
$org = Organization::find(27);

// Créer des participations de test
ProjectOrganization::create([
    'project_id' => 1,
    'organization_id' => $org->id,
    'role' => 'sponsor',
    'status' => 'active',
]);

ProjectOrganization::create([
    'project_id' => 2,
    'organization_id' => $org->id,
    'role' => 'moe',
    'status' => 'active',
]);

// Tester helpers
$sponsors = $org->projectsAsSponsor();
assert($sponsors->count() === 1);

$moes = $org->projectsAsMoe();
assert($moes->count() === 1);
```

---

### 4. Tests de Scopes Avancés

#### A. Global Scopes
```php
// Tester scope actif
$activeOrgs = Organization::active()->get();
$inactiveOrgs = Organization::inactive()->get();
$total = Organization::count();

assert($activeOrgs->count() + $inactiveOrgs->count() === $total);
```

#### B. Query Scopes
```php
// Tester scope internal
$internalOrgs = Organization::internal()->get();
assert($internalOrgs->every(fn($org) => $org->type === 'Internal'));

// Tester scope clients
$clients = Organization::clients()->get();
assert($clients->every(fn($org) => $org->type === 'Client'));
```

---

### 5. Tests de Performance

#### A. Eager Loading
```php
// Test performance avec/sans eager loading
$start = microtime(true);
$projects = Project::all();
foreach ($projects as $project) {
    $org = $project->clientOrganization; // N+1 query
}
$timeWithoutEager = microtime(true) - $start;

$start = microtime(true);
$projects = Project::with('clientOrganization')->get();
foreach ($projects as $project) {
    $org = $project->clientOrganization; // Pas de N+1
}
$timeWithEager = microtime(true) - $start;

assert($timeWithEager < $timeWithoutEager);
```

---

### 6. Tests d'Intégration

#### A. Scénario Complet : Créer Projet avec Organisations
```php
// Créer un nouveau projet
$project = Project::create([
    'name' => 'Projet Test',
    'code' => 'TEST001',
    'client_organization_id' => 1,
    'status' => 'initiation',
]);

// Ajouter sponsor
$sponsor = ProjectOrganization::create([
    'project_id' => $project->id,
    'organization_id' => 1,
    'role' => 'sponsor',
    'status' => 'active',
]);

// Ajouter MOA
$moa = ProjectOrganization::create([
    'project_id' => $project->id,
    'organization_id' => 2,
    'role' => 'moa',
    'status' => 'active',
]);

// Ajouter MOE primaire
$moe = ProjectOrganization::create([
    'project_id' => $project->id,
    'organization_id' => 3,
    'role' => 'moe',
    'status' => 'active',
    'is_primary' => true,
]);

// Vérifier helpers
assert($project->getSponsor()->id === 1);
assert($project->getMoa()->id === 2);
assert($project->getPrimaryMoe()->id === 3);
assert($project->projectOrganizations()->count() === 3);
```

---

## 📊 Estimation Couverture

| Type de Test | Actuel | Recommandé | Priorité |
|--------------|--------|------------|----------|
| **Relations basiques** | ✅ 30% | 100% | 🔥 HAUTE |
| **Business rules** | ❌ 0% | 100% | 🔥 CRITIQUE |
| **Helpers métier** | ✅ 20% | 100% | ⚡ HAUTE |
| **Performance** | ❌ 0% | 80% | 📋 MOYENNE |
| **Intégration** | ❌ 0% | 100% | 🔥 HAUTE |

---

## 🚀 Actions Recommandées

### Priorité 1 (Critique) - Sprint 2
- [ ] Tests validation ProjectOrganization (règles métier)
- [ ] Tests UserRole avec scopes
- [ ] Tests hasPermission() avec données réelles

### Priorité 2 (Haute) - Sprint 6
- [ ] Tests relations complètes avec données
- [ ] Tests N+1 queries
- [ ] Tests scénarios complets (création projet)

### Priorité 3 (Moyenne) - Sprint 7
- [ ] Tests performance
- [ ] Tests coverage complet
- [ ] Tests edge cases

---

## 📝 Conclusion

Les tests effectués dans Sprint 1 étaient **suffisants pour valider la structure**, mais **insuffisants pour la production**.

**Recommandation :** Ajouter tests complets au **Sprint 6 (Tests)** comme prévu dans le roadmap.

Pour l'instant, nous pouvons continuer avec Sprint 2 (RLS) en sachant que :
- ✅ Les relations existent et sont syntaxiquement correctes
- ✅ Pas d'erreur SQL évidente
- ⚠️ Validation métier à tester plus tard
- ⚠️ Tests unitaires/Feature à ajouter Sprint 6-7
