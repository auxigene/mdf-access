<?php

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

if (!function_exists('user_can')) {
    /**
     * Vérifier si l'utilisateur connecté a une permission
     *
     * @param string $permissionSlug Le slug de la permission (ex: "view_projects")
     * @param Model|null $scope Le scope optionnel (Portfolio, Program, Project)
     * @return bool
     */
    function user_can(string $permissionSlug, ?Model $scope = null): bool
    {
        $user = auth()->user();

        if (!$user || !($user instanceof User)) {
            return false;
        }

        return $user->hasPermission($permissionSlug, $scope);
    }
}

if (!function_exists('user_can_any')) {
    /**
     * Vérifier si l'utilisateur a au moins une des permissions
     *
     * @param array $permissions Tableau de slugs de permissions
     * @param Model|null $scope Le scope optionnel
     * @return bool
     */
    function user_can_any(array $permissions, ?Model $scope = null): bool
    {
        $user = auth()->user();

        if (!$user || !($user instanceof User)) {
            return false;
        }

        foreach ($permissions as $permission) {
            if ($user->hasPermission($permission, $scope)) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('user_can_all')) {
    /**
     * Vérifier si l'utilisateur a toutes les permissions
     *
     * @param array $permissions Tableau de slugs de permissions
     * @param Model|null $scope Le scope optionnel
     * @return bool
     */
    function user_can_all(array $permissions, ?Model $scope = null): bool
    {
        $user = auth()->user();

        if (!$user || !($user instanceof User)) {
            return false;
        }

        foreach ($permissions as $permission) {
            if (!$user->hasPermission($permission, $scope)) {
                return false;
            }
        }

        return true;
    }
}

if (!function_exists('user_has_role')) {
    /**
     * Vérifier si l'utilisateur a un rôle spécifique
     *
     * @param string $roleSlug Le slug du rôle (ex: "admin")
     * @return bool
     */
    function user_has_role(string $roleSlug): bool
    {
        $user = auth()->user();

        if (!$user || !($user instanceof User)) {
            return false;
        }

        return $user->hasRole($roleSlug);
    }
}

if (!function_exists('user_is_admin')) {
    /**
     * Vérifier si l'utilisateur est admin système
     *
     * @return bool
     */
    function user_is_admin(): bool
    {
        $user = auth()->user();

        if (!$user || !($user instanceof User)) {
            return false;
        }

        return $user->isSystemAdmin();
    }
}

if (!function_exists('abort_unless_can')) {
    /**
     * Lancer une exception 403 si l'utilisateur n'a pas la permission
     *
     * @param string $permissionSlug Le slug de la permission
     * @param Model|null $scope Le scope optionnel
     * @param string|null $message Message d'erreur personnalisé
     * @return void
     */
    function abort_unless_can(string $permissionSlug, ?Model $scope = null, ?string $message = null): void
    {
        if (!user_can($permissionSlug, $scope)) {
            abort(403, $message ?? "Vous n'avez pas la permission requise.");
        }
    }
}

if (!function_exists('permission_slug')) {
    /**
     * Construire un slug de permission à partir d'une action et d'une ressource
     *
     * @param string $action L'action (view, create, edit, delete, etc.)
     * @param string $resource La ressource (projects, tasks, budgets, etc.)
     * @return string Le slug complet (ex: "view_projects")
     */
    function permission_slug(string $action, string $resource): string
    {
        return $action . '_' . $resource;
    }
}
