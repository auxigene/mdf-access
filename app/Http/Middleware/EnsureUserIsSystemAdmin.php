<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to ensure the authenticated user is a system administrator.
 *
 * This middleware checks if the currently authenticated user has the
 * 'is_system_admin' flag set to true. If not, it returns a 403 Forbidden response.
 *
 * Usage in routes:
 * Route::middleware(['auth', 'verified', 'admin'])->group(function () {
 *     // Admin-only routes
 * });
 */
class EnsureUserIsSystemAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (! $request->user()) {
            abort(401, 'Unauthenticated.');
        }

        // Check if user is a system admin
        if (! $request->user()->is_system_admin) {
            abort(403, 'Access denied. System administrator privileges required.');
        }

        return $next($request);
    }
}
