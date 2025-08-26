<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Helpers\CartHelper;
use App\Models\Cart;
use App\Models\Product;
use App\Helpers\LoggerHelper;
use App\Helpers\ProductDiscountHelper;
use App\Helpers\ResponseApiHelper;
use Illuminate\Support\Facades\DB;

class CartService
{
    public function addItem(int $userId, int $productId, int $quantity): Cart
    {
        // Search active product discount on product
        $product = Product::with('activeDiscount')->findOrFail($productId);

        // Check product exists on cart using helper
        $cart = CartHelper::getLockedCart($userId, $productId);

        // Check quantity in cart
        $totalQuantity = ($cart ? $cart->quantity : 0) + $quantity;

        if ($totalQuantity > $product->stock) {
            throw new ApiException('Quantity exceeds available stock.');
        }

        // Product Price
        $priceAtTime = ProductDiscountHelper::getPriceAtTime($product);

        try {
            DB::beginTransaction();

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
            
            throw new ApiException('Failed to add item on cart');
        }
    }

    public function decreaseItem(int $userId, int $quantity, Cart $cart): ?Cart
    {
        $cart = CartHelper::getLockedCartById(['product'], $cart->id);

        try {
            DB::beginTransaction();

            if ($quantity > $cart->quantity) {
                throw new ApiException('Cannot decrease more than current quantity.');
            }

            // Decrease the current quantity
            $newQuantity = $cart->quantity - $quantity;

            if ($newQuantity <= 0) {
                // if quantity <= 0 delete item on cart
                $cart->delete();
                // Log
                LoggerHelper::info('Cart item successfully deleted.', [
                    'user_id' => $userId,
                    'product_id' => $cart->product_id,
                ]);

                $result = null;

            } else {

                $cart->update(['quantity' => $newQuantity]);
                // Log
                LoggerHelper::info('Cart item successfully decreased.', [
                    'user_id' => $userId,
                    'product_id' => $cart->product_id,
                    'quantity' => (int)$newQuantity,
                ]);
                
                $result = $cart->fresh(['product']);
            }

            DB::commit();

            return $result;

        } catch (\Throwable $th) {
            DB::rollBack();

            // Log
            LoggerHelper::error('Failed to decrease item on cart', [
                'user_id' => $userId,
                'error' => $th->getMessage(),
            ]);
            
            throw new ApiException('Failed to decrease item on cart');
        }
    }

    public function replaceItem(int $userId, int $quantity, Cart $cart): ?Cart
    {
        $cart = CartHelper::getLockedCartById(['product'], $cart->id);
        
        try {
            DB::beginTransaction();
                
            if ($quantity > $cart->product->stock) {
                throw new ApiException('Quantity exceeds available stock.');
            }
            
            if ($quantity <= 0) {
                // if quantity <= 0 delete item on cart
                $cart->delete();
                // Log
                LoggerHelper::info('Cart item successfully deleted.', [
                    'user_id' => $userId,
                    'product_id' => $cart->product_id,
                ]);
                $result = null;
            } else {
                
                $cart->update(['quantity' => $quantity]);
                // Log
                LoggerHelper::info('Cart item successfully replaced.', [
                    'user_id' => $userId,
                    'product_id' => $cart->product_id,
                    'quantity' => (int)$quantity,
                ]);

                $result = $cart->fresh(['product']);
            }

            DB::commit();

            return $result;

        } catch (\Throwable $th) {
            DB::rollBack();

            // Log
            LoggerHelper::error('Failed to replaced item on cart', [
                'user_id' => $userId,
                'error' => $th->getMessage(),
            ]);

            throw new ApiException('Failed to replace item on cart');
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
            
            throw new ApiException('Failed to delete item on cart');
        }
    }

    // Synchronize cart if needed
    
    // public function syncCart(int $userId, array $items): void
    // {
    //     try {
    //         DB::beginTransaction();

    //         foreach ($items as $item) {
    //             $product = Product::with('activeDiscount')->findOrFail($item['product_id']);
                
    //             // Check cart exist
    //             $cart = CartHelper::getLockedCart($userId, $product->id);

    //             $totalQuantity = ($cart ? $cart->quantity : 0) + $item['quantity'];
                
    //             if ($totalQuantity > $product->stock) {
    //                 throw new ApiException('Quantity exceeds available stock.');
    //             }

    //             $priceAtTime = ProductDiscountHelper::getPriceAtTime($product);

    //             if ($cart) {
    //                 $cart->update([
    //                     'quantity' => (int)$totalQuantity,
    //                     'price_at_time' => $priceAtTime,
    //                 ]);
    //             } else {
    //                 Cart::create([
    //                     'user_id' => $userId,
    //                     'product_id' => $product->id,
    //                     'quantity' => (int)$totalQuantity,
    //                     'price_at_time' => $priceAtTime,
    //                 ]);
    //             }
    //         }

    //         DB::commit();

    //         LoggerHelper::info('Cart synced successfully', [
    //             'user_id' => $userId,
    //             'items' => $items,
    //         ]);
    //     } catch (\Throwable $th) {
    //         DB::rollBack();

    //         LoggerHelper::error('Failed to sync cart', [
    //             'user_id' => $userId,
    //             'error' => $th->getMessage(),
    //         ]);

    //         throw new ApiException('Failed to sync cart');
    //     }
    // }
}