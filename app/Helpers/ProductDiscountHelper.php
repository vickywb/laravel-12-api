<?php

namespace App\Helpers;

use App\Models\Product;

class ProductDiscountHelper
{
    public static function getPriceAtTime(Product $product): string
    {
        if ($product->activeDiscount) {
            return bcsub($product->price, $product->activeDiscount->discount_price, 2); //2  decimal
        }

        return $product->price;
    }
}