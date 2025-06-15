<?php

use App\Http\Controllers\Api\V1\Admin\CategoryController;
use App\Http\Controllers\Api\V1\Admin\DiscountController;
use App\Http\Controllers\Api\V1\File\FileController;
use App\Http\Controllers\Api\V1\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Api\V1\Admin\ProductDiscountController;
use App\Http\Controllers\Api\V1\User\ProductController;
use App\Http\Controllers\Api\V1\Admin\RoleController;
use App\Http\Controllers\Api\V1\Admin\TransactionDetailController;
use App\Http\Controllers\Api\V1\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\User\CartController;

// Public routes
Route::prefix('auth')
    ->controller(AuthController::class)
    ->middleware('guest')
    ->group(function () {
        Route::post('login', 'login')->middleware('throttle-api:login,3,60'); // Limit 3 times delayed 1 minute
        Route::post('register', 'register')->middleware('throttle-api:register,3,180'); // Limit 3 times delayed 3 minute
});

Route::prefix('files')
    ->controller(FileController::class)
    ->group(function () {
        Route::post('/upload', 'upload');
});

Route::prefix('products')
    ->controller(ProductController::class)
    ->group(function () {
        Route::get('/', 'index');
        Route::get('{product}/product-detail', 'show');
});

// Authenticated routes
Route::middleware('auth-api')->group(function () {

    Route::prefix('auth')
        ->controller(AuthController::class)
        ->group(function () {
            Route::post('logout', 'logout');
    });
    
    // User routes
    Route::prefix('user')
        ->group(function () {

        Route::prefix('profiles')
            ->controller(AuthController::class)
            ->group(function () {
                Route::patch('update-profiles', 'updateProfile');
        });

        Route::prefix('carts')
            ->controller(CartController::class)
            ->group(function () {
                Route::get('/', 'index');
                Route::post('/store', 'store');
                Route::patch('{cart}/update', 'update');
                Route::delete('{cart}/delete', 'destroy');
        });
    });

    // Admin Panel
    Route::prefix('admin')
        ->middleware('admin-api')
        ->group(function () {

        Route::prefix('users')
            ->group(function () {
                Route::get('/', [AdminUserController::class, 'index']);
                Route::get('/transaction-details', [TransactionDetailController::class, 'index']);
        });
        
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
            ->controller(AdminProductController::class)
            ->group(function () {
                Route::get('/', 'index');
                Route::post('/store', 'store');
                Route::get('{product}/show', 'show');
                Route::patch('{product}/update', 'update');
                Route::delete('{product}/delete', 'destroy');
        });

        Route::prefix('product-discounts')
            ->controller(ProductDiscountController::class)
            ->group(function () {
                Route::get('/', 'index');
                Route::post('/store', 'store');
                Route::patch('{productDiscount}/update', 'update');
                Route::delete('{productDiscount}/delete', 'destroy');
        });

        Route::prefix('discounts')
            ->controller(DiscountController::class)
            ->group(function () {
                Route::get('/', 'index');
                Route::post('/store', 'store');
                Route::patch('{discount}/update', 'update');
                Route::delete('{discount}/delete', 'destroy');
        });
    });
});
