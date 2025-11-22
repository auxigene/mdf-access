# 🏗️ Sprint 2 - Architecture Multi-Tenant Pure

**Date :** 20 novembre 2025
**Objectif :** Adapter le code du Sprint 2 (RLS) pour une architecture multi-tenant PURE
**Statut :** ✅ Complété

---

## 🎯 Principe Fondamental

> **Architecture Multi-Tenant Pure** : Toutes les organisations sont traitées de manière égale. Seul le System Admin a un accès privilégié. Aucune exception organisationnelle.

### Ce que cela signifie

- ✅ **SAMSIC** voit uniquement les projets où elle participe (comme tout le monde)
- ✅ **Clients** voient uniquement les projets où ils participent
- ✅ **Partenaires** voient uniquement les projets où ils participent
- ✅ **System Admin** est le SEUL à avoir un bypass complet

---

## 📊 Architecture Réelle (DB Backup)

### Structure de la Base de Données

```sql
-- Table organizations : PAS de colonne 'type' ou 'is_internal'
CREATE TABLE organizations (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    status VARCHAR(255) DEFAULT 'active',
    address TEXT,
    ville VARCHAR(255),
    contact_info JSON,
    logo VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Table project_organizations : Définit les rôles contextuels
CREATE TABLE project_organizations (
    id BIGSERIAL PRIMARY KEY,
    project_id BIGINT NOT NULL,
    organization_id BIGINT NOT NULL,
    role VARCHAR(255) NOT NULL, -- 'sponsor', 'moa', 'moe', 'subcontractor'
    reference VARCHAR(255),
    scope_description TEXT,
    is_primary BOOLEAN DEFAULT FALSE,
    start_date DATE,
    end_date DATE,
    status VARCHAR(255) DEFAULT 'active',
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Rôles Contextuels

Une organisation peut avoir **différents rôles** selon les projets :

| Organisation | Projet A | Projet B | Projet C |
|--------------|----------|----------|----------|
| SAMSIC | sponsor | moe | subcontractor |
| Client XYZ | sponsor | - | - |
| Partenaire ABC | - | moe | subcontractor |

---

## 🔐 Système RLS (Row-Level Security)

### Logique de Filtrage

```
┌─────────────────────────────────────────────────────┐
│ Requête Eloquent (ex: Project::all())              │
└────────────────────┬────────────────────────────────┘
                     │
                     ▼
            ┌────────────────────┐
            │   TenantScope      │
            │   (Global Scope)   │
            └────────┬───────────┘
                     │
                     ▼
            ┌─────────────────────┐
            │ Auth::user()        │
            │ existe ?            │
            └────────┬────────────┘
                     │
         ┌───────────┴───────────┐
         │ NON                   │ OUI
         ▼                       ▼
    ┌────────┐         ┌──────────────────┐
    │ Bypass │         │ isSystemAdmin()? │
    └────────┘         └────────┬─────────┘
                                │
                    ┌───────────┴──────────┐
                    │ OUI                  │ NON
                    ▼                      ▼
              ┌──────────┐      ┌─────────────────────────┐
              │  Bypass  │      │ Filtre sur participations│
              │ (voit    │      │ (project_organizations)  │
              │  tout)   │      └──────────┬──────────────┘
              └──────────┘                 │
                                           ▼
                                ┌─────────────────────────┐
                                │ WHERE EXISTS (          │
                                │   SELECT 1              │
                                │   FROM project_orgs     │
                                │   WHERE project_id = X  │
                                │   AND org_id = user.org │
                                │   AND status = 'active' │
                                │ )                       │
                                └─────────────────────────┘
```

### Deux Cas Seulement

#### 1. System Admin (Bypass)

```php
if ($user->isSystemAdmin()) {
    return; // Pas de filtre - voit tout
}
```

**Qui :** Users avec `is_system_admin = true`
**Accès :** TOUS les projets, sans restriction
**Usage :** Super-administrateurs de la plateforme

#### 2. Toutes les Organisations (Filtrées)

```php
// TOUTES les organisations (y compris SAMSIC)
$this->applyParticipationFilter($builder, $user);
```

**Qui :** Tous les autres utilisateurs (avec `organization_id`)
**Accès :** Uniquement les projets où leur organisation participe
**Filtre :** `project_organizations.status = 'active'`

---

## 💻 Implémentation

### 1. Model User

```php
class User extends Authenticatable
{
    /**
     * Vérifier si l'utilisateur est un administrateur système
     */
    public function isSystemAdmin(): bool
    {
        return $this->is_system_admin === true;
    }

