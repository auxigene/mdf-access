<?php

namespace App\Traits;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;

trait TenantScoped
{
    /**
     * Boot le trait TenantScoped
     *
     * Ajoute automatiquement le TenantScope global au model
     */
    protected static function bootTenantScoped(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    /**
     * Obtenir une nouvelle query sans le scope tenant
     * Utile pour les admins ou opérations spéciales
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function withoutTenantScope()
    {
        return static::withoutGlobalScope(TenantScope::class);
    }

    /**
     * Vérifier si le model doit être scopé pour l'utilisateur actuel
     *
     * @return bool
     */
    public function shouldApplyTenantScope(): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false; // Pas d'utilisateur connecté = pas de scope
        }

        // System Admin : bypass
        if ($user->isSystemAdmin()) {
            return false;
        }

        // Internal (SAMSIC) : bypass
        if ($user->isInternal()) {
            return false;
        }

        // Client et Partner : appliquer le scope
        return true;
    }
}
