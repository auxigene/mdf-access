<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantAccess
{
    /**
     * Vérifier l'accès tenant pour la requête
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Pas d'utilisateur connecté : autoriser (laissé à auth middleware)
        if (!$user) {
            return $next($request);
        }

        // System Admin : bypass
        if ($user->isSystemAdmin()) {
            return $next($request);
        }

        // Internal (SAMSIC) : bypass
        if ($user->isInternal()) {
            return $next($request);
        }

        // Pour Client et Partner : vérifier que organization_id est set
        if (!$user->organization_id) {
            abort(403, 'Utilisateur sans organisation assignée');
        }

        // Vérifier que l'organisation existe et est active
        if (!$user->organization || !$user->organization->isActive()) {
            abort(403, 'Organisation inactive ou inexistante');
        }

        return $next($request);
    }
}
