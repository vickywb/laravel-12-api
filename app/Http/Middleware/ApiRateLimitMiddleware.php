<?php

namespace App\Http\Middleware;

use App\Helpers\LoggerHelper;
use App\Helpers\ResponseApiHelper;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ApiRateLimitMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $action = 'default', int $maxAttempts = 5, int $decaySeconds = 60): Response
    {
        // Unique identifier for rate limiting, combining action type (e.g., login/register) with user IP or email
        $identifier = match ($action) {
            'register', 'login' => $request->ip() . '|' . strtolower($request->email),
            default => $request->ip()
        };
        // Rate limit key: combines action type with user identifier
        $key = "throttle:$action:$identifier";

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            // Logging
            LoggerHelper::error("Too many {$action} attempts.", [
                'email' => $request->email,
                'action' => $action,
                'ip' => $request->ip(),
                'retry_after_seconds' => RateLimiter::availableIn($key)
            ]);

            return ResponseApiHelper::error("Too many {$action} attempts. Try again later.", [
                'retry_after_seconds' => RateLimiter::availableIn($key)
            ], 429);
        }

        // Hit Rate Limit
        RateLimiter::hit($key, $decaySeconds);

        return $next($request);
    }
}
