<?php

namespace App\Repository;

use App\Models\Product;

class ProductRepository
{
    private $product;

    public function __construct(Product $product) {
        $this->product = $product;
    }
}