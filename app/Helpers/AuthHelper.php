<?php

namespace App\Helpers;
use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

class AuthHelper
{
    public static function getUserFromToken($token): ?User
    {
        $accessToken = PersonalAccessToken::findToken($token);
        $user = User::find($accessToken->tokenable_id ?? null);

        return $user;
    }
}