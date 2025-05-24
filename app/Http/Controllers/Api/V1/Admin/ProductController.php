<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Helpers\ResponseApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Repository\ProductRepository;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    private $productRepository;

    public function __construct(ProductRepository $productRepository) {
        $this->productRepository = $productRepository;
    }

    public function index()
    {
        $products = $this->productRepository->get([
            'search' => [
                'name' => request()->name,
                'category_name' => request()->category_name
            ]
        ]);

        // Message for reponse
        $message = request()->name || request()->category_name
        ? 'Filtered products retrieved successfully.'
        : 'All products retrieved successfully.';

        return ResponseApiHelper::success($message, new ProductCollection($products));
    }

    public function store(Request $request)
    {
        //
    }

    public function show(Product $product)
    {
        return ResponseApiHelper::success('Product retrived successfully', new ProductResource($product));
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        //
    }
}
