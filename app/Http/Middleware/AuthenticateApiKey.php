<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string|null  $apiType  Type d'API requis (optionnel)
     * @param  string|null  $accessLevel  Niveau d'accès minimum requis (optionnel)
     */
    public function handle(Request $request, Closure $next, ?string $apiType = null, ?string $accessLevel = null): Response
    {
        // Extraire la clé API du header ou du query parameter
        $apiKeyValue = $request->header('X-API-Key') ?? $request->query('api_key');

        if (!$apiKeyValue) {
            return response()->json([
                'error' => 'API key is missing',
                'message' => 'Vous devez fournir une clé API via le header X-API-Key ou le paramètre api_key'
            ], 401);
        }

        // Hasher la clé pour la comparer avec la base de données
        $hashedKey = ApiKey::hashKey($apiKeyValue);

        // Chercher la clé API dans la base de données
        $apiKey = ApiKey::where('key', $hashedKey)->first();

        if (!$apiKey) {
            return response()->json([
                'error' => 'Invalid API key',
                'message' => 'La clé API fournie est invalide'
            ], 401);
        }

        // Vérifier que la clé est valide (active et non expirée)
        if (!$apiKey->isValid()) {
            $reason = !$apiKey->is_active
                ? 'La clé API est désactivée'
                : 'La clé API a expiré';

            return response()->json([
                'error' => 'Invalid API key',
                'message' => $reason
            ], 401);
        }

        // Vérifier le type d'API si spécifié
        if ($apiType && !$apiKey->hasApiType($apiType)) {
            return response()->json([
                'error' => 'Unauthorized API type',
                'message' => "Cette clé API n'a pas accès au type d'API: {$apiType}"
            ], 403);
        }

        // Vérifier le niveau d'accès si spécifié
        if ($accessLevel && !$apiKey->hasAccessLevel($accessLevel)) {
            return response()->json([
                'error' => 'Insufficient access level',
                'message' => "Cette clé API ne dispose pas du niveau d'accès requis: {$accessLevel}"
            ], 403);
        }

        // Mettre à jour la date de dernière utilisation
        $apiKey->markAsUsed();

        // Attacher la clé API à la requête pour un usage ultérieur
        $request->merge(['api_key_model' => $apiKey]);

        return $next($request);
    }
}
