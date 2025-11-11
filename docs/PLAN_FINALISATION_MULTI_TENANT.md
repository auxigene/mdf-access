# Plan de Finalisation du Système Multi-Tenant

**Date** : 2025-11-08
**Version** : 1.0
**Objectif** : Finaliser l'implémentation complète du système multi-tenant avec RLS et RBAC

---

## État actuel (Checkpoint)

### ✅ Complété

**Architecture et conception**
- [x] Documentation architecture multi-tenant (MULTI_TENANT_ARCHITECTURE.md)
- [x] Documentation multi-organisations (MULTI_TENANT_MULTI_ORGANISATIONS.md)
- [x] Documentation rôles et permissions (ROLES_AND_PERMISSIONS.md)

**Migrations de base de données**
- [x] 11 migrations multi-tenant créées
- [x] 4 migrations multi-organisations créées
- [x] Contraintes métier DB (CHECK, partial unique indexes)

**Permissions et rôles**
- [x] 174 permissions définies (PermissionsSeeder)
- [x] 29 rôles définis (3 seeders : RolesSeeder, ProjectOrganizationsRolesSeeder, ClientMoaRolesSeeder)

### ❌ En attente

**Base de données**
- [ ] Exécution des migrations
- [ ] Exécution des seeders

**Application (Backend)**
- [ ] Models avec relations
- [ ] RLS application layer (Traits, Scopes, Middleware)
- [ ] Services métier
- [ ] Requests de validation
- [ ] Controllers et API endpoints
- [ ] Policies Laravel
- [ ] Tests

**Interface (Frontend)**
- [ ] UI gestion organisations
- [ ] UI attribution rôles
- [ ] UI gestion permissions

---

## Phase 1 : Fondations DB (PRIORITÉ CRITIQUE)

**Durée estimée** : 30 minutes
**Objectif** : Mettre en place la structure DB complète

### 1.1 Exécution des migrations ⚡ IMMÉDIAT

```bash
# Vérifier l'état des migrations
php artisan migrate:status

# Exécuter toutes les migrations en attente
php artisan migrate

# Vérifier que tout est OK
php artisan migrate:status
```

**Migrations à exécuter** (15 migrations) :
1. Multi-tenant de base (11)
   - add_client_organization_id_to_projects
   - add_tenant_fields_to_users
   - create_roles, create_permissions, create_role_permission
   - create_user_roles, add_scope_check_constraint
   - add_client_reference, rename_organization_id
   - replace_user_type_with_is_system_admin

2. Multi-organisations (4)
   - create_project_organizations_table
   - add_assigned_organization_to_scope_items
   - remove_executor_columns_from_projects_table
   - add_business_constraints_to_project_organizations_table

**Validation** :
```bash
# Vérifier les tables créées
php artisan db:show

# Vérifier les contraintes
psql -U postgres -d mdf_access -c "\d+ project_organizations"
```

### 1.2 Exécution des seeders ⚡ IMMÉDIAT

```bash
# 1. Permissions (170 + 4)
php artisan db:seed --class=PermissionsSeeder
php artisan db:seed --class=ProjectOrganizationsPermissionsSeeder

# 2. Rôles (25 + 4)
php artisan db:seed --class=RolesSeeder
php artisan db:seed --class=ProjectOrganizationsRolesSeeder
php artisan db:seed --class=ClientMoaRolesSeeder

# Vérifier les données
php artisan tinker
>>> \DB::table('permissions')->count()
>>> \DB::table('roles')->count()
>>> \DB::table('role_permission')->count()
```

**Validation** :
- 174 permissions créées
- 29 rôles créés
- Associations role_permission correctes

### 1.3 Données de test (optionnel mais recommandé)

Créer un seeder pour données de test :
- [ ] 1 organisation Internal (SAMSIC)
- [ ] 2 organisations Client
- [ ] 2 organisations Partner
- [ ] 3-5 utilisateurs de test avec rôles variés
- [ ] 2-3 projets de test

**Fichier à créer** : `database/seeders/TestDataSeeder.php`

---

## Phase 2 : Models et Relations (PRIORITÉ HAUTE)

