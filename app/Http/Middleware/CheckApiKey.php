<?php

namespace App\Http\Middleware;

use App\Enums\ResponseStatusCode;
use Closure;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckApiKey
{
    use ApiResponse;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-KEY');
        $allowedKeys = explode(',', env('API_KEYS'));

        if (!in_array($apiKey, $allowedKeys)) {
            return $this->sendResponse(
                null,
                ResponseStatusCode::FORBIDDEN,
                'Access forbidden',
            );
        }

        return $next($request);
    }
}