    // PAS de méthode isInternal(), isClient(), isPartner()
    // Ces concepts n'existent plus dans l'architecture pure
}
```

**Changements :**
- ✅ Garde uniquement `isSystemAdmin()`
- ❌ Supprime `isInternal()` (pas de notion d'organisation spéciale)
- ❌ Supprime `isClient()` et `isPartner()` (rôles contextuels dans project_organizations)

### 2. Model Organization

```php
class Organization extends Model
{
    protected $fillable = [
        'name',
        'address',
        'ville',
        'contact_info',
        'logo',
        'status',
        // PAS de 'type' ou 'is_internal'
    ];

    // Pas de méthode isInternal()
}
```

**Changements :**
- ❌ Pas de colonne `type` (supprimée par migration précédente)
- ❌ Pas de colonne `is_internal` (concept non générique)
- ❌ Pas de méthode `isInternal()`

### 3. TenantScope (Global Scope)

```php
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = Auth::user();

        if (!$user) {
            return; // Pas d'utilisateur = pas de filtre
        }

        // SEUL bypass : System Admin
        if ($user->isSystemAdmin()) {
            return;
        }

        // TOUTES les organisations : filtrées sur participations
        $this->applyParticipationFilter($builder, $user);
    }

    protected function applyParticipationFilter(Builder $builder, $user): void
    {
        if (!$user->organization_id) {
            $builder->whereRaw('1 = 0');
            return;
        }

        $tableName = $builder->getModel()->getTable();

        if ($tableName === 'projects') {
            $builder->whereExists(function ($query) use ($user) {
                $query->select(\DB::raw(1))
                      ->from('project_organizations')
                      ->whereColumn('project_organizations.project_id', 'projects.id')
                      ->where('project_organizations.organization_id', $user->organization_id)
                      ->where('project_organizations.status', 'active');
            });
        } elseif ($this->hasColumn($tableName, 'project_id')) {
            $builder->whereHas('project', function ($query) use ($user) {
                $query->whereExists(function ($subQuery) use ($user) {
                    $subQuery->select(\DB::raw(1))
                             ->from('project_organizations')
                             ->whereColumn('project_organizations.project_id', 'projects.id')
                             ->where('project_organizations.organization_id', $user->organization_id)
                             ->where('project_organizations.status', 'active');
                });
            });
        } else {
            $builder->whereRaw('1 = 0');
        }
    }
}
```

**Changements :**
- ✅ Un seul filtre pour tous (sauf System Admin)
- ❌ Plus de distinction Internal/Client/Partner
- ✅ Code plus simple et maintenable

### 4. Middleware CheckTenantAccess

```php
class CheckTenantAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // System Admin : seule exception
        if ($user->isSystemAdmin()) {
            return $next($request);
        }

        // TOUTES les organisations : vérifications multi-tenant
        if (!$user->organization_id) {
            abort(403, 'Utilisateur sans organisation assignée');
        }

        if (!$user->organization || !$user->organization->isActive()) {
            abort(403, 'Organisation inactive ou inexistante');
        }

        return $next($request);
    }
}
```

**Changements :**
- ❌ Plus de bypass pour "Internal"
- ✅ Toutes les organisations ont les mêmes vérifications

---

## 🧪 Tests

### Script de Test

**Fichier :** `test_sprint2_rls_pure_multitenant.php`

```bash
php test_sprint2_rls_pure_multitenant.php
```

### Tests Couverts

| Test | Description | Résultat Attendu |
|------|-------------|------------------|
| **Test 1** | System Admin | Voit TOUS les projets ✅ |
| **Test 2** | User SAMSIC (non admin) | Voit uniquement participations SAMSIC ✅ |
| **Test 3** | Organisation avec participations | Voit uniquement ses participations ✅ |
| **Test 4** | Organisation sans participations | Voit 0 projets ✅ |
| **Test 5** | withoutTenantScope() | Bypass du scope fonctionne ✅ |

---

## 🎯 Cas d'Usage SAMSIC

### Option 1 : System Admin (Recommandé pour super-users)

```php
// Créer un user SAMSIC avec accès complet
User::create([
    'name' => 'Admin SAMSIC',
    'email' => 'admin@samsic.ma',
    'password' => bcrypt('password'),
    'is_system_admin' => true,      // ← Bypass RLS
    'organization_id' => 1,          // SAMSIC
]);
```

**Quand utiliser :**
- Super-administrateurs de la plateforme
- Users qui doivent gérer TOUS les projets
- Accès complet sans restriction

### Option 2 : Participer à Tous les Projets (Multi-tenant pur)

```php
// Ajouter SAMSIC à tous les projets
$projects = Project::all();
foreach ($projects as $project) {
    ProjectOrganization::firstOrCreate([
        'project_id' => $project->id,
        'organization_id' => 1, // SAMSIC
        'role' => 'moe',
        'status' => 'active',
    ]);
}

