<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'user_id' => 1,
                'name' => 'Spatu',
                'slug' =>  'spatu',
                'price' => 10000,
                'description' => 'spatu',
                'stock' => 10,
                'category_id' => 1,
                'product_url' => 'http://localhost:8000/product/spatu',
            ],
            [
                'user_id' => 1,
                'name' => 'Television',
                'slug' => 'television',
                'price' => 10000,
                'description' => 'television',
                'stock' => 10,
                'category_id' => 2,
                'product_url' => 'http://localhost:8000/product/television',
            ],
            [
                'user_id' => 1,
                'name' => 'T-Shirt',
                'slug' => 't-shirt',
                'price' => 10000,
                'description' => 't-shirt',
                'stock' => 10,
                'category_id' => 3,
                'product_url' => 'http://localhost:8000/product/t-shirt',
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