**Durée estimée** : 3-4 heures
**Objectif** : Créer tous les Models Eloquent avec relations complètes

### 2.1 Models principaux

#### a) Model Organization
**Fichier** : `app/Models/Organization.php`

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'code', 'type', 'address',
        'contact_name', 'contact_email', 'contact_phone',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relations
    public function users() {
        return $this->hasMany(User::class);
    }

    public function clientProjects() {
        return $this->hasMany(Project::class, 'client_organization_id');
    }

    public function projectOrganizations() {
        return $this->hasMany(ProjectOrganization::class);
    }

    public function projects() {
        return $this->belongsToMany(Project::class, 'project_organizations')
                    ->withPivot('role', 'reference', 'scope_description', 'is_primary', 'status', 'start_date', 'end_date')
                    ->withTimestamps();
    }

    // Scopes
    public function scopeActive($query) {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, string $type) {
        return $query->where('type', $type);
    }
}
```

**Checklist** :
- [ ] Créer Model Organization
- [ ] Ajouter relations
- [ ] Ajouter scopes
- [ ] Ajouter casts
- [ ] Tester dans tinker

#### b) Model User
**Fichier** : `app/Models/User.php`

```php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name', 'email', 'password',
        'organization_id', 'is_system_admin'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_system_admin' => 'boolean',
        'password' => 'hashed',
    ];

    // Relations
    public function organization() {
        return $this->belongsTo(Organization::class);
    }

    public function userRoles() {
        return $this->hasMany(UserRole::class);
    }

    public function roles() {
        return $this->belongsToMany(Role::class, 'user_roles')
                    ->withPivot('portfolio_id', 'program_id', 'project_id')
                    ->withTimestamps();
    }

    // Helpers permissions
    public function hasPermission(string $permissionSlug, ?Model $scope = null): bool {
        // À implémenter (voir Phase 3)
    }

    public function hasRole(string $roleSlug): bool {
        return $this->roles()->where('slug', $roleSlug)->exists();
    }

    public function isSystemAdmin(): bool {
        return $this->is_system_admin === true;
    }

    public function isInternal(): bool {
        return $this->organization?->type === 'Internal';
    }

    public function isClient(): bool {
        return $this->organization?->type === 'Client';
    }

    public function isPartner(): bool {
        return $this->organization?->type === 'Partner';
    }
}
```

**Checklist** :
- [ ] Mettre à jour Model User
- [ ] Ajouter relations organization, userRoles, roles
- [ ] Ajouter helpers permissions (squelette)
- [ ] Ajouter helpers isSystemAdmin, isInternal, isClient, isPartner
- [ ] Tester dans tinker

#### c) Model ProjectOrganization (NOUVEAU)
**Fichier** : `app/Models/ProjectOrganization.php`

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectOrganization extends Model
{
    protected $fillable = [
        'project_id', 'organization_id', 'role', 'reference',
        'scope_description', 'is_primary', 'start_date',
        'end_date', 'status'
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    // Relations
    public function project() {
        return $this->belongsTo(Project::class);
    }

    public function organization() {
        return $this->belongsTo(Organization::class);
    }

    // Scopes
    public function scopeActive($query) {
        return $query->where('status', 'active');
    }

    public function scopeOfRole($query, string $role) {
        return $query->where('role', $role);
    }

    public function scopePrimary($query) {
        return $query->where('is_primary', true);
    }

    // Helpers
    public function isSponsor(): bool {
        return $this->role === 'sponsor';
    }

    public function isMoa(): bool {
        return $this->role === 'moa';
    }

    public function isMoe(): bool {
        return $this->role === 'moe';
    }

    public function isSubcontractor(): bool {
        return $this->role === 'subcontractor';
    }
}
```

**Checklist** :
- [ ] Créer Model ProjectOrganization
- [ ] Ajouter relations project, organization
- [ ] Ajouter scopes (active, ofRole, primary)
- [ ] Ajouter helpers (isSponsor, isMoa, isMoe, isSubcontractor)
- [ ] Tester dans tinker

