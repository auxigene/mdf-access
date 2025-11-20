<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class TenantScope implements Scope
{
    /**
     * Appliquer le scope à une query Eloquent
     *
     * Architecture Multi-Tenant Pure :
     * - System Admin (is_system_admin) : bypass complet
     * - Toutes les autres organisations : filtrées sur leurs participations
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    public function apply(Builder $builder, Model $model): void
    {
        $user = Auth::user();

        // Pas d'utilisateur connecté = pas de filtre
        if (!$user) {
            return;
        }

        // System Admin : SEUL cas de bypass
        // Si SAMSIC ou toute autre organisation veut tout voir,
        // les users doivent être System Admin OU participer à tous les projets
        if ($user->isSystemAdmin()) {
            return;
        }

        // TOUTES les organisations (y compris SAMSIC) : filtrées sur participations
        // C'est l'essence du multi-tenant : chaque organisation ne voit que ce qui la concerne
        $this->applyParticipationFilter($builder, $user);
    }

    /**
     * Appliquer le filtre basé sur les participations (multi-tenant pur)
     *
     * Filtre : Projets où l'organisation participe (via project_organizations)
     *
     * Architecture Multi-Tenant Pure :
     * - Chaque organisation ne voit QUE les projets où elle participe activement
     * - Aucune exception, aucun bypass (sauf System Admin)
     * - SAMSIC, clients, partenaires : tous traités de la même manière
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \App\Models\User $user
     * @return void
     */
    protected function applyParticipationFilter(Builder $builder, $user): void
    {
        // Si pas d'organisation, ne rien afficher
        if (!$user->organization_id) {
            $builder->whereRaw('1 = 0');
            return;
        }

        $tableName = $builder->getModel()->getTable();

        // Pour la table projects : filtre via project_organizations
        if ($tableName === 'projects') {
            $builder->whereExists(function ($query) use ($user) {
                $query->select(\DB::raw(1))
                      ->from('project_organizations')
                      ->whereColumn('project_organizations.project_id', 'projects.id')
                      ->where('project_organizations.organization_id', $user->organization_id)
                      ->where('project_organizations.status', 'active');
            });
        }
        // Pour les autres tables liées aux projets (tasks, deliverables, etc.)
        elseif ($this->hasColumn($tableName, 'project_id')) {
            $builder->whereHas('project', function ($query) use ($user) {
                $query->whereExists(function ($subQuery) use ($user) {
                    $subQuery->select(\DB::raw(1))
                             ->from('project_organizations')
                             ->whereColumn('project_organizations.project_id', 'projects.id')
                             ->where('project_organizations.organization_id', $user->organization_id)
                             ->where('project_organizations.status', 'active');
                });
            });
        }
        else {
            // Si pas de relation projet, ne rien afficher (sécurité)
            $builder->whereRaw('1 = 0');
        }
    }

    /**
     * Vérifier si une table a une colonne spécifique
     *
     * @param string $table
     * @param string $column
     * @return bool
     */
    protected function hasColumn(string $table, string $column): bool
    {
        return \Schema::hasColumn($table, $column);
    }

    /**
     * Étendre la query pour exclure le scope
     * (pour les méthodes like withoutGlobalScope)
     */
    public function extend(Builder $builder): void
    {
        // Permet d'exclure le scope avec Model::withoutGlobalScope(TenantScope::class)
    }
}
