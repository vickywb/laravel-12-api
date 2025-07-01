<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Discount;
use App\Enums\OrderStatus;
use App\Models\OrderDetail;
use App\Helpers\LoggerHelper;
use Illuminate\Support\Facades\DB;

class OrderService
{
    private $globalDiscountService;

    public function __construct(GlobalDiscountService $globalDiscountService) {
        $this->globalDiscountService = $globalDiscountService;
    }

    public function createOrderFromCart(int $userId): Order
    {
        $code = request()->code;

        // Check user cart
        $carts = Cart::with('product')
            ->where('user_id', $userId)
            ->lockForUpdate()
            ->get();

        if ($carts->isEmpty()) {
            throw new \RuntimeException('Cart is empty.');
        }

        // sub total price
        $subTotal = $carts->sum(function ($cart) {
            return bcmul($cart->quantity, $cart->price_at_time, 2);
        });

        // Initial default
        $discountAmount = 0;
        $discountType = null;
        $discountCode = null;
        
        // Check if code exists
        if (!empty($code)) {
            $globalDiscount = $this->globalDiscountService->getActiveGlobalDiscount($code);

            if ($globalDiscount) {
                $discountAmount = $this->globalDiscountService?->calculatedGlobalDiscount($subTotal, $globalDiscount);
                $discountType = $globalDiscount?->discount_type;
                $discountCode = $globalDiscount?->code;
            }
        }

        // Finap price
        $finalPrice = bcsub($subTotal, $discountAmount, 2);

        try {
            DB::beginTransaction();

            // Create order
            $order = Order::create([
                'user_id' => $userId,
                'order_status' => OrderStatus::PENDING->value,
                'discount_code' => $discountCode,
                'global_discount_amount' => $discountAmount,
                'discount_type' => $discountType,
                'sub_total' => $subTotal,
                'final_price' => $finalPrice
            ]);

            foreach ($carts as $cart) {
                // Create Order detail
                $orderDetail = OrderDetail::create([
                    'order_id' => $order->id,
                    'product_id' => $cart->product_id,
                    'quantity' => $cart->quantity,
                    'unit_price' => $cart->price_at_time, // Price after discount
                    'total_price' => bcmul($cart->quantity, $cart->price_at_time, 2),
                    'product_discount_amount' => bcsub($cart->product->price, $cart->price_at_time, 2)
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