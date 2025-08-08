<?php

namespace App\Helpers;

use App\Models\Product;

class ProductDiscountHelper
{
    public static function getPriceAtTime(Product $product): string
    {
        if ($product->activeDiscount) {
            if ($product->activeDiscount->discount_type === 'percentage') {
                return bcsub($product->price, bcmul($product->price, $product->activeDiscount->discount_value / 100));
            } elseif ($product->activeDiscount->discount_type === 'fixed') {
                return bcsub($product->price, $product->activeDiscount->discount_value);
            }
        }
        // If no active discount, return the original price
        return $product->price;
    }
}