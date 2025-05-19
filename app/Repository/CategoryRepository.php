<?php

namespace App\Repository;

use App\Models\Category;

class CategoryRepository
{
    private $category;

    public function __construct(Category $category) {
        $this->category = $category;
    }

    public function store(Category $category)
    {
        $category->save();

        return $category;
    }
}