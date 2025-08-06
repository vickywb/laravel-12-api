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
                'discount_value' => 10,
                'discount_type' => 'percentage',
                'start_at' => now(),
                'end_at' => now()->addDays(3)
            ],
            [
                'product_id' => 4,
                'discount_value' => 15000,
                'discount_type' => 'fixed',
                'start_at' => now(),
                'end_at' => now()->addDays(3)
            ],
            [
                'product_id' => 3,
                'discount_value' => 20,
                'discount_type' => 'percentage',
                'start_at' => now(),
                'end_at' => now()->addDays(3)
            ],
            [
                'product_id' => 2,
                'discount_value' => 15,
                'discount_type' => 'percentage',
                'start_at' => now(),
                'end_at' => now()->addDays(3)
            ],
            [
                'product_id' => 1,
                'discount_value' => 50000,
                'discount_type' => 'fixed',
                'start_at' => now(),
                'end_at' => now()->addDays(3)
            ]
        ];

        foreach ($productDiscounts as $productDiscount) {
            ProductDiscount::create($productDiscount);
        }
    }
}
