<?php

namespace Database\Seeders;

use App\Models\OrderDetail;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orderDetails = [
            [
                'order_id' => 1,
                'product_id' => 1,
                'quantity' => 3,
                'unit_price' => '700000',
                'total_price' => '2100000',
                'product_discount_amount' => '50000'
            ],
            [
                'order_id' => 2,
                'product_id' => 2,
                'quantity' => 1,
                'unit_price' => '3825000',
                'total_price' => '3725000',
                'product_discount_amount' => '0'
            ],
        ];

        foreach ($orderDetails as $detail) {
            OrderDetail::create($detail);
        }
    }
}
