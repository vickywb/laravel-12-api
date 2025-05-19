<?php

namespace App\Http\Middleware;

use App\Helpers\AuthHelper;
use App\Helpers\LoggerHelper;
use App\Helpers\ResponseApiHelper;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminApiMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = AuthHelper::getUserFromToken($request->bearerToken());

        if ($user->role->slug !== 'admin') {
            LoggerHelper::error('Access Denied: Unauthorized.', [
                'token' => substr($request->bearerToken(), 0, 5) . '...' . substr($request->bearerToken(), -5),
                'user' => $user
            ]);

            return ResponseApiHelper::error('Access Denied: Unauthorized.', [
                'error' => 'You dont have permission to access.'
            ], 403);
        }

        return $next($request);
    }
}