#### d) Model Permission
**Fichier** : `app/Models/Permission.php`

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'resource', 'action'
    ];

    // Relations
    public function roles() {
        return $this->belongsToMany(Role::class, 'role_permission')
                    ->withTimestamps();
    }
}
```

**Checklist** :
- [ ] Créer Model Permission
- [ ] Ajouter relation roles

#### e) Model Role
**Fichier** : `app/Models/Role.php`

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'scope', 'organization_id'
    ];

    // Relations
    public function permissions() {
        return $this->belongsToMany(Permission::class, 'role_permission')
                    ->withTimestamps();
    }

    public function organization() {
        return $this->belongsTo(Organization::class);
    }

    public function userRoles() {
        return $this->hasMany(UserRole::class);
    }

    public function users() {
        return $this->belongsToMany(User::class, 'user_roles')
                    ->withPivot('portfolio_id', 'program_id', 'project_id')
                    ->withTimestamps();
    }

    // Helpers
    public function hasPermission(string $permissionSlug): bool {
        return $this->permissions()->where('slug', $permissionSlug)->exists();
    }

    public function isGlobal(): bool {
        return $this->scope === 'global';
    }

    public function isOrganization(): bool {
        return $this->scope === 'organization';
    }

    public function isProject(): bool {
        return $this->scope === 'project';
    }
}
```

**Checklist** :
- [ ] Créer Model Role
- [ ] Ajouter relations permissions, organization, userRoles, users
- [ ] Ajouter helpers

#### f) Model UserRole
**Fichier** : `app/Models/UserRole.php`

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    protected $fillable = [
        'user_id', 'role_id',
        'portfolio_id', 'program_id', 'project_id'
    ];

    // Relations
    public function user() {
        return $this->belongsTo(User::class);
    }

    public function role() {
        return $this->belongsTo(Role::class);
    }

    public function portfolio() {
        return $this->belongsTo(Portfolio::class);
    }

    public function program() {
        return $this->belongsTo(Program::class);
    }

    public function project() {
        return $this->belongsTo(Project::class);
    }

    // Helpers
    public function hasScope(): bool {
        return $this->portfolio_id !== null
            || $this->program_id !== null
            || $this->project_id !== null;
    }

    public function scopeLevel(): ?string {
        if ($this->project_id) return 'project';
        if ($this->program_id) return 'program';
        if ($this->portfolio_id) return 'portfolio';
        return null;
    }
}
```

**Checklist** :
- [ ] Créer Model UserRole
- [ ] Ajouter relations
- [ ] Ajouter helpers

### 2.2 Mettre à jour Models existants

#### Project Model
**Fichier** : `app/Models/Project.php`

Ajouter :
```php
// Relations multi-organisations
public function projectOrganizations() {
    return $this->hasMany(ProjectOrganization::class);
}

public function organizations() {
    return $this->belongsToMany(Organization::class, 'project_organizations')
                ->withPivot('role', 'reference', 'scope_description', 'is_primary', 'status', 'start_date', 'end_date')
                ->withTimestamps();
}

public function clientOrganization() {
    return $this->belongsTo(Organization::class, 'client_organization_id');
}

// Helpers organisations
public function getSponsor() {
    return $this->projectOrganizations()->ofRole('sponsor')->active()->first();
}

public function getMoa() {
    return $this->projectOrganizations()->ofRole('moa')->active()->first();
}

public function getPrimaryMoe() {
    return $this->projectOrganizations()->ofRole('moe')->primary()->active()->first();
}

public function getSubcontractors() {
    return $this->projectOrganizations()->ofRole('subcontractor')->active()->get();
}
```

**Checklist** :
- [ ] Mettre à jour Project Model
- [ ] Ajouter relations projectOrganizations, organizations, clientOrganization
- [ ] Ajouter helpers (getSponsor, getMoa, getPrimaryMoe, getSubcontractors)
- [ ] Tester dans tinker

#### Deliverable, Task, WbsElement Models
Ajouter à chacun :
```php
public function assignedOrganization() {
    return $this->belongsTo(Organization::class, 'assigned_organization_id');
}
```

**Checklist** :
- [ ] Mettre à jour Deliverable Model
- [ ] Mettre à jour Task Model
- [ ] Mettre à jour WbsElement Model

---

## Phase 3 : RLS Application Layer (PRIORITÉ HAUTE)

**Durée estimée** : 4-6 heures
**Objectif** : Implémenter le filtrage multi-tenant automatique

### 3.1 Trait TenantScoped
**Fichier** : `app/Traits/TenantScoped.php`

```php
namespace App\Traits;

