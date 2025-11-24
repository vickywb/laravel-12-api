<?php

use App\Http\Controllers\Api\V1\Admin\CategoryController;
use App\Http\Controllers\Api\V1\Admin\DiscountController;
use App\Http\Controllers\Api\V1\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Api\V1\File\FileController;
use App\Http\Controllers\Api\V1\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Api\V1\Admin\ProductDiscountController;
use App\Http\Controllers\Api\V1\User\ProductController;
use App\Http\Controllers\Api\V1\Admin\RoleController;
use App\Http\Controllers\Api\V1\Admin\TransactionController as AdminTransactionController;
use App\Http\Controllers\Api\V1\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Midtrans\MidtransWebhookController;
use App\Http\Controllers\Api\V1\User\CartController;
use App\Http\Controllers\Api\V1\User\OrderController;
use App\Http\Controllers\Api\V1\User\TransactionController;
use App\Http\Controllers\HomeController;
use Illuminate\Routing\RouteGroup;

// Home
Route::get('/', [HomeController::class, 'index']);

// Public routes
Route::prefix('auth')
    ->controller(AuthController::class)
    ->middleware('guest')
    ->group(function () {
        Route::post('login', 'login')->middleware('throttle-api:login,5,60'); // Limit 5 times delayed 1 minute
        Route::post('register', 'register')->middleware('throttle-api:register,5,180'); // Limit 5 times delayed 3 minute
});

// Files for upload user/admin
Route::prefix('files')
    ->controller(FileController::class)
    ->group(function () {
        Route::post('/upload', 'upload');
});

// Public Product
Route::prefix('products')
    ->controller(ProductController::class)
    ->group(function () {
        Route::get('/', 'index');
        Route::get('{product:slug}/product-detail', 'show');
        // Route::get('{product}/product-detail', 'show');
});

// Midtrans callback
Route::prefix('midtrans')
    ->controller(MidtransWebhookController::class)
    ->group(function () {
        Route::post('webhook', 'handle');
    });

// Authenticated routes
Route::middleware('auth-api')->group(function () {

    Route::prefix('auth')
        ->controller(AuthController::class)
        ->group(function () {
            Route::get('me', 'me');
            Route::post('logout', 'logout');
    });
    
    // User routes
    Route::prefix('user')
        ->group(function () {

        Route::prefix('profiles')
            ->controller(AuthController::class)
            ->group(function () {
                Route::get('/', 'getProfile');
                Route::patch('update', 'updateProfile');
        });

        Route::prefix('carts')
            ->controller(CartController::class)
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('add', 'add')->name('add')->middleware('throttle-api:add-cart,10,60');
                Route::post('{cart}/decrease', 'decrease')->name('decrease')->middleware('throttle-api:decrease-cart,10,60');
                Route::patch('{cart}/replace', 'replace')->name('replace')->middleware('throttle-api:replace-cart,10,60');
                Route::delete('{cart}/remove', 'remove')->name('remove')->middleware('throttle-api:remove-cart,10,60');

                Route::get('/summary', 'summary')->name('summary');
                Route::post('/sync','syncCart')->name('sync');
                Route::post('/validate-checkout', 'validateCheckout')->name('validateCheckout');
                Route::delete('/clear', 'clear')->name('clear');
        });

        Route::prefix('orders')
            ->controller(OrderController::class)
            ->group(function () {
                // Orders
                Route::get('/', 'index');
                Route::post('/', 'store')->middleware('throttle-api:add-order,5,60');
                Route::get('{order}', 'show');
        });

        // User Transactions
        Route::get('/transactions', [TransactionController::class, 'index']);
        Route::get('/transactions/{transaction}/transaction-detail', [TransactionController::class, 'show']);
        Route::post('/orders/{order}/checkout', [TransactionController::class, 'store'])->middleware('throttle-api:checkout-order,3,60');

    });

    // Admin Panel
    Route::prefix('admin')
        ->middleware('admin-api')
        ->group(function () {

        Route::prefix('users')
            ->group(function () {
                Route::get('/', [AdminUserController::class, 'index']);
        });
        
        Route::prefix('roles')
            ->controller(RoleController::class)
            ->group(function () {
                Route::get('/', 'index');
                Route::post('store', 'store');
                Route::get('{role}/detail', 'show');
                Route::patch('{role}/update', 'update');
                Route::delete('{role}/delete', 'destroy');
        });

        Route::prefix('categories')
            ->controller(CategoryController::class)
            ->group(function () {
                Route::get('/', 'index');
                Route::post('store', 'store');
                Route::get('{category}/detail', 'show');
                Route::patch('{category}/update', 'update');
                Route::delete('{category}/delete', 'destroy');
        });

        Route::prefix('products')
            ->controller(AdminProductController::class)
            ->group(function () {
                Route::get('/', 'index');
                Route::post('store', 'store');
                Route::get('{product}', 'show');
                Route::patch('{product}/update', 'update');
                Route::delete('{product}/delete', 'destroy');
        });

        Route::prefix('product-discounts')
            ->controller(ProductDiscountController::class)
            ->group(function () {
                Route::get('/', 'index');
                Route::post('store', 'store');
                Route::patch('{productDiscount}/update', 'update');
                Route::delete('{productDiscount}/delete', 'destroy');
        });

        Route::prefix('discounts')
            ->controller(DiscountController::class)
            ->group(function () {
                Route::get('/', 'index');
                Route::post('store', 'store');
                Route::patch('{discount}/update', 'update');
                Route::delete('{discount}/delete', 'destroy');
        });

        Route::prefix('transactions')
            ->controller(AdminTransactionController::class)
            ->group(function () {
                Route::get('/', 'index');
                Route::get('{transaction}/transaction-detail', 'show');
                Route::patch('{transaction}/update', 'update');
                Route::delete('{transaction}/delete', 'destroy');
        });

        Route::prefix('orders')
            ->controller(AdminOrderController::class)
            ->group(function () {
                Route::get('/', 'index');
                Route::get('{order}/detail', 'show');
                Route::patch('{order}/update', 'update');
                Route::delete('{order}/delete', 'destroy');
        });
    });
});
