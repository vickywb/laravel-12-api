<?php

use App\Http\Controllers\Api\V1\Admin\CategoryController;
use App\Http\Controllers\Api\V1\Admin\ProductController;
use App\Http\Controllers\Api\V1\Admin\RoleController;
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
                Route::get('{role}/show', 'show');
                Route::patch('{role}/update', 'update');
                Route::delete('{role}/delete', 'destroy');
        });

        Route::prefix('categories')
            ->controller(CategoryController::class)
            ->group(function () {
                Route::get('/', 'index');
                Route::post('/store', 'store');
                Route::get('{category}/show', 'show');
                Route::patch('{category}/update', 'update');
                Route::delete('{category}/delete', 'destroy');
        });

        Route::prefix('products')
            ->controller(ProductController::class)
            ->group(function () {
                Route::get('/', 'index');
                Route::post('/store', 'store');
                Route::get('{product}/show', 'show');
                Route::patch('{product}/update', 'update');
                Route::delete('{product}/delete', 'destroy');

                // Product Files
                Route::get('{product}/product-files', 'fileIndex');
                Route::post('{product}/product-files/store', 'fileStore');
                Route::patch('{product}/product-files/update/{file}', 'fileUpdate');
                Route::delete('{product}/product-files/delete/{file}', 'fileDestroy');
        });
    });
    
});