use App\Scopes\TenantScope;

trait TenantScoped
{
    protected static function bootTenantScoped()
    {
        static::addGlobalScope(new TenantScope);
    }
}
```

**Checklist** :
- [ ] Créer Trait TenantScoped

### 3.2 Global Scope TenantScope
**Fichier** : `app/Scopes/TenantScope.php`

```php
namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        // Ne pas filtrer si pas d'utilisateur authentifié
        if (!Auth::check()) {
            return;
        }

        $user = Auth::user();

        // Bypass pour system admin
        if ($user->is_system_admin) {
            return;
        }

        $organization = $user->organization;

        // Bypass pour organisation Internal (SAMSIC)
        if ($organization && $organization->type === 'Internal') {
            return;
        }

        // Filtre pour organisations Client
        if ($organization && $organization->type === 'Client') {
            $builder->where($model->getTable() . '.client_organization_id', $organization->id);
            return;
        }

        // Filtre pour organisations Partner
        if ($organization && $organization->type === 'Partner') {
            $builder->whereExists(function ($query) use ($model, $organization) {
                $query->select(\DB::raw(1))
                      ->from('project_organizations')
                      ->whereColumn('project_organizations.project_id', $model->getTable() . '.id')
                      ->where('project_organizations.organization_id', $organization->id)
                      ->whereIn('project_organizations.role', ['moa', 'moe', 'subcontractor']);
            });
            return;
        }
    }
}
```

**Checklist** :
- [ ] Créer Global Scope TenantScope
- [ ] Implémenter logique RLS (Internal bypass, Client filter, Partner filter)
- [ ] Tester avec différents types d'utilisateurs

### 3.3 Appliquer le Trait aux Models

Ajouter `use TenantScoped;` aux models :
```php
class Project extends Model
{
    use TenantScoped;
    // ...
}
```

**Checklist** :
- [ ] Appliquer à Project
- [ ] Appliquer à Task
- [ ] Appliquer à Deliverable
- [ ] Appliquer à WbsElement
- [ ] Appliquer à Budget
- [ ] Appliquer à Risk
- [ ] Appliquer à Issue
- [ ] etc. (tous les models liés à des projets)

### 3.4 Middleware CheckTenantAccess
**Fichier** : `app/Http/Middleware/CheckTenantAccess.php`

```php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Permettre si system admin
        if ($user && $user->is_system_admin) {
            return $next($request);
        }

        // Vérifier l'accès tenant selon le type d'organisation
        // (Logique supplémentaire si nécessaire)

        return $next($request);
    }
}
```

**Checklist** :
- [ ] Créer Middleware CheckTenantAccess
- [ ] Enregistrer dans `app/Http/Kernel.php`
- [ ] Appliquer aux routes API

### 3.5 Helpers permissions dans User Model

**Fichier** : `app/Models/User.php`

```php
public function hasPermission(string $permissionSlug, ?Model $scope = null): bool
{
    // System admin bypass
    if ($this->is_system_admin) {
        return true;
    }

    // Récupérer les rôles de l'utilisateur
    $userRoles = $this->userRoles;

    // Filtrer par scope si fourni
    if ($scope !== null) {
        if ($scope instanceof Project) {
            $userRoles = $userRoles->filter(function ($userRole) use ($scope) {
                return $userRole->project_id === $scope->id
                    || $userRole->project_id === null;
            });
        } elseif ($scope instanceof Program) {
            $userRoles = $userRoles->filter(function ($userRole) use ($scope) {
                return $userRole->program_id === $scope->id
                    || $userRole->program_id === null;
            });
        } elseif ($scope instanceof Portfolio) {
            $userRoles = $userRoles->filter(function ($userRole) use ($scope) {
                return $userRole->portfolio_id === $scope->id
                    || $userRole->portfolio_id === null;
            });
        }
    }

    // Vérifier si un des rôles a la permission
    foreach ($userRoles as $userRole) {
        $role = $userRole->role;
        if ($role->hasPermission($permissionSlug)) {
            return true;
        }
    }

    return false;
}

