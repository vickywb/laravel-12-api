<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Helpers\LoggerHelper;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function createOrderFromCart(int $userId): Order
    {
        $carts = Cart::with('product')
            ->where('user_id', $userId)
            ->lockForUpdate()
            ->get();

        if ($carts->isEmpty()) {
            throw new \RuntimeException('Cart is empty.');
        }

        // Count total price
        $totalPrice = $carts->sum(function ($cart) {
            return bcmul($cart->quantity, $cart->price_at_time, 2);
        });

        try {
            DB::beginTransaction();

            // Create order
            $order = Order::create([
                'user_id' => $userId,
                'order_status' => OrderStatus::PENDING->value,
                'total_price' => $totalPrice
            ]);

            foreach ($carts as $cart) {
                // Create Order detail
                $orderDetail = OrderDetail::create([
                    'order_id' => $order->id,
                    'product_id' => $cart->product_id,
                    'quantity' => $cart->quantity,
                    'unit_price' => $cart->price_at_time
                ]);
            }

            // Remove Cart after store data order and order detail
            Cart::where('user_id', $userId)->delete();

            DB::commit();
            // Log
            LoggerHelper::info('Order and Order detail successfully created.', [
                'user_id' => $userId,
                'order_id' => $order->id,
                'order_detail_id' => $orderDetail->id
            ]);

            return $order->load('orderDetails.product');
            
        } catch (\Throwable $th) {
            DB::rollBack();
            // Log
            LoggerHelper::error('Failed to store Order data.', [
                'user_id' => $userId,
                'error' => $th->getMessage()
            ]);
        
            throw $th; 
        }
    }
}