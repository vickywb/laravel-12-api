<?php

namespace Database\Seeders;

use App\Models\Order;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orderData = [
            [
                'user_id' => 2,
                'order_status' => 'pending',
                'sub_total' => '2100000',
                'final_price' => '1260000',
                'discount_code' => 'DISC40',
                'discount_type' => 'percentage',
                'global_discount_amount' => '840000'
            ],
            [
                'user_id' => 2,
                'order_status' => 'paid',
                'sub_total' => '3825000',
                'final_price' => '3725000',
                'discount_code' => '2025100k',
                'discount_type' => 'fixed',
                'global_discount_amount' => '100000'
            ]
        ];

        foreach ($orderData as $data) {
            Order::create($data);
        }
    }
}
