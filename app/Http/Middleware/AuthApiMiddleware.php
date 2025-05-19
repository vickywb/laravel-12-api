<?php

namespace App\Http\Middleware;

use App\Helpers\LoggerHelper;
use App\Helpers\ResponseApiHelper;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class AuthApiMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {   
        $token = $request->bearerToken();
        $personalAccessToken = PersonalAccessToken::findToken($token);

        if (! $personalAccessToken) {
            LoggerHelper::error('Access Denied: Unauthorized, missing or invalid token.', [
                'token' => $token
            ]);

            return ResponseApiHelper::error('Access Denied: Unauthorized, missing or invalid token.', [
                'token' => 'Token Invalid'
            ], 401);
        }

        $user = User::find($personalAccessToken->tokenable_id);

        if (! $user) {
            LoggerHelper::error('Token not found.', [
                'token' => $token
            ]);

            return ResponseApiHelper::error('Access Denied: Unauthorized.', [
                'token' => 'Token Invalid'
            ], 401);
        }

        $abilities = $personalAccessToken->abilities;

        if (! in_array($user->role->slug, $abilities) || $personalAccessToken->expires_at < now()) {
            $personalAccessToken ? $personalAccessToken->delete() : null;

            LoggerHelper::warning('Token Invalid or Expired.', [
                'token' => $token,
                'user_id' => $user->id
            ]);

            return ResponseApiHelper::error('Access Denied: Unauthorized.', [
                'token' => 'Token Invalid.'
            ], 401);
        }

        $personalAccessToken->update(['last_used_at' => now()]);

        LoggerHelper::info('Token Validated.', [
            'token' => $token,
            'user_id' => $user->user_id
        ]);

        return $next($request);
    }
}
