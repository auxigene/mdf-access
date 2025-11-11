<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    // ===================================
    // CONFIGURATION
    // ===================================

    protected $fillable = [
        'name',
        'slug',
        'description',
        'scope',
        'organization_id',
    ];

    // ===================================
    // RELATIONS - Organisation
    // ===================================

    /**
     * Organisation à laquelle ce rôle est rattaché (si scope = organization)
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    // ===================================
    // RELATIONS - Permissions
    // ===================================

    /**
     * Permissions associées à ce rôle
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permission')
                    ->withTimestamps();
    }

    // ===================================
    // RELATIONS - Utilisateurs
    // ===================================

    /**
     * Attributions de ce rôle aux utilisateurs (avec scopes)
     */
    public function userRoles()
    {
        return $this->hasMany(UserRole::class);
    }

    /**
     * Utilisateurs ayant ce rôle
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles')
                    ->withPivot(['portfolio_id', 'program_id', 'project_id'])
                    ->withTimestamps();
    }

    // ===================================
    // SCOPES - Scope du Rôle
    // ===================================

    /**
     * Filtrer par scope
     */
    public function scopeOfScope($query, string $scope)
    {
        return $query->where('scope', $scope);
    }

    /**
     * Filtrer les rôles globaux
     */
    public function scopeGlobal($query)
    {
        return $query->where('scope', 'global');
    }

    /**
     * Filtrer les rôles au niveau organisation
     */
    public function scopeOrganization($query)
    {
        return $query->where('scope', 'organization');
    }

    /**
     * Filtrer les rôles au niveau projet
     */
    public function scopeProject($query)
    {
        return $query->where('scope', 'project');
    }

    // ===================================
    // SCOPES - Organisation
    // ===================================

    /**
     * Filtrer les rôles pour une organisation spécifique
     */
    public function scopeForOrganization($query, int $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    /**
     * Filtrer les rôles génériques (non liés à une organisation)
     */
    public function scopeGeneric($query)
    {
        return $query->whereNull('organization_id');
    }

    // ===================================
    // HELPERS - Scope du Rôle
    // ===================================

    /**
     * Vérifier si le rôle est global
     */
    public function isGlobal(): bool
    {
        return $this->scope === 'global';
    }

    /**
     * Vérifier si le rôle est au niveau organisation
     */
    public function isOrganizationScope(): bool
    {
        return $this->scope === 'organization';
    }

    /**
     * Vérifier si le rôle est au niveau projet
     */
    public function isProjectScope(): bool
    {
        return $this->scope === 'project';
    }

    /**
     * Vérifier si le rôle est lié à une organisation spécifique
     */
    public function isOrganizationSpecific(): bool
    {
        return $this->organization_id !== null;
    }

    /**
     * Vérifier si le rôle est générique (non lié à une organisation)
     */
    public function isGeneric(): bool
    {
        return $this->organization_id === null;
    }

    // ===================================
    // HELPERS - Permissions
    // ===================================

    /**
     * Vérifier si le rôle possède une permission spécifique
     */
    public function hasPermission(string $permissionSlug): bool
    {
        return $this->permissions()->where('slug', $permissionSlug)->exists();
    }

    /**
     * Ajouter une permission au rôle
     */
    public function givePermission(Permission $permission): void
    {
        if (!$this->hasPermission($permission->slug)) {
            $this->permissions()->attach($permission->id);
        }
    }

    /**
     * Ajouter plusieurs permissions au rôle
     */
    public function givePermissions(array $permissions): void
    {
        foreach ($permissions as $permission) {
            if ($permission instanceof Permission) {
                $this->givePermission($permission);
            } elseif (is_string($permission)) {
                $permissionModel = Permission::findBySlug($permission);
                if ($permissionModel) {
                    $this->givePermission($permissionModel);
                }
            }
        }
    }

    /**
     * Retirer une permission du rôle
     */
    public function revokePermission(Permission $permission): void
    {
        $this->permissions()->detach($permission->id);
    }

    /**
     * Retirer plusieurs permissions du rôle
     */
    public function revokePermissions(array $permissions): void
    {
        $permissionIds = collect($permissions)->map(function ($permission) {
            if ($permission instanceof Permission) {
                return $permission->id;
            } elseif (is_string($permission)) {
                return Permission::findBySlug($permission)?->id;
            }
            return null;
        })->filter()->toArray();

        if (!empty($permissionIds)) {
            $this->permissions()->detach($permissionIds);
        }
    }

    /**
     * Synchroniser les permissions du rôle
     */
    public function syncPermissions(array $permissions): void
    {
        $permissionIds = collect($permissions)->map(function ($permission) {
            if ($permission instanceof Permission) {
                return $permission->id;
            } elseif (is_string($permission)) {
                return Permission::findBySlug($permission)?->id;
            } elseif (is_int($permission)) {
                return $permission;
            }
            return null;
        })->filter()->toArray();

        $this->permissions()->sync($permissionIds);
    }

    /**
     * Obtenir les permissions par resource
     */
    public function getPermissionsForResource(string $resource)
    {
        return $this->permissions()->where('resource', $resource)->get();
    }

    /**
     * Obtenir les permissions par action
     */
    public function getPermissionsForAction(string $action)
    {
        return $this->permissions()->where('action', $action)->get();
    }

    /**
     * Obtenir les permissions groupées par resource
     */
    public function getPermissionsGroupedByResource()
    {
        return $this->permissions()->get()->groupBy('resource');
    }

    /**
     * Obtenir les permissions groupées par action
     */
    public function getPermissionsGroupedByAction()
    {
        return $this->permissions()->get()->groupBy('action');
    }

    // ===================================
    // HELPERS - Recherche
    // ===================================

    /**
     * Trouver un rôle par son slug
     */
    public static function findBySlug(string $slug): ?Role
    {
        return static::where('slug', $slug)->first();
    }

    /**
     * Trouver ou créer un rôle par slug
     */
    public static function findOrCreateBySlug(
        string $slug,
        ?string $name = null,
        ?string $description = null,
        string $scope = 'project'
    ): Role {
        return static::firstOrCreate(
            ['slug' => $slug],
            [
                'name' => $name ?? ucfirst(str_replace('_', ' ', $slug)),
                'description' => $description,
                'scope' => $scope,
            ]
        );
    }

    // ===================================
    // HELPERS - Statistiques
    // ===================================

    /**
     * Compter le nombre d'utilisateurs ayant ce rôle
     */
    public function getUsersCount(): int
    {
        return $this->userRoles()->count();
    }

    /**
     * Compter le nombre de permissions de ce rôle
     */
    public function getPermissionsCount(): int
    {
        return $this->permissions()->count();
    }

    /**
     * Vérifier si le rôle a des utilisateurs assignés
     */
    public function hasUsers(): bool
    {
        return $this->userRoles()->exists();
    }

    /**
     * Vérifier si le rôle a des permissions assignées
     */
    public function hasPermissions(): bool
    {
        return $this->permissions()->exists();
    }

    // ===================================
    // HELPERS - Description
    // ===================================

    /**
     * Obtenir le label du scope en français
     */
    public function getScopeLabel(): string
    {
        $labels = [
            'global' => 'Global',
            'organization' => 'Organisation',
            'project' => 'Projet',
        ];

        return $labels[$this->scope] ?? ucfirst($this->scope);
    }

    /**
     * Obtenir la description complète du rôle
     */
    public function getFullDescription(): string
    {
        $desc = $this->name . ' (' . $this->getScopeLabel() . ')';

        if ($this->isOrganizationSpecific() && $this->organization) {
            $desc .= ' - ' . $this->organization->name;
        }

        return $desc;
    }
}
