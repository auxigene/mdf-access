<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * BasePolicy
 *
 * Policy de base réutilisable pour toutes les ressources
 * Utilise le système de permissions flexibles
 */
abstract class BasePolicy
{
    /**
     * Obtenir le nom de la ressource (à override dans les classes enfants)
     *
     * @return string
     */
    abstract protected function getResourceSlug(): string;

    /**
     * Déterminer si l'utilisateur peut voir la liste
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission($this->getPermissionSlug('view'));
    }

    /**
     * Déterminer si l'utilisateur peut voir un élément spécifique
     */
    public function view(User $user, Model $model): bool
    {
        return $user->hasPermission($this->getPermissionSlug('view'), $model);
    }

    /**
     * Déterminer si l'utilisateur peut créer un élément
     */
    public function create(User $user): bool
    {
        return $user->hasPermission($this->getPermissionSlug('create'));
    }

    /**
     * Déterminer si l'utilisateur peut mettre à jour un élément
     */
    public function update(User $user, Model $model): bool
    {
        return $user->hasPermission($this->getPermissionSlug('edit'), $model);
    }

    /**
     * Déterminer si l'utilisateur peut supprimer un élément
     */
    public function delete(User $user, Model $model): bool
    {
        return $user->hasPermission($this->getPermissionSlug('delete'), $model);
    }

    /**
     * Déterminer si l'utilisateur peut restaurer un élément (soft delete)
     */
    public function restore(User $user, Model $model): bool
    {
        return $user->hasPermission($this->getPermissionSlug('edit'), $model);
    }

    /**
     * Déterminer si l'utilisateur peut forcer la suppression (soft delete)
     */
    public function forceDelete(User $user, Model $model): bool
    {
        return $user->hasPermission($this->getPermissionSlug('delete'), $model);
    }

    /**
     * Déterminer si l'utilisateur peut approuver
     */
    public function approve(User $user, Model $model): bool
    {
        return $user->hasPermission($this->getPermissionSlug('approve'), $model);
    }

    /**
     * Déterminer si l'utilisateur peut exporter
     */
    public function export(User $user): bool
    {
        return $user->hasPermission($this->getPermissionSlug('export'));
    }

    /**
     * Construire le slug de permission
     *
     * @param string $action L'action (view, create, edit, delete, etc.)
     * @return string Le slug de permission (ex: "view_projects")
     */
    protected function getPermissionSlug(string $action): string
    {
        return $action . '_' . $this->getResourceSlug();
    }
}
