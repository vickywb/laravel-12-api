<?php

namespace App\Http\Controllers\Api\V1\User;

use Midtrans\Snap;
use Midtrans\Config;
use App\Models\Order;
use App\Helpers\AuthHelper;
use App\Models\Transaction;
use App\Enums\PaymentStatus;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Helpers\LoggerHelper;
use App\Models\TransactionDetail;
use App\Services\MidtransService;
use App\Helpers\ResponseApiHelper;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Repository\TransactionRepository;
use App\Http\Resources\TransactionResource;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TransactionController extends Controller
{
    private $midtrans, $transactionRepository;
    use AuthorizesRequests;

    public function __construct(
        MidtransService $midtrans,
        TransactionRepository $transactionRepository
    ) {
        $this->midtrans = $midtrans;
        $this->transactionRepository = $transactionRepository;
    }

    public function index()
    {
        $user = AuthHelper::getUserFromToken(request()->bearerToken());

        $transactions = $this->transactionRepository->get([
            'orders' => 'created_at DESC',
            'with' => ['transactionDetails.product', 'user.userProfile'],
            'user_id' => $user->id,
            'search' => [
                'status' => request('status'),
                'invoice_number' => request('invoice_number'),
                'bank' => request('bank'),
                'date_from' => request('date_from'),
                'date_to' => request('date_to'),
                'total_min' => request('total_min'),
                'total_max' => request('total_max')
            ],
            'page' => 10
        ]);

        return ResponseApiHelper::success('Transaction data retrieved successfully.', TransactionResource::collection($transactions));
    }

    public function store(Request $request, Order $order)
    {
        $user = AuthHelper::getUserFromToken($request->bearerToken());
        $order = Order::with(['orderDetails.product', 'user.userProfile'])
            ->where('user_id', $user->id)
            ->where('order_status', 'pending')
            ->findOrFail($order->id);
        
        if ($order->final_price <= 0) {
            throw new ApiException('Invalid final price.');
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
                'transaction_status' => PaymentStatus::UNPAID->value
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

    public function show(Transaction $transaction)
    {
        $user = AuthHelper::getUserFromToken(request()->bearerToken());
        auth()->loginUsingId($user->id); // convert to auth user from token

        $transaction->load('transactionDetails.product');
        
        try {
            $this->authorize('view', $transaction);
        } catch (AuthorizationException $e) {
            return ResponseApiHelper::error('You do not have permission to view this transaction.', [], 403);
        }
        
        return ResponseApiHelper::success('Transaction data retrieved successfully.', new TransactionResource($transaction));
    }
}