public function can($ability, $arguments = [])
{
    // Intégration avec le système Laravel de permissions
    if ($this->hasPermission($ability, $arguments[0] ?? null)) {
        return true;
    }

    return parent::can($ability, $arguments);
}
```

**Checklist** :
- [ ] Implémenter hasPermission() complet
- [ ] Overrider can() pour intégration Laravel
- [ ] Tester avec différents scopes

---

## Phase 4 : Services et Validation (PRIORITÉ MOYENNE)

**Durée estimée** : 4-5 heures
**Objectif** : Implémenter la logique métier et validation

### 4.1 ProjectOrganizationService
**Fichier** : `app/Services/ProjectOrganizationService.php`

```php
namespace App\Services;

use App\Models\Project;
use App\Models\ProjectOrganization;
use Illuminate\Support\Facades\DB;

class ProjectOrganizationService
{
    /**
     * Valider les règles métier pour les organisations d'un projet
     */
    public function validateProjectOrganizations(Project $project): array
    {
        $errors = [];

        $activeOrgs = $project->projectOrganizations()->active()->get();

        // Règle 1: Exactement UN sponsor actif
        $sponsors = $activeOrgs->where('role', 'sponsor');
        if ($sponsors->count() === 0) {
            $errors[] = 'Le projet doit avoir exactement UN sponsor actif';
        } elseif ($sponsors->count() > 1) {
            $errors[] = 'Le projet ne peut avoir qu\'UN SEUL sponsor actif';
        }

        // Règle 2: Exactement UN MOA actif
        $moas = $activeOrgs->where('role', 'moa');
        if ($moas->count() === 0) {
            $errors[] = 'Le projet doit avoir exactement UN MOA actif';
        } elseif ($moas->count() > 1) {
            $errors[] = 'Le projet ne peut avoir qu\'UN SEUL MOA actif';
        }

        // Règle 3: Au moins UN MOE actif
        $moes = $activeOrgs->whereIn('role', ['moe', 'subcontractor']);
        if ($moes->count() === 0) {
            $errors[] = 'Le projet doit avoir AU MOINS UN MOE actif';
        }

        // Règle 4: Si plusieurs MOE, UN SEUL primary
        if ($moes->count() > 1) {
            $primaryMoes = $moes->where('is_primary', true);
            if ($primaryMoes->count() === 0) {
                $errors[] = 'Le projet avec plusieurs MOE doit avoir UN MOE primary';
            } elseif ($primaryMoes->count() > 1) {
                $errors[] = 'Le projet ne peut avoir qu\'UN SEUL MOE primary';
            }
        }

        // Règle 5: Subcontractor DOIT avoir scope_description
        $subcontractors = $activeOrgs->where('role', 'subcontractor');
        foreach ($subcontractors as $sub) {
            if (empty($sub->scope_description)) {
                $errors[] = "Le sous-traitant {$sub->organization->name} doit avoir une description de scope";
            }
        }

        return $errors;
    }

    /**
     * Ajouter une organisation au projet
     */
    public function addOrganization(
        Project $project,
        int $organizationId,
        string $role,
        ?string $reference = null,
        ?string $scopeDescription = null,
        bool $isPrimary = false,
        ?string $startDate = null,
        ?string $endDate = null
    ): ProjectOrganization {
        // Validation métier
        $this->validateBeforeAdd($project, $organizationId, $role, $isPrimary, $scopeDescription);

        return ProjectOrganization::create([
            'project_id' => $project->id,
            'organization_id' => $organizationId,
            'role' => $role,
            'reference' => $reference,
            'scope_description' => $scopeDescription,
            'is_primary' => $isPrimary,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'active',
        ]);
    }

