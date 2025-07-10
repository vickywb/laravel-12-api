<?php

namespace App\Http\Controllers\Api\V1\Midtrans;

use App\Enums\PaymentStatus;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Helpers\LoggerHelper;
use App\Helpers\ResponseApiHelper;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class MidtransWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();
        // Log
        LoggerHelper::info('Midtrans Webhook Received', [
            'payload' => $payload,
        ]);

        // Explode order_id to get invoice number
        $orderIdRaw = $payload['order_id'] ?? null;
        $orderId = explode('-', $orderIdRaw);
        $invoiceNumber = implode('-', array_slice($orderId, 0, 3));

        // Get va_number and bank from payload
        $vaNumbers = $payload['va_numbers'] ?? [];
        $vaNumber = null;
        $bank = null;
        if (!empty($vaNumbers) && is_array($vaNumbers)) {
            $vaNumber = $vaNumbers[0]['va_number'] ?? null;
            $bank = $vaNumbers[0]['bank'] ?? null;
        }

        $transactionStatus = $payload['transaction_status'] ?? null;
        $fraudStatus = $payload['fraud_status'] ?? null;
        $paymentType = $payload['payment_type'] ?? null;
        $transactionId = $payload['transaction_id'] ?? null;
        $vaNumber = $vaNumber;
        $bank = $bank;

        
        if (!$orderId || !$transactionStatus) {
            // Log
            LoggerHelper::error('Midtrans Webhook Missing order_id or transaction_status');
            return ResponseApiHelper::error('Invalid webhook data', [], 400);
        }

        // Find Transaction id by invoice number
        $transaction = Transaction::where('invoice_number', $invoiceNumber)->first();

        if (!$transaction) {
            // Log
            LoggerHelper::warning('Midtrans Webhook Transaction not found.');
            return ResponseApiHelper::error('Transaction not found', [], 404);
        }

        $statusEnum = PaymentStatus::fromMidtrans($transactionStatus);

        try {
            DB::beginTransaction();

            $transaction->update([
                'transaction_status' => $transactionStatus,
                'fraud_status' => $fraudStatus,
                'payment_method' => $paymentType,
                'midtrans_transaction_id' => $transactionId,
                'va_number' => $vaNumber,
                'bank' => $bank,
                'payment_status' => $statusEnum,
                'paid_at' => $statusEnum->isPaid() ? now() : null,
            ]);

            DB::commit();
            // Log
            LoggerHelper::info('Midtrans Webhook Transaction updated.', [
                'transaction_id' => $transaction->id,
                'status' => $transaction->payment_status,
                'invoice_number' => $transaction->invoice_number,
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();
            // Log
            LoggerHelper::error('Midtrans Webhook Failed to update transaction', [
                'error' => $th->getMessage()
            ]);

            return ResponseApiHelper::error('Failed to update transaction', [], 500);
        }

        return ResponseApiHelper::success('Transaction successfully updated.', [
            'transaction' => $transaction
        ]);
    }
}
