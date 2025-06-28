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
                'name' => 'Shoes',
                'slug' =>  'shoes',
                'price' => 125000,
                'description' => 'shoes',
                'stock' => 100,
                'category_id' => 3,
                'product_url' => 'http://localhost:8000/product/shoes',
            ],
            [
                'user_id' => 1,
                'name' => 'Television',
                'slug' => 'television',
                'price' => 250000,
                'description' => 'television',
                'stock' => 100,
                'category_id' => 2,
                'product_url' => 'http://localhost:8000/product/television',
            ],
            [
                'user_id' => 1,
                'name' => 'T-Shirt',
                'slug' => 't-shirt',
                'price' => 75000,
                'description' => 't-shirt',
                'stock' => 100,
                'category_id' => 3,
                'product_url' => 'http://localhost:8000/product/t-shirt',
            ],
            [
                'user_id' => 1,
                'name' => 'Adventure Book',
                'slug' => 'adventure-book',
                'price' => 100000,
                'description' => 'adventure book',
                'stock' => 100,
                'category_id' => 5,
                'product_url' => 'http://localhost:8000/product/adventure-book',
            ],
            [
                'user_id' => 1,
                'name' => 'Glasses',
                'slug' => 'glasses',
                'price' => 50000,
                'description' => 'glasses',
                'stock' => 100,
                'category_id' => 3,
                'product_url' => 'http://localhost:8000/product/glasses',
            ],
            [
                'user_id' => 1,
                'name' => 'Hammer',
                'slug' => 'hammer',
                'price' => 89000,
                'description' => 'hammer',
                'stock' => 100,
                'category_id' => 4,
                'product_url' => 'http://localhost:8000/product/hammer',
            ],
            [
                'user_id' => 1,
                'name' => 'Basket Ball',
                'slug' => 'basket-ball',
                'price' => 123000,
                'description' => 'basket-ball',
                'stock' => 100,
                'category_id' => 1,
                'product_url' => 'http://localhost:8000/product/basket-ball',
            ],
            [
                'user_id' => 1,
                'name' => 'Foot Ball',
                'slug' => 'foot-ball',
                'price' => 99000,
                'description' => 'foot-ball',
                'stock' => 100,
                'category_id' => 1,
                'product_url' => 'http://localhost:8000/product/foot-ball',
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
