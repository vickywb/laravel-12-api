<?php

namespace App\Listeners;

use App\Models\Discount;
use App\Events\OrderPaid;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ReduceDiscountUsage
{
    /**
     * Handle the event.
     */
    public function handle(OrderPaid $event): void
    {
        $order = $event->order;

        // If the order does not have a discount code, do nothing
        if (empty($order->discount_code)) {
            return;
        }

        $discount = Discount::where('code', $order->discount_code)
            ->lockForUpdate()
            ->first();

        // If the discount does not exist, or if it has no usage limit, do nothing
        if (!$discount || is_null($discount->usage_limit) || $discount->usage_limit <= 0) {
            return;
        }

        $discount->decrement('usage_limit');
    }
}
