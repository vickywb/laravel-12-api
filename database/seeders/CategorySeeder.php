<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Sport',
                'slug' => 'sport'
            ],
            [
                'name' => 'Electronic',
                'slug' => 'electronic'
            ],
            [
                'name' => 'Lifestyle',
                'slug' => 'lifestyle'
            ],
            [
                'name' => 'Material',
                'slug' => 'material'
            ],
            [
                'name' => 'Book',
                'slug' => 'book'
            ]
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
