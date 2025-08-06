<?php

namespace App\Providers;

use App\Events\OrderPaid;
use App\Listeners\ReduceDiscountUsage;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        OrderPaid::class => [
            ReduceDiscountUsage::class,
        ],
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