// User SAMSIC normal
User::create([
    'name' => 'User SAMSIC',
    'email' => 'user@samsic.ma',
    'password' => bcrypt('password'),
    'is_system_admin' => false,     // ← Filtré
    'organization_id' => 1,          // SAMSIC
]);
// → Ce user voit tous les projets via participations
```

**Quand utiliser :**
- Respect strict du multi-tenant
- SAMSIC doit être enregistrée comme participant
- Traçabilité et audit complets

---

## ✅ Avantages de l'Architecture Pure

### 1. Généricité

- ✅ Aucune logique spécifique à une organisation
- ✅ Code réutilisable pour d'autres clients
- ✅ Pas de "magic values" ou d'exceptions

### 2. Simplicité

- ✅ 2 cas au lieu de 4 (System Admin / Filtrés)
- ✅ Moins de code à maintenir
- ✅ Logique claire et compréhensible

### 3. Sécurité

- ✅ Isolation stricte des données
- ✅ Pas d'accès non autorisé par défaut
- ✅ Principe du moindre privilège

### 4. Flexibilité

- ✅ Les rôles sont contextuels (par projet)
- ✅ Une organisation peut avoir plusieurs rôles
- ✅ Évolution facile des besoins métier

### 5. Maintenabilité

- ✅ Pas de dépendance à des organisations spécifiques
- ✅ Tests plus simples
- ✅ Refactoring plus facile

---

## 📋 Comparaison Architectures

| Aspect | Architecture Précédente | Architecture Pure |
|--------|------------------------|-------------------|
| **Bypass RLS** | System Admin + Internal | System Admin uniquement |
| **is_internal** | Colonne dans organizations | ❌ N'existe pas |
| **Logique SAMSIC** | Codée en dur | Via is_system_admin ou participations |
| **Cas de filtrage** | 4 cas (Admin, Internal, Client, Partner) | 2 cas (Admin, Filtrés) |
| **Complexité** | Élevée | Faible |
| **Généricité** | Faible (spécifique SAMSIC) | Élevée (réutilisable) |
| **Maintenance** | Complexe | Simple |

---

## 🚀 Déploiement

### Étapes

1. **Pas de migration nécessaire**
   - La structure DB est déjà correcte (pas de colonne type/is_internal)
   - Aucune modification de schéma requise

2. **Code mis à jour**
   - Models : User, Organization
   - Scopes : TenantScope
   - Middleware : CheckTenantAccess

3. **Tests**
   ```bash
   php test_sprint2_rls_pure_multitenant.php
   ```

4. **Configuration SAMSIC**
   - Décider : System Admin ou Participations ?
   - Appliquer la stratégie choisie

---

## 💡 Recommandations

### Pour SAMSIC

**Stratégie Hybride (Recommandée) :**

1. **Super-admins SAMSIC** : `is_system_admin = true`
   - Pour les users qui gèrent la plateforme
   - Accès complet sans restriction

2. **Users SAMSIC normaux** : `is_system_admin = false`
   - Filtrés sur les projets où SAMSIC participe
   - Respect du multi-tenant
   - Ajout de SAMSIC dans `project_organizations` des projets concernés

### Pour les Autres Organisations

**Toujours filtré** : Seule la participation dans `project_organizations` détermine l'accès

---

## 📝 Résumé Technique

### Fichiers Modifiés

- ✅ `app/Models/User.php` : Suppression isInternal(), isClient(), isPartner()
- ✅ `app/Models/Organization.php` : Suppression is_internal
- ✅ `app/Scopes/TenantScope.php` : Logique simplifiée (2 cas)
- ✅ `app/Http/Middleware/CheckTenantAccess.php` : Suppression bypass Internal
- ✅ `test_sprint2_rls_pure_multitenant.php` : Tests adaptés
- ✅ `docs/SPRINT2_MULTITENANT_PUR.md` : Documentation complète

### Migrations

- ❌ **Aucune migration nécessaire**
- La structure DB réelle est déjà correcte (pas de colonne type/is_internal)

---

**Document créé :** 20 novembre 2025
**Version :** 2.0
**Auteur :** Équipe Dev MDF Access
**Status :** ✅ Architecture Multi-Tenant Pure implémentée
