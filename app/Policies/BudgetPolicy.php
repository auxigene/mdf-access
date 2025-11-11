<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Budget;

/**
 * BudgetPolicy
 *
 * Gère les autorisations pour les budgets
 */
class BudgetPolicy extends BasePolicy
{
    /**
     * {@inheritdoc}
     */
    protected function getResourceSlug(): string
    {
        return 'budgets';
    }

    /**
     * Déterminer si l'utilisateur peut voir un budget spécifique
     */
    public function view(User $user, Budget $budget): bool
    {
        // Vérifier la permission ET l'accès au projet parent
        if (!parent::view($user, $budget)) {
            return false;
        }

        // Vérifier que l'utilisateur a accès au projet du budget
        $project = $budget->project;
        return $project && ($user->organization?->participatesInProject($project->id) ?? false);
    }

    /**
     * Déterminer si l'utilisateur peut mettre à jour un budget
     */
    public function update(User $user, Budget $budget): bool
    {
        // Vérifier la permission ET l'accès au projet parent
        if (!parent::update($user, $budget)) {
            return false;
        }

        // Vérifier l'accès au projet
        $project = $budget->project;
        return $project && ($user->organization?->participatesInProject($project->id) ?? false);
    }

    /**
     * Déterminer si l'utilisateur peut approuver un budget
     */
    public function approve(User $user, Budget $budget): bool
    {
        // Vérifier la permission d'approbation
        if (!parent::approve($user, $budget)) {
            return false;
        }

        // Seules les organisations MOA/Client peuvent approuver
        $project = $budget->project;
        if (!$project) {
            return false;
        }

        return $user->isMoaForProject($project->id) || $user->isClientForProject($project->id);
    }
}
