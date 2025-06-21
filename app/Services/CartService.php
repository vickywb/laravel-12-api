<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use App\Helpers\LoggerHelper;
use App\Helpers\ProductDiscountHelper;
use Illuminate\Support\Facades\DB;

class CartService
{
    public function addItem(int $userId, int $productId, int $quantity): Cart
    {
        // Search active product discount on product
        $product = Product::with('activeDiscount')->findOrFail($productId);

        if ($quantity > $product->stock) {
            throw new \RuntimeException('Quantity exceeds available stock.');
        }

        // Product Price
        $priceAtTime = ProductDiscountHelper::getPriceAtTime($product);

        try {
            DB::beginTransaction();

            // Check product exists on cart
            $cart = Cart::where('user_id', $userId)
                ->where('product_id', $productId)
                ->lockForUpdate()
                ->first();

            if ($cart) {
                // if product already exists in cart, update the quantity
                $cart->increment('quantity', (int) $quantity);
            } else {
                // If not exists, create new cart
                $cart = Cart::create([
                    'user_id'    => $userId,
                    'product_id' => $productId,
                    'quantity'   => (int)$quantity,
                    'price_at_time' => $priceAtTime
                ]);
            }

            DB::commit();

            // Log
            LoggerHelper::info('Cart item successfully added.', [
                'user_id' => $userId,
                'product_id' => $productId,
                'quantity' => (int)$quantity,
            ]);

            return $cart->fresh(['product']);

        } catch (\Throwable $th) {
            DB::rollBack();

            // Log
            LoggerHelper::error('Failed to add item on cart', [
                'user_id' => $userId,
                'error' => $th->getMessage(),
            ]);
            
            throw $th;
        }
    }

    public function decreaseItem(int $userId, int $quantity, Cart $cart): ?Cart
    {
        try {
            DB::beginTransaction();

            if ($quantity > $cart->quantity) {
                throw new \RuntimeException('Cannot decrease more than current quantity.');
            }

            // Decrease the current quantity
            $newQuantity = $cart->quantity - $quantity;

            if ($newQuantity <= 0) {
                // if quantity <= 0 delete item on cart
                $cart->delete();
                return null;
            }

            $cart->update(['quantity' => $newQuantity]);

            DB::commit();

            // Log
            LoggerHelper::info('Cart item successfully decreased.', [
                'user_id' => $userId,
                'product_id' => $cart->product_id,
                'quantity' => (int)$newQuantity,
            ]);

            return $cart->fresh(['product']);

        } catch (\Throwable $th) {
            DB::rollBack();

            // Log
            LoggerHelper::error('Failed to decrease item on cart', [
                'user_id' => $userId,
                'error' => $th->getMessage(),
            ]);
            
            throw $th;
        }
    }

    public function replaceItem(int $userId, int $quantity, Cart $cart): ?Cart
    {
        try {
            DB::beginTransaction();
                
            if ($quantity > $cart->product->stock) {
                throw new \RuntimeException('Quantity exceeds available stock.');
            }
            
            if ($quantity <= 0) {
                // if quantity <= 0 delete item on cart
                $cart->delete();
                return null;
            }

            $cart->update(['quantity' => $quantity]);

            DB::commit();

            // Log
            LoggerHelper::info('Cart item successfully replaced.', [
                'user_id' => $userId,
                'product_id' => $cart->product_id,
                'quantity' => (int)$quantity,
            ]);

            return $cart->fresh(['product']);

        } catch (\Throwable $th) {
            DB::rollBack();

            // Log
            LoggerHelper::error('Failed to replaced item on cart', [
                'user_id' => $userId,
                'error' => $th->getMessage(),
            ]);
            
            throw $th;
        }
    }

    public function removeItem(int $userId, Cart $cart): void
    {
        try {
            DB::beginTransaction();
            
            $cart->delete();
            
            DB::commit();

            // Log
            LoggerHelper::info('Cart item successfully removed.', [
                'user_id' => $userId,
                'product_id' => $cart->product_id,
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();

            // Log
            LoggerHelper::error('Failed to delete item on cart', [
                'user_id' => $userId,
                'error' => $th->getMessage(),
            ]);
            
            throw $th;
        }
    }
}