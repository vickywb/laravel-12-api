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

    // Return boolean
    public function removeItem(int $userId, Cart $cart): bool
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

            return true;

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

    // Clear all cart items for user
    public function clearCart(int $userId): bool
    {
        try {
            DB::beginTransaction();
            
            $deletedCount = Cart::where('user_id', $userId)->delete();
            
            DB::commit();

            // Log
            LoggerHelper::info('Cart cleared successfully.', [
                'user_id' => $userId,
                'deleted_items' => $deletedCount,
            ]);

            return true;

        } catch (\Throwable $th) {
            DB::rollBack();

            // Log
            LoggerHelper::error('Failed to clear cart', [
                'user_id' => $userId,
                'error' => $th->getMessage(),
            ]);
            
            throw new ApiException('Failed to clear cart');
        }
    }

    // Get cart summary
    public function getCartSummary(int $userId): array
    {
        $carts = Cart::with(['product', 'product.activeDiscount'])
                    ->where('user_id', $userId)
                    ->get();

        $totalItems = $carts->sum('quantity');
        $totalPrice = $carts->sum(function ($cart) {
            return $cart->quantity * $cart->price_at_time;
        });

        return [
            'total_items' => $totalItems,
            'total_price' => $totalPrice,
            'items_count' => $carts->count(),
        ];
    }

    // Synchronize cart for guest users when they login
    public function syncCart(int $userId, array $items): array
    {
        try {
            DB::beginTransaction();

            $syncedItems = [];
            $errors = [];

            foreach ($items as $item) {
                try {
                    $product = Product::with('activeDiscount')->findOrFail($item['product_id']);
                    
                    // Check existing cart
                    $cart = CartHelper::getLockedCart($userId, $product->id);
                    
                    $quantity = (int)$item['quantity'];
                    $totalQuantity = ($cart ? $cart->quantity : 0) + $quantity;
                    
                    if ($totalQuantity > $product->stock) {
                        $errors[] = "Product {$product->name}: Quantity exceeds available stock.";
                        continue;
                    }

                    $priceAtTime = ProductDiscountHelper::getPriceAtTime($product);

                    if ($cart) {
                        $cart->update([
                            'quantity' => $totalQuantity,
                            'price_at_time' => $priceAtTime,
                        ]);
                    } else {
                        $cart = Cart::create([
                            'user_id' => $userId,
                            'product_id' => $product->id,
                            'quantity' => $quantity,
                            'price_at_time' => $priceAtTime,
                        ]);
                    }

                    $syncedItems[] = $cart->fresh(['product']);

                } catch (\Exception $e) {
                    $errors[] = "Product ID {$item['product_id']}: " . $e->getMessage();
                }
            }

            DB::commit();

            LoggerHelper::info('Cart synced successfully', [
                'user_id' => $userId,
                'synced_items' => count($syncedItems),
                'errors' => count($errors),
            ]);

            return [
                'synced_items' => $syncedItems,
                'errors' => $errors,
                'success_count' => count($syncedItems),
                'error_count' => count($errors),
            ];

        } catch (\Throwable $th) {
            DB::rollBack();

            LoggerHelper::error('Failed to sync cart', [
                'user_id' => $userId,
                'error' => $th->getMessage(),
            ]);

            throw new ApiException('Failed to sync cart');
        }
    }

    // Validate cart before checkout
    public function validateCartForCheckout(int $userId): array
    {
        $carts = Cart::with(['product'])->where('user_id', $userId)->get();
        
        if ($carts->isEmpty()) {
            throw new ApiException('Cart is empty');
        }

        $errors = [];
        $validItems = [];

        foreach ($carts as $cart) {
            if (!$cart->product) {
                $errors[] = "Product not found for cart item ID: {$cart->id}";
                continue;
            }

            if ($cart->quantity > $cart->product->stock) {
                $errors[] = "Insufficient stock for {$cart->product->name}. Available: {$cart->product->stock}, Requested: {$cart->quantity}";
                continue;
            }
            
            if (isset($cart->product->deleted_at) && $cart->product->deleted_at !== null) {
                $errors[] = "Product {$cart->product->name} is no longer available";
                continue;
            }

            $validItems[] = $cart;
        }

        return [
            'valid_items' => $validItems,
            'errors' => $errors,
            'is_valid' => empty($errors)
        ];
    }
}