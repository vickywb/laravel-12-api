<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TransactionDetail;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TransactionDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $transactionDetailData = [
            [
                'transaction_id' => 2,
                'product_id' => 1,
                'product_name' => 'Vans Oldskool',
                'quantity' => 3,
                'unit_price' => '700000',
                'total_price' => '2100000',
                'product_discount_amount' => '50000'
            ],
            [
                'transaction_id' => 2,
                'product_id' => 2,
                'product_name' => 'Television Samsung 55 inch',
                'quantity' => 1,
                'unit_price' => '3825000',
                'total_price' => '3725000',
                'product_discount_amount' => '0'
            ],
        ];

        foreach ($transactionDetailData as $data) {
            TransactionDetail::create($data);
        }
    }
}
