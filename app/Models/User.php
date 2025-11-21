<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    // ===================================
    // CONFIGURATION
    // ===================================

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'organization_id',
        'is_system_admin',
        'two_factor_enabled',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_system_admin' => 'boolean',
            'two_factor_enabled' => 'boolean',
            'cached_permissions' => 'array',
            'permissions_cached_at' => 'datetime',
        ];
    }

    // ===================================
    // RELATIONS - Organisation
    // ===================================

    /**
     * Organisation à laquelle appartient l'utilisateur
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    // ===================================
    // RELATIONS - Rôles et Permissions
    // ===================================

    /**
     * Attributions de rôles de l'utilisateur (avec scopes)
     * Table user_roles contient: user_id, role_id, portfolio_id, program_id, project_id
     */
    public function userRoles()
    {
        return $this->hasMany(UserRole::class);
    }

    /**
     * Rôles de l'utilisateur (via la table pivot user_roles)
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles')
                    ->withPivot(['portfolio_id', 'program_id', 'project_id'])
                    ->withTimestamps();
    }

    // ===================================
    // RELATIONS - API Keys
    // ===================================

    /**
     * Clés API de l'utilisateur
     */
    public function apiKeys()
    {
        return $this->hasMany(ApiKey::class);
    }

    // ===================================
    // HELPERS - Type d'Utilisateur
    // ===================================

    /**
     * Vérifier si l'utilisateur est un administrateur système
     */
    public function isSystemAdmin(): bool
    {
        return $this->is_system_admin === true;
    }

    /**
     * Vérifier si l'organisation de l'utilisateur est cliente pour un projet donné
     *
     * @param int $projectId ID du projet
     * @return bool True si l'organisation de l'utilisateur est sponsor du projet
     */
    public function isClientForProject(int $projectId): bool
    {
        return $this->organization?->isClientForProject($projectId) ?? false;
    }

    /**
     * Vérifier si l'organisation de l'utilisateur est MOE pour un projet donné
     *
     * @param int $projectId ID du projet
     * @return bool True si l'organisation de l'utilisateur est MOE du projet
     */
    public function isMoeForProject(int $projectId): bool
    {
        return $this->organization?->isMoeForProject($projectId) ?? false;
    }

    /**
     * Vérifier si l'organisation de l'utilisateur est MOA pour un projet donné
     *
     * @param int $projectId ID du projet
     * @return bool True si l'organisation de l'utilisateur est MOA du projet
     */
    public function isMoaForProject(int $projectId): bool
    {
        return $this->organization?->isMoaForProject($projectId) ?? false;
    }

    /**
     * Obtenir le rôle de l'organisation de l'utilisateur pour un projet donné
     *
     * @param int $projectId ID du projet
     * @return string|null Le rôle ou null
     */
    public function getRoleForProject(int $projectId): ?string
    {
        return $this->organization?->getRoleForProject($projectId);
    }

    /**
     * Récupérer tous les projets accessibles à l'utilisateur
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAccessibleProjects()
    {
        // System admin voit tous les projets
        if ($this->isSystemAdmin()) {
            return Project::all();
        }

        // Utilisateur voit tous les projets où son organisation participe
        return $this->organization?->allProjects() ?? collect();
    }

    /**
     * Récupérer tous les projets où l'organisation de l'utilisateur est cliente
     *
     * @return \Illuminate\Support\Collection
     */
    public function getProjectsWhereClient()
    {
        return $this->organization?->getProjectsWhereClient() ?? collect();
    }

    /**
     * Récupérer tous les projets où l'organisation de l'utilisateur est MOE
     *
     * @return \Illuminate\Support\Collection
     */
    public function getProjectsWhereMoe()
    {
        return $this->organization?->getProjectsWhereMoe() ?? collect();
    }

    /**
     * Récupérer tous les projets où l'organisation de l'utilisateur est MOA
     *
     * @return \Illuminate\Support\Collection
     */
    public function getProjectsWhereMoa()
    {
        return $this->organization?->getProjectsWhereMoa() ?? collect();
    }

    // ===================================
    // HELPERS - Permissions (RBAC)
    // ===================================

    /**
     * Vérifier si l'utilisateur a une permission spécifique
     *
     * @param string $permissionSlug Slug de la permission (ex: 'view_projects')
     * @param Model|null $scope Scope optionnel (Portfolio, Program, ou Project)
     * @return bool
     */
    public function hasPermission(string $permissionSlug, ?Model $scope = null): bool
    {
        // System admin bypass : accès total
        if ($this->is_system_admin) {
            return true;
        }

        // Récupérer les rôles de l'utilisateur
        $userRoles = $this->userRoles;

        // Si un scope est fourni, filtrer les rôles pertinents
        if ($scope !== null) {
            $userRoles = $userRoles->filter(function ($userRole) use ($scope) {
                // Rôle sans scope (global) = toujours valide
                if ($userRole->portfolio_id === null
                    && $userRole->program_id === null
                    && $userRole->project_id === null) {
                    return true;
                }

                // Vérifier le scope correspondant
                if ($scope instanceof Project) {
                    return $userRole->project_id === $scope->id;
                } elseif ($scope instanceof Program) {
                    return $userRole->program_id === $scope->id;
                } elseif ($scope instanceof Portfolio) {
                    return $userRole->portfolio_id === $scope->id;
                }

                return false;
            });
        }

        // Vérifier si un des rôles possède la permission
        foreach ($userRoles as $userRole) {
            $role = $userRole->role;
            if ($role && $role->hasPermission($permissionSlug)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Vérifier si l'utilisateur a un rôle spécifique
     *
     * @param string $roleSlug Slug du rôle (ex: 'project_manager')
     * @return bool
     */
    public function hasRole(string $roleSlug): bool
    {
        return $this->roles()->where('slug', $roleSlug)->exists();
    }

    /**
     * Récupérer toutes les permissions de l'utilisateur (dédupliquées)
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllPermissions()
    {
        // System admin a toutes les permissions
        if ($this->is_system_admin) {
            return Permission::all();
        }

        // Récupérer toutes les permissions des rôles de l'utilisateur
        $permissions = collect();

        foreach ($this->userRoles as $userRole) {
            $role = $userRole->role;
            if ($role) {
                $permissions = $permissions->merge($role->permissions);
            }
        }

        // Dédupliquer par ID
        return $permissions->unique('id');
    }

    /**
     * Override Laravel can() pour intégration avec Gates
     */
    public function can($ability, $arguments = [])
    {
        // Essayer d'abord avec notre système de permissions
        $scope = is_array($arguments) && count($arguments) > 0 ? $arguments[0] : null;

        if ($this->hasPermission($ability, $scope)) {
            return true;
        }

        // Fallback sur le système Laravel natif
        return parent::can($ability, $arguments);
    }

    // ===================================
    // HELPERS - Rôles par Scope
    // ===================================

    /**
     * Récupérer les rôles globaux de l'utilisateur (sans scope)
     */
    public function globalRoles()
    {
        return $this->userRoles()
                    ->whereNull('portfolio_id')
                    ->whereNull('program_id')
                    ->whereNull('project_id')
                    ->with('role')
                    ->get()
                    ->pluck('role');
    }

    /**
     * Récupérer les rôles de l'utilisateur pour un projet spécifique
     */
    public function rolesForProject(int $projectId)
    {
        return $this->userRoles()
                    ->where(function ($query) use ($projectId) {
                        $query->where('project_id', $projectId)
                              ->orWhereNull('project_id');
                    })
                    ->with('role')
                    ->get()
                    ->pluck('role');
    }

    /**
     * Récupérer les rôles de l'utilisateur pour un programme spécifique
     */
    public function rolesForProgram(int $programId)
    {
        return $this->userRoles()
                    ->where(function ($query) use ($programId) {
                        $query->where('program_id', $programId)
                              ->orWhereNull('program_id');
                    })
                    ->with('role')
                    ->get()
                    ->pluck('role');
    }

    /**
     * Récupérer les rôles de l'utilisateur pour un portfolio spécifique
     */
    public function rolesForPortfolio(int $portfolioId)
    {
        return $this->userRoles()
                    ->where(function ($query) use ($portfolioId) {
                        $query->where('portfolio_id', $portfolioId)
                              ->orWhereNull('portfolio_id');
                    })
                    ->with('role')
                    ->get()
                    ->pluck('role');
    }

    // ===================================
    // PROJECT TEAM METHODS
    // ===================================

    /**
     * Get user's project team memberships
     */
    public function projectTeams()
    {
        return $this->hasMany(ProjectTeam::class);
    }

    /**
     * Get user's active project team memberships
     */
    public function activeProjectTeams()
    {
        return $this->projectTeams()->currentlyActive();
    }

    /**
     * Check if user is a member of a project team
     */
    public function isProjectTeamMember(Project $project): bool
    {
        return $this->projectTeams()
            ->forProject($project)
            ->currentlyActive()
            ->exists();
    }

    /**
     * Get user's role in a project team (returns the highest privilege role if multiple)
     */
    public function getProjectTeamRole(Project $project): ?Role
    {
        $teamMember = $this->projectTeams()
            ->forProject($project)
            ->currentlyActive()
            ->with('role')
            ->first();

        return $teamMember?->role;
    }

    /**
     * Get all projects user is a team member of
     */
    public function getTeamProjects()
    {
        return ProjectTeam::getUserProjects($this);
    }

    // ===================================
    // PERMISSION CACHING
    // ===================================

    /**
     * Cache user permissions for performance
     */
    public function cachePermissions(): void
    {
        $permissions = [
            'global' => [],
            'computed_at' => now()->toIso8601String(),
            'roles' => [
                'global' => [],
                'organization' => [],
                'projects' => [],
            ],
        ];

        // Get all user roles (global, organization, project-scoped)
        $userRoles = $this->roles()->with('permissions')->get();

        foreach ($userRoles as $userRole) {
            $rolePermissions = $userRole->role->permissions->pluck('slug')->toArray();

            // Determine scope
            $pivot = $userRole->pivot;
            if ($pivot->project_id) {
                $key = "project_{$pivot->project_id}";
                $permissions[$key] = array_merge($permissions[$key] ?? [], $rolePermissions);
                $permissions['roles']['projects'][$pivot->project_id] = $userRole->role->slug;
            } elseif ($pivot->program_id) {
                $key = "program_{$pivot->program_id}";
                $permissions[$key] = array_merge($permissions[$key] ?? [], $rolePermissions);
            } elseif ($pivot->portfolio_id) {
                $key = "portfolio_{$pivot->portfolio_id}";
                $permissions[$key] = array_merge($permissions[$key] ?? [], $rolePermissions);
            } elseif ($this->organization_id) {
                $key = "organization_{$this->organization_id}";
                $permissions[$key] = array_merge($permissions[$key] ?? [], $rolePermissions);
                $permissions['roles']['organization'][] = $userRole->role->slug;
            } else {
                $permissions['global'] = array_merge($permissions['global'], $rolePermissions);
                $permissions['roles']['global'][] = $userRole->role->slug;
            }
        }

        // Deduplicate permissions
        foreach ($permissions as $key => $value) {
            if (is_array($value) && $key !== 'roles') {
                $permissions[$key] = array_unique($value);
            }
        }

        $this->update([
            'cached_permissions' => $permissions,
            'permissions_cached_at' => now(),
        ]);
    }

    /**
     * Clear user's permission cache
     */
    public function clearPermissionsCache(): void
    {
        $this->update([
            'cached_permissions' => null,
            'permissions_cached_at' => null,
        ]);
    }

    /**
     * Get cached permissions (returns null if stale or missing)
     */
    public function getCachedPermissions(string $context = 'global'): ?array
    {
        // Check if cache exists and is fresh (15 minutes TTL)
        if (!$this->cached_permissions || !$this->permissions_cached_at) {
            return null;
        }

        if ($this->permissions_cached_at->lt(now()->subMinutes(15))) {
            return null; // Cache is stale
        }

        return $this->cached_permissions[$context] ?? null;
    }

    /**
     * Check if user has permission in context (with caching)
     */
    public function hasPermissionInContext(
        string $permissionSlug,
        ?Model $context = null,
        bool $checkHierarchy = true
    ): bool {
        // System admin bypass
        if ($this->is_system_admin) {
            return true;
        }

        // Try cache first
        if ($context instanceof Project) {
            $cached = $this->getCachedPermissions("project_{$context->id}");
            if ($cached !== null) {
                return in_array($permissionSlug, $cached);
            }
        }

        // Fallback to existing hasPermission method
        return $this->hasPermission($permissionSlug, $context);
    }
}
