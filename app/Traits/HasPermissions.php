<?php

namespace App\Traits;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Collection;

/**
 * Trait HasPermissions
 *
 * Ajoute des méthodes de vérification de permissions aux utilisateurs
 */
trait HasPermissions
{
    /**
     * Vérifier si l'utilisateur a une permission spécifique
     *
     * @param  string  $permissionSlug  Le slug de la permission (ex: "projects_view")
     * @return bool
     */
    public function hasPermission(string $permissionSlug): bool
    {
        return $this->getPermissions()->contains('slug', $permissionSlug);
    }

    /**
     * Vérifier si l'utilisateur a au moins une des permissions
     *
     * @param  array|string  $permissions  Tableau de slugs ou slug unique
     * @return bool
     */
    public function hasAnyPermission(array|string $permissions): bool
    {
        $permissions = is_array($permissions) ? $permissions : [$permissions];
        $userPermissions = $this->getPermissions()->pluck('slug');

        foreach ($permissions as $permission) {
            if ($userPermissions->contains($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Vérifier si l'utilisateur a toutes les permissions
     *
     * @param  array  $permissions  Tableau de slugs
     * @return bool
     */
    public function hasAllPermissions(array $permissions): bool
    {
        $userPermissions = $this->getPermissions()->pluck('slug');

        foreach ($permissions as $permission) {
            if (!$userPermissions->contains($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Vérifier si l'utilisateur a une permission pour une ressource et une action
     *
     * @param  string  $resourceSlug  Le slug de la ressource (ex: "projects")
     * @param  string  $actionSlug  Le slug de l'action (ex: "view")
     * @return bool
     */
    public function canPerform(string $resourceSlug, string $actionSlug): bool
    {
        $permissionSlug = $actionSlug . '_' . $resourceSlug;
        return $this->hasPermission($permissionSlug);
    }

    /**
     * Obtenir toutes les permissions de l'utilisateur (via ses rôles)
     *
     * @return \Illuminate\Support\Collection
     */
    public function getPermissions(): Collection
    {
        // Cache les permissions pour éviter de requêter plusieurs fois
        if (!isset($this->cachedPermissions)) {
            $this->cachedPermissions = $this->roles()
                ->with('permissions')
                ->get()
                ->pluck('permissions')
                ->flatten()
                ->unique('id');
        }

        return $this->cachedPermissions;
    }

    /**
     * Vérifier si l'utilisateur a un rôle spécifique
     *
     * @param  string  $roleSlug  Le slug du rôle (ex: "admin")
     * @return bool
     */
    public function hasRole(string $roleSlug): bool
    {
        return $this->roles()->where('slug', $roleSlug)->exists();
    }

    /**
     * Vérifier si l'utilisateur a au moins un des rôles
     *
     * @param  array  $roleSlugs  Tableau de slugs de rôles
     * @return bool
     */
    public function hasAnyRole(array $roleSlugs): bool
    {
        return $this->roles()->whereIn('slug', $roleSlugs)->exists();
    }

    /**
     * Vérifier si l'utilisateur a tous les rôles
     *
     * @param  array  $roleSlugs  Tableau de slugs de rôles
     * @return bool
     */
    public function hasAllRoles(array $roleSlugs): bool
    {
        $userRoles = $this->roles()->pluck('slug');

        foreach ($roleSlugs as $roleSlug) {
            if (!$userRoles->contains($roleSlug)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Vérifier si l'utilisateur est admin
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Obtenir les slugs de toutes les permissions
     *
     * @return array
     */
    public function getPermissionSlugs(): array
    {
        return $this->getPermissions()->pluck('slug')->toArray();
    }

    /**
     * Relation avec les rôles (doit être définie dans le model User)
     */
    abstract public function roles();
}
