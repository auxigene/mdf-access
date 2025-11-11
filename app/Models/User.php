<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
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
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
}
