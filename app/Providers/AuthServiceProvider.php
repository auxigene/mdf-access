<?php

namespace App\Providers;

use App\Models\Permission;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

/**
 * AuthServiceProvider
 *
 * Enregistre les Gates Laravel pour le système de permissions
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\Project::class => \App\Policies\ProjectPolicy::class,
        \App\Models\Task::class => \App\Policies\TaskPolicy::class,
        \App\Models\Budget::class => \App\Policies\BudgetPolicy::class,
        // Ajoutez d'autres policies ici au besoin
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Enregistrer dynamiquement un Gate pour chaque permission
        $this->registerPermissionGates();

        // Enregistrer les policies
        $this->registerPolicies();

        // Gate pour vérifier si un utilisateur est system admin
        Gate::define('system-admin', function ($user) {
            return $user->isSystemAdmin();
        });

        // Gate super-admin : bypass toutes les vérifications
        Gate::before(function ($user, $ability) {
            if ($user->isSystemAdmin()) {
                return true; // System admin peut tout faire
            }
        });
    }

    /**
     * Enregistrer dynamiquement un Gate pour chaque permission
     */
    protected function registerPermissionGates(): void
    {
        try {
            // Récupérer toutes les permissions actives
            $permissions = Permission::with(['aclResource', 'action'])
                                     ->where('is_active', true)
                                     ->get();

            foreach ($permissions as $permission) {
                Gate::define($permission->slug, function ($user, $scope = null) use ($permission) {
                    return $user->hasPermission($permission->slug, $scope);
                });
            }
        } catch (\Exception $e) {
            // En cas d'erreur (ex: migrations non exécutées), ne pas bloquer l'application
            // Les Gates seront disponibles après les migrations
        }
    }
}
