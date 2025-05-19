<?php

use App\Http\Middleware\AdminApiMiddleware;
use App\Http\Middleware\ApiRateLimitMiddleware;
use Illuminate\Foundation\Application;
use App\Http\Middleware\AuthApiMiddleware;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('api')
                ->prefix('api/v1')
                ->group(base_path('routes/api_v1.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'auth-api' => AuthApiMiddleware::class,
            'throttle-api' => ApiRateLimitMiddleware::class,
            'admin-api' => AdminApiMiddleware::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
