<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = config('cdn.api_key');

        if (empty($apiKey)) {
            return $next($request);
        }

        $provided = $request->header('X-CDN-Key') ?? $request->query('api_key');

        if ($provided !== $apiKey) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        return $next($request);
    }
}
