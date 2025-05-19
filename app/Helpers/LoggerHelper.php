<?php

namespace App\Helpers;

use App\Helpers\AuthHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LoggerHelper
{
    // This Log used when data processing is success
    public static function info($message, array $context = [])
    {
        $context = self::formatContext($context);
        Log::info($message, $context);
    }

    // This Log used when an operation fails
    public static function error($message, array $context = [])
    {
        $context = self::formatContext($context);
        Log::error($message, $context);
    }

    // This Log used when an non-critical issues that should be reviewed
    public static function warning($message, array $context = [])
    {
        $context = self::formatContext($context);
        Log::warning($message, $context);
    }

    // This Log used when an normal but significant events
    public static function notice($message, array $context = [])
    {
        $context = self::formatContext($context);
        Log::notice($message, $context);
    }

    // This Log used when an important conditions that require immediate attention
    public static function alert($message, array $context = [])
    {
        $context = self::formatContext($context);
        Log::alert($message, $context);
    }

    // This is format data for context information to be included in each log entery
    private static function formatContext(array $context)
    {
        $user = AuthHelper::getUserFromToken(request()->bearerToken());

        // Add user ID to context if user is authenticated
        $context['user_id'] = $user?->id;
        
        // Add ip address
        $context['ip_address'] = request()->ip();

        // Add the user agent string(browser/device info)
        $context['user_agent'] = request()->userAgent();

        // Add current date and time
        $context['datetime'] = now()->format('d F Y, H:i:s');

        return $context;
    }
}