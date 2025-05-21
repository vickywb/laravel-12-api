<?php

use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\Auth\AuthController;


Route::prefix('auth')
    ->controller(AuthController::class)
    ->group(function () {
        Route::post('login', 'login')->middleware('throttle-api:login,3,60'); // Limit 3 times delayed 1 minute
        Route::post('register', 'register')->middleware('throttle-api:register,3,180'); // Limit 3 times delayed 3 minute
        Route::post('logout', 'logout')->middleware('auth-api');
});

Route::middleware('auth-api')->group(function () {

    Route::prefix('admin')
        ->middleware('admin-api')
        ->group(function () {
        
        Route::prefix('roles')
            ->controller(RoleController::class)
            ->group(function () {
                Route::get('/', 'index');
                Route::post('/store', 'store');
                Route::patch('{role}/update', 'update');
                Route::delete('{role}/delete', 'destroy');
        });
    });
    
});
