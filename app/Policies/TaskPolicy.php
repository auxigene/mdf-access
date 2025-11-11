<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Task;

/**
 * TaskPolicy
 *
 * Gère les autorisations pour les tâches
 */
class TaskPolicy extends BasePolicy
{
    /**
     * {@inheritdoc}
     */
    protected function getResourceSlug(): string
    {
        return 'tasks';
    }

    /**
     * Déterminer si l'utilisateur peut voir un tâche spécifique
     */
    public function view(User $user, Task $task): bool
    {
        // Vérifier la permission ET l'accès au projet parent
        if (!parent::view($user, $task)) {
            return false;
        }

        // Vérifier que l'utilisateur a accès au projet de la tâche
        $project = $task->project;
        return $project && ($user->organization?->participatesInProject($project->id) ?? false);
    }

    /**
     * Déterminer si l'utilisateur peut mettre à jour une tâche
     */
    public function update(User $user, Task $task): bool
    {
        // Vérifier la permission ET l'accès au projet parent
        if (!parent::update($user, $task)) {
            return false;
        }

        // Vérifier l'accès au projet
        $project = $task->project;
        return $project && ($user->organization?->participatesInProject($project->id) ?? false);
    }

    /**
     * Déterminer si l'utilisateur peut supprimer une tâche
     */
    public function delete(User $user, Task $task): bool
    {
        // Vérifier la permission ET l'accès au projet parent
        if (!parent::delete($user, $task)) {
            return false;
        }

        // Vérifier l'accès au projet
        $project = $task->project;
        return $project && ($user->organization?->participatesInProject($project->id) ?? false);
    }
}
