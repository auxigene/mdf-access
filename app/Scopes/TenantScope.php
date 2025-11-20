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

        // System Admin : bypass complet (voit tout)
        if ($user->isSystemAdmin()) {
            return;
        }

        // Internal (SAMSIC) : bypass complet (voit tout)
        if ($user->isInternal()) {
            return;
        }

        // Client : filtre sur client_organization_id
        if ($user->isClient()) {
            $this->applyClientFilter($builder, $user);
            return;
        }

        // Partner : filtre sur participations projets
        if ($user->isPartner()) {
            $this->applyPartnerFilter($builder, $user);
            return;
        }

        // Par défaut : ne rien afficher (sécurité)
        $builder->whereRaw('1 = 0');
    }

    /**
     * Appliquer le filtre pour un utilisateur Client
     *
     * Filtre : client_organization_id = user.organization_id
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \App\Models\User $user
     * @return void
     */
    protected function applyClientFilter(Builder $builder, $user): void
    {
        $tableName = $builder->getModel()->getTable();

        // Vérifier si la table a la colonne client_organization_id
        if ($this->hasColumn($tableName, 'client_organization_id')) {
            $builder->where("{$tableName}.client_organization_id", $user->organization_id);
        } else {
            // Si pas de colonne, ne rien afficher (sécurité)
            $builder->whereRaw('1 = 0');
        }
    }

    /**
     * Appliquer le filtre pour un utilisateur Partner
     *
     * Filtre : Projets où l'organisation participe (via project_organizations)
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \App\Models\User $user
     * @return void
     */
    protected function applyPartnerFilter(Builder $builder, $user): void
    {
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
