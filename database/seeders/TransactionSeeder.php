<?php

namespace Database\Seeders;

use App\Models\Transaction;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $transactionData = [
            [
                'user_id' => 2,
                'order_id' => 1,
                'invoice_number' => 'INV-' . 1 . '-' . now(),
                'invoice_url' => 'https://example.com/invoice/INV-1-' . now(),
                'payment_method' => 'bank_transfer',
                'total_price' => '1260000',
                'payment_status' => 'pending',
                'transaction_status' => 'pending',
                'fraud_status' => 'pending',
                'va_number' => '1234567890',
                'bank' => 'null',
                'midtrans_transaction_id' => 'midtrans-123456',
                'paid_at' => now()
            ],
             [
                'user_id' => 2,
                'order_id' => 2,
                'invoice_number' => 'INV-' . 2 . '-' . now(),
                'invoice_url' => 'https://example.com/invoice/INV-2-' . now(),
                'payment_method' => 'bank_transfer',
                'total_price' => '3725000',
                'payment_status' => 'settlement',
                'transaction_status' => 'paid',
                'fraud_status' => 'accept',
                'va_number' => '1234567890',
                'bank' => 'bca',
                'midtrans_transaction_id' => 'midtrans-123456',
                'paid_at' => now()
            ],
        ];

        foreach ($transactionData as $data) {
            Transaction::create($data);
        }
    }
}
