<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Enums\PaymentStatus;
use Midtrans\Snap;
use Midtrans\Config;
use App\Models\Order;
use App\Helpers\AuthHelper;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Helpers\LoggerHelper;
use App\Models\TransactionDetail;
use App\Helpers\ResponseApiHelper;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Services\MidtransService;

class TransactionController extends Controller
{
    private $midtrans;

    public function __construct(MidtransService $midtrans) {
        $this->midtrans = $midtrans;
    }

    public function index()
    {
        //
    }

    public function store(Request $request, Order $order)
    {
        $user = AuthHelper::getUserFromToken($request->bearerToken());
        $order = Order::with(['orderDetails.product', 'user.userProfile'])
            ->where('user_id', $user->id)
            ->where('order_status', 'pending')
            ->findOrFail($order->id);
        
        if ($order->final_price <= 0) {
            throw new \Exception('Invalid final price.');
        }

        $totalPrice = $order->final_price;
        $invoiceNumber = 'INV-' . $order->id . '-' . now()->format('YmdHis');
        $orderId = $invoiceNumber . '-' . uniqid();

        try {
            DB::beginTransaction();

            $transaction = Transaction::create([
                'order_id' => $order->id,
                'user_id' => $user->id,
                'invoice_number' => $invoiceNumber,
                'payment_method' => 'midtrans',
                'total_price' => $totalPrice,
                'payment_status' => PaymentStatus::UNPAID
            ]);

            foreach ($order->orderDetails as $transactionDetail) {
                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $transactionDetail->product_id,
                    'product_name' => $transactionDetail->product->name,
                    'quantity' => $transactionDetail->quantity,
                    'unit_price' => $transactionDetail->unit_price,
                    'total_price' => $transactionDetail->total_price,
                    'product_discount_amount' => $transactionDetail->product_discount_amount,
                ]);
            }

            // Prepare item detail for midtrans data
            $itemDetails = [];
            $totalBeforeDiscount = 0;

            foreach ($order->orderDetails as $item) {
                if (!$item->product) {
                    continue;
                }

                $itemDetails[] = [
                    'id'       => 'SKU-' . $item->product_id,
                    'price'    => (int) $item->unit_price,
                    'quantity' => $item->quantity,
                    'name'     => $item->product->name,
                ];

                $totalBeforeDiscount += $item->unit_price * $item->quantity;
            }

            // add discount global (if exists)
            $discountAmount = $totalBeforeDiscount - $order->final_price;
            if ($discountAmount > 0) {
                $itemDetails[] = [
                    'id'       => $item->order->discount_code ?? 'GLB-DISCOUNT',
                    'price'    => -(int) $discountAmount,
                    'quantity' => 1,
                    'name'     => 'Global Discount',
                ];
            }

            $payload = [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => (int) $order->final_price,
                ],
                'customer_details' => [
                    'first_name' => $order->user->name,
                    'email' => $order->user->email,
                ],
                'item_details' => $itemDetails,
                'enabled_payments' => ['gopay', 'bank_transfer', 'shopee_pay', 'qris', 'ewallet'],
            ];

            $snapToken = $this->midtrans->createSnapTransaction($payload);
            $invoiceUrl = $snapToken['redirect_url'] ?? null;

            $transaction->update([
                'invoice_url' => $invoiceUrl
            ]);

            DB::commit();
            // Log
            LoggerHelper::info('Transaction data stored successfully.', [
                'token' => $snapToken
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();
            // Log
            LoggerHelper::error('Failed to store transaction data.', [
                'error' => $th->getMessage(),
                'payload' => $payload ?? null,
            ]);

            return ResponseApiHelper::error('An error occurred while proccess store transaction data. Please try again later.');
        }

        return ResponseApiHelper::success('Transaction data stored successfully.', [
            'transaction' => $transaction,
            'payload' => $payload,
            'invoice_url' => $invoiceUrl,
            'snap_token' => $snapToken['token'] ?? null
        ]);
    }
}