    private function validateBeforeAdd(
        Project $project,
        int $organizationId,
        string $role,
        bool $isPrimary,
        ?string $scopeDescription
    ): void {
        // Vérifier que le sponsor/moa n'existe pas déjà
        if (in_array($role, ['sponsor', 'moa'])) {
            $exists = $project->projectOrganizations()
                ->where('role', $role)
                ->where('status', 'active')
                ->exists();

            if ($exists) {
                throw new \Exception("Un {$role} actif existe déjà pour ce projet");
            }
        }

        // Vérifier qu'un subcontractor a un scope_description
        if ($role === 'subcontractor' && empty($scopeDescription)) {
            throw new \Exception("Un sous-traitant doit avoir une description de scope");
        }

        // Vérifier is_primary cohérent
        if ($isPrimary && !in_array($role, ['moe', 'subcontractor'])) {
            throw new \Exception("Seul un MOE ou subcontractor peut être primary");
        }
    }
}
```

**Checklist** :
- [ ] Créer ProjectOrganizationService
- [ ] Implémenter validateProjectOrganizations()
- [ ] Implémenter addOrganization()
- [ ] Implémenter updateOrganization()
- [ ] Implémenter removeOrganization()
- [ ] Tests unitaires

### 4.2 Form Requests

#### StoreProjectOrganizationRequest
**Fichier** : `app/Http/Requests/StoreProjectOrganizationRequest.php`

```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectOrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('create_project_organizations');
    }

    public function rules(): array
    {
        return [
            'organization_id' => 'required|exists:organizations,id',
            'role' => 'required|in:sponsor,moa,moe,subcontractor',
            'reference' => 'nullable|string|max:255',
            'scope_description' => 'nullable|string',
            'is_primary' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'in:active,inactive,completed',
        ];
    }

    public function messages(): array
    {
        return [
            'organization_id.required' => 'L\'organisation est obligatoire',
            'organization_id.exists' => 'L\'organisation n\'existe pas',
            'role.required' => 'Le rôle est obligatoire',
            'role.in' => 'Le rôle doit être sponsor, moa, moe ou subcontractor',
            'end_date.after_or_equal' => 'La date de fin doit être après la date de début',
        ];
    }
}
```

**Checklist** :
- [ ] Créer StoreProjectOrganizationRequest
- [ ] Créer UpdateProjectOrganizationRequest
- [ ] Créer StoreUserRoleRequest
- [ ] Créer UpdateUserRoleRequest

### 4.3 Policies Laravel

#### ProjectPolicy
**Fichier** : `app/Policies/ProjectPolicy.php`

```php
namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view_projects');
    }

    public function view(User $user, Project $project): bool
    {
        return $user->hasPermission('view_projects', $project);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create_projects');
    }

    public function update(User $user, Project $project): bool
    {
        return $user->hasPermission('edit_projects', $project);
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->hasPermission('delete_projects', $project);
    }

    public function approve(User $user, Project $project): bool
    {
        return $user->hasPermission('approve_projects', $project);
    }
}
```

**Checklist** :
- [ ] Créer ProjectPolicy
- [ ] Créer ProjectOrganizationPolicy
- [ ] Créer UserRolePolicy
- [ ] Enregistrer dans AuthServiceProvider

---

## Phase 5 : Controllers et API (PRIORITÉ MOYENNE)

**Durée estimée** : 4-6 heures
**Objectif** : Exposer les fonctionnalités via API REST

### 5.1 ProjectOrganizationController
**Fichier** : `app/Http/Controllers/Api/ProjectOrganizationController.php`

```php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectOrganizationRequest;
use App\Http\Requests\UpdateProjectOrganizationRequest;
use App\Models\Project;
use App\Models\ProjectOrganization;
use App\Services\ProjectOrganizationService;

class ProjectOrganizationController extends Controller
{
    public function __construct(
        private ProjectOrganizationService $service
    ) {}

    public function index(Project $project)
    {
        $this->authorize('view', $project);

        return response()->json([
            'data' => $project->projectOrganizations()->with('organization')->get()
        ]);
    }

