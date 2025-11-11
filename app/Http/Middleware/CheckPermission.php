<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware CheckPermission
 *
 * Vérifie qu'un utilisateur possède une permission spécifique
 *
 * Usage:
 * Route::middleware('permission:projects_view')->get('/projects', ...);
 * Route::middleware('permission:projects_create')->post('/projects', ...);
 */
class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $permission  Le slug de la permission (ex: "projects_view")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        // Vérifier que l'utilisateur est authentifié
        if (!auth()->check()) {
            abort(401, 'Non authentifié');
        }

        $user = auth()->user();

        // Vérifier que le user a la permission
        if (!$user->hasPermission($permission)) {
            abort(403, "Vous n'avez pas la permission '{$permission}' requise pour accéder à cette ressource.");
        }

        return $next($request);
    }
}
