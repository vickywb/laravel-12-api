<?php

namespace App\Helpers;

use App\Exceptions\ApiException;

class ExceptionHelper
{
    public static function throw(string $message, int $code = 400): never
    {
        throw new ApiException($message, $code);
    }

    public static function throwIfNull($value, string $message, int $code = 400): void
    {
        if (is_null($value)) {
            self::throw($message, $code);
        }
    }
}