<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Project;

/**
 * ProjectPolicy
 *
 * Gère les autorisations pour les projets
 */
class ProjectPolicy extends BasePolicy
{
    /**
     * {@inheritdoc}
     */
    protected function getResourceSlug(): string
    {
        return 'projects';
    }

    /**
     * Déterminer si l'utilisateur peut voir la liste des projets
     */
    public function viewAny(User $user): bool
    {
        // Peut voir si a la permission OU si son organisation participe à des projets
        return parent::viewAny($user) || $user->getAccessibleProjects()->isNotEmpty();
    }

    /**
     * Déterminer si l'utilisateur peut voir un projet spécifique
     */
    public function view(User $user, Project $project): bool
    {
        // Peut voir si a la permission OU si son organisation participe au projet
        return parent::view($user, $project)
               || $user->organization?->participatesInProject($project->id) ?? false;
    }

    /**
     * Déterminer si l'utilisateur peut créer un projet
     */
    public function create(User $user): bool
    {
        // Seuls les utilisateurs avec la permission peuvent créer
        return parent::create($user);
    }

    /**
     * Déterminer si l'utilisateur peut mettre à jour un projet
     */
    public function update(User $user, Project $project): bool
    {
        // Peut modifier si a la permission sur ce projet
        return parent::update($user, $project);
    }

    /**
     * Déterminer si l'utilisateur peut supprimer un projet
     */
    public function delete(User $user, Project $project): bool
    {
        // Seuls les admins ou utilisateurs avec permission delete peuvent supprimer
        return parent::delete($user, $project);
    }
}
