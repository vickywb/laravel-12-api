<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Helpers\ResponseApiHelper;
use App\Http\Controllers\Controller;
use App\Repository\ProductRepository;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;

class ProductController extends Controller
{
    private $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function index()
    {
        $products = $this->productRepository->get([
            'with' => ['category'],
            'search' => [
                'name' => request()->name
            ],
            'page' => 10,
        ]);

        if ($products->isEmpty()) {
            return ResponseApiHelper::error('Product is not found.', [], 404);
        }

        return ResponseApiHelper::success('Products retrieved successfully.', new ProductCollection($products));
    }

    public function show(Product $product)
    {
        return ResponseApiHelper::success('Product retrieved successfully.', new ProductResource($product));
    }
}
