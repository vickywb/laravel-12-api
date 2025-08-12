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
            'order' => 'CASE WHEN EXISTS (
                SELECT 1 FROM product_discounts 
                WHERE product_discounts.product_id = products.id 
                AND start_at <= NOW() AND end_at >= NOW() AND is_active = 1) 
                THEN 0 ELSE 1 END',
            'order_desc' => 'created_at',
            'with' => 'activeDiscount',
            'page' => 8
        ]);

        return ResponseApiHelper::success('Successfully Retrived Data.', ProductResource::collection($newProducts));
    }
}