    public function store(StoreProjectOrganizationRequest $request, Project $project)
    {
        $this->authorize('update', $project);

        $projectOrg = $this->service->addOrganization(
            $project,
            $request->organization_id,
            $request->role,
            $request->reference,
            $request->scope_description,
            $request->is_primary ?? false,
            $request->start_date,
            $request->end_date
        );

        return response()->json([
            'data' => $projectOrg->load('organization'),
            'message' => 'Organisation ajoutée au projet'
        ], 201);
    }

    public function update(UpdateProjectOrganizationRequest $request, Project $project, ProjectOrganization $projectOrganization)
    {
        $this->authorize('update', $project);

        // Update logic
        $projectOrganization->update($request->validated());

        return response()->json([
            'data' => $projectOrganization->load('organization'),
            'message' => 'Organisation mise à jour'
        ]);
    }

    public function destroy(Project $project, ProjectOrganization $projectOrganization)
    {
        $this->authorize('update', $project);

        $projectOrganization->delete();

        return response()->json([
            'message' => 'Organisation retirée du projet'
        ]);
    }
}
```

**Checklist** :
- [ ] Créer ProjectOrganizationController
- [ ] Créer UserRoleController
- [ ] Créer RoleController (admin)
- [ ] Créer PermissionController (admin)

### 5.2 Routes API
**Fichier** : `routes/api.php`

```php
use App\Http\Controllers\Api\ProjectOrganizationController;
use App\Http\Controllers\Api\UserRoleController;

