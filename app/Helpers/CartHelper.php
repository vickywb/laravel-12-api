<?php

namespace App\Helpers;

use App\Models\Cart;

class CartHelper
{
    // Get Locked cart by user and product
    public static function getLockedCart(int $userId, int $productId): ?Cart
    {
        return Cart::where('product_id', $productId)
            ->where('user_id', $userId)
            ->lockForUpdate()
            ->first();
    }

    // Get Locked cart by cart id with product relationship
    public static function getLockedCartById(array $with = [], int $cartId): Cart
    {
        return Cart::with($with)
            ->where('id', $cartId)
            ->lockForUpdate()
            ->firstOrFail();   
    }
}