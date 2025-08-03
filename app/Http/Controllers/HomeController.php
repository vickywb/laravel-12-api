<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Helpers\ResponseApiHelper;
use App\Http\Resources\ProductCollection;
use App\Repository\ProductRepository;
use App\Http\Resources\ProductResource;

class HomeController extends Controller
{
    private $productRepository;

    public function __construct(ProductRepository $productRepository) {
        $this->productRepository = $productRepository;
    }

    public function index()
    {
        $newProducts = $this->productRepository->get([
            'order' => 'created_at desc',
            'page' => 8
        ]);

        $discountProducts = $this->productRepository->get([
            'whereHas' => 'activeDiscount',
            'with' => 'activeDiscount'
        ]);

        return ResponseApiHelper::success('Successfully Retrived Data.', [
            'new_products' => ProductResource::collection($newProducts),
            'discount_products' => ProductResource::collection($discountProducts)
        ]);
    }

    public function show(string $id)
    {
        //
    }
}
