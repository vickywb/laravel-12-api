<?php

namespace Database\Seeders;

use App\Models\Discount;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DiscountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $globalDiscounts = [
            [
                'code' => 'DISC10',
                'discount_type' => 'percentage',
                'discount_amount' => 10,
                'minimum_order_total' => 50000,
                'start_at' => now(),
                'end_at' => now()->addDay(3)
            ],
            [ 
                'code' => 'DISC20',
                'discount_type' => 'percentage',
                'discount_amount' => 20,
                'minimum_order_total' => 100000,
                'start_at' => now(),
                'end_at' => now()->addDay(3)
            ],
            [ 
                'code' => 'DISC30',
                'discount_type' => 'percentage',
                'discount_amount' => 30,
                'minimum_order_total' => 200000,
                'start_at' => now(),
                'end_at' => now()->addDay(3)
            ],
            [ 
                'code' => '202510k',
                'discount_type' => 'fixed',
                'discount_amount' => 30,
                'minimum_order_total' => 0,
                'start_at' => now(),
                'end_at' => now()->addDay(3)
            ],
            [ 
                'code' => '202525k',
                'discount_type' => 'fixed',
                'discount_amount' => 30,
                'minimum_order_total' => 20000,
                'start_at' => now(),
                'end_at' => now()->addDay(3)
            ],
            [ 
                'code' => '202550k',
                'discount_type' => 'fixed',
                'discount_amount' => 30,
                'minimum_order_total' => 200000,
                'start_at' => now(),
                'end_at' => now()->addDay(3)
            ]
        ];

        foreach ($globalDiscounts as $globalDiscount) {
            Discount::create($globalDiscount);
        }
    }
}
