<?php

namespace Database\Seeders;

use App\Models\ProductDiscount;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductDiscountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productDiscounts = [
            [
                'product_id' => 5,
                'discount_price' => 5000,
                'start_at' => now(),
                'end_at' => now()->addDays(3)
            ],
            [
                'product_id' => 4,
                'discount_price' => 10000,
                'start_at' => now(),
                'end_at' => now()->addDays(3)
            ],
            [
                'product_id' => 3,
                'discount_price' => 15000,
                'start_at' => now(),
                'end_at' => now()->addDays(3)
            ],
            [
                'product_id' => 2,
                'discount_price' => 25000,
                'start_at' => now(),
                'end_at' => now()->addDays(3)
            ],
            [
                'product_id' => 1,
                'discount_price' => 50000,
                'start_at' => now(),
                'end_at' => now()->addDays(3)
            ]
        ];

        foreach ($productDiscounts as $productDiscount) {
            ProductDiscount::create($productDiscount);
        }
    }
}
