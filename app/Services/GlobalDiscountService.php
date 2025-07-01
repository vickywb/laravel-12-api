<?php

namespace App\Services;

use App\Models\Discount;

class GlobalDiscountService
{
    public function getActiveGlobalDiscount(string $code): ?Discount
    {
        //  Check is dicount code currently active
        return Discount::where('code', $code)
            ->where('start_at', '<=', now())
            ->where('end_at', '>=', now())
            ->first();
    }

    public function calculatedGlobalDiscount(string $subTotal, Discount $discount): string
    {
        // Return 0 if subtotal does not meet the minimum order total
        if ($subTotal < $discount->minimum_order_total) {
            return 0;
        }

        // Calculate based on discount type: percentage or fixed
        return $discount->discount_type === 'percentage'
            ? bcmul($subTotal, bcdiv($discount->discount_amount, 100, 2), 2)
            : min($discount->discount_amount, $subTotal);
    }
}