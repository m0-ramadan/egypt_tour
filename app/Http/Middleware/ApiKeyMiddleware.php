<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ApiKeyMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->header('api-key');
        $expectedApiKey = env('ERP_URL');

        if (!$apiKey || $apiKey !== $expectedApiKey) {
            Log::warning('Unauthorized API access attempt', ['provided_key' => $apiKey]);
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: Invalid API key',
            ], 401);
        }

        return $next($request);
    }
}