Route::middleware(['auth:sanctum', 'tenant'])->group(function () {
    // Project Organizations
    Route::apiResource('projects.organizations', ProjectOrganizationController::class);

    // User Roles
    Route::apiResource('users.roles', UserRoleController::class);

    // Admin routes
    Route::prefix('admin')->middleware('permission:view_roles')->group(function () {
        Route::apiResource('roles', RoleController::class);
        Route::apiResource('permissions', PermissionController::class);
    });
});
```

**Checklist** :
- [ ] Ajouter routes ProjectOrganizations
- [ ] Ajouter routes UserRoles
- [ ] Ajouter routes admin (Roles, Permissions)
- [ ] Tester avec Postman/Insomnia

---

## Phase 6 : Interface Admin (PRIORITÉ BASSE)

**Durée estimée** : 8-12 heures
**Objectif** : Interface utilisateur pour gérer organisations et rôles

### 6.1 Composants Vue/React

**Composants à créer** :
- [ ] ProjectOrganizationsList.vue
- [ ] ProjectOrganizationForm.vue
- [ ] UserRolesList.vue
- [ ] UserRoleForm.vue
- [ ] RolePermissionsMatrix.vue

### 6.2 Pages admin

- [ ] Page gestion organisations d'un projet
- [ ] Page attribution rôles utilisateurs
- [ ] Page gestion rôles (admin)
- [ ] Page gestion permissions (admin)

---

## Phase 7 : Tests (PRIORITÉ HAUTE)

**Durée estimée** : 6-8 heures
**Objectif** : Garantir la qualité et la sécurité

### 7.1 Tests unitaires

**Fichiers à créer** :
```bash
tests/Unit/Models/UserTest.php
tests/Unit/Models/ProjectOrganizationTest.php
tests/Unit/Services/ProjectOrganizationServiceTest.php
tests/Unit/Scopes/TenantScopeTest.php
```

**Checklist** :
- [ ] Tests Model User (permissions, helpers)
- [ ] Tests Model ProjectOrganization
- [ ] Tests ProjectOrganizationService (règles métier)
- [ ] Tests TenantScope (RLS)

### 7.2 Tests Feature

**Fichiers à créer** :
```bash
tests/Feature/MultiTenant/TenantIsolationTest.php
tests/Feature/MultiTenant/ProjectOrganizationsTest.php
tests/Feature/MultiTenant/UserRolesTest.php
tests/Feature/Permissions/RbacTest.php
```

**Scénarios à tester** :
- [ ] Isolation Client : Un client ne voit que ses projets
- [ ] Isolation Partner : Un partner ne voit que ses projets assignés
- [ ] System Admin bypass : System admin voit tout
- [ ] Règles métier ProjectOrganizations (sponsor unique, etc.)
- [ ] Permissions par rôle
- [ ] Scopes des rôles (project, program, portfolio)

### 7.3 Tests d'intégration

- [ ] Test complet workflow : Créer projet → Ajouter orgs → Assigner utilisateurs → Vérifier accès
- [ ] Test multi-organisation : Projet avec sponsor, MOA, MOE, subcontractors
- [ ] Test approbations : Client Sponsor, MOA Manager

---

## Phase 8 : Documentation utilisateur (PRIORITÉ BASSE)

**Durée estimée** : 4-6 heures

### 8.1 Guides utilisateur

**Fichiers à créer** :
- [ ] `docs/GUIDE_GESTION_ORGANISATIONS.md`
- [ ] `docs/GUIDE_ATTRIBUTION_ROLES.md`
- [ ] `docs/GUIDE_PERMISSIONS.md`
- [ ] `docs/FAQ_MULTI_TENANT.md`

### 8.2 API Documentation

- [ ] Documentation OpenAPI/Swagger
- [ ] Exemples Postman collection
- [ ] Guide développeur intégration

---

## Checklist finale avant mise en production

### Sécurité
- [ ] Toutes les routes API ont l'authentification
- [ ] Middleware tenant appliqué partout
- [ ] Policies Laravel en place
- [ ] Tests de sécurité passés
- [ ] Pas de endpoints exposés sans autorisation

### Performance
- [ ] Index DB sur colonnes RLS (client_organization_id, organization_id)
- [ ] Eager loading des relations (avoid N+1)
- [ ] Cache si nécessaire

### Data
- [ ] Migrations exécutées en production
- [ ] Seeders données initiales (SAMSIC org, admin user)
- [ ] Backup DB avant déploiement

### Monitoring
- [ ] Logs pour actions sensibles (changement rôles, etc.)
- [ ] Monitoring erreurs permissions denied
- [ ] Alertes tentatives accès non autorisé

---

## Estimation totale

| Phase | Durée | Priorité |
|-------|-------|----------|
| Phase 1 : Fondations DB | 30 min | CRITIQUE |
| Phase 2 : Models | 3-4h | HAUTE |
| Phase 3 : RLS Application | 4-6h | HAUTE |
| Phase 4 : Services/Validation | 4-5h | MOYENNE |
| Phase 5 : Controllers/API | 4-6h | MOYENNE |
| Phase 6 : Interface Admin | 8-12h | BASSE |
| Phase 7 : Tests | 6-8h | HAUTE |
| Phase 8 : Documentation | 4-6h | BASSE |

**Total : 34-48 heures** (environ 5-6 jours de développement)

---

## Ordre d'exécution recommandé

### Sprint 1 (Jour 1) : Fondations
1. ✅ Phase 1.1 : Exécuter migrations
2. ✅ Phase 1.2 : Exécuter seeders
3. ✅ Phase 1.3 : Créer données de test
4. ✅ Phase 2.1 : Créer Models principaux

### Sprint 2 (Jour 2) : RLS et Relations
1. ✅ Phase 2.2 : Mettre à jour Models existants
2. ✅ Phase 3 : Implémenter RLS complet
3. ✅ Tests manuels dans tinker

### Sprint 3 (Jour 3) : Services et Validation
1. ✅ Phase 4.1 : ProjectOrganizationService
2. ✅ Phase 4.2 : Form Requests
3. ✅ Phase 4.3 : Policies
4. ✅ Tests unitaires services

### Sprint 4 (Jour 4) : API
1. ✅ Phase 5.1 : Controllers
2. ✅ Phase 5.2 : Routes
3. ✅ Tests API avec Postman

### Sprint 5 (Jour 5) : Tests et stabilisation
1. ✅ Phase 7 : Tests complets
2. ✅ Corrections bugs
3. ✅ Optimisations

### Sprint 6 (Jour 6+) : Interface et doc
1. ⏸️ Phase 6 : Interface admin (si nécessaire)
2. ⏸️ Phase 8 : Documentation utilisateur

---

**Prochaine action immédiate** : Exécuter Phase 1.1 (migrations) ⚡
