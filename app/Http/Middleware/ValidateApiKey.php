<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Try to get the API key from multiple sources
        $apiKey = $this->extractApiKey($request);

        if (!$apiKey) {
            return response()->json([
                'error' => 'API key is required',
                'message' => 'Please provide a valid API key in X-API-Key header, Bearer token, or api_key query parameter'
            ], 401);
        }

        // Validate the API key
        $key = ApiKey::where('key', $apiKey)->first();

        if (!$key) {
            return response()->json([
                'error' => 'Invalid API key',
                'message' => 'The provided API key is not valid'
            ], 401);
        }

        if (!$key->isActive()) {
            return response()->json([
                'error' => 'API key is inactive',
                'message' => 'The provided API key has been deactivated'
            ], 403);
        }

        // Mark the key as used
        $key->markAsUsed();

        // Add the API key to the request for later use
        $request->attributes->set('api_key', $key);

        return $next($request);
    }

    /**
     * Extract API key from request
     */
    private function extractApiKey(Request $request): ?string
    {
        // Check X-API-Key header
        if ($request->header('X-API-Key')) {
            return $request->header('X-API-Key');
        }

        // Check Authorization Bearer token
        if ($request->bearerToken()) {
            return $request->bearerToken();
        }

        // Check query parameter
        if ($request->query('api_key')) {
            return $request->query('api_key');
        }

        return null;
    }
}
