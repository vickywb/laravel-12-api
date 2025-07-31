<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Helpers\ResponseApiHelper;
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
        return ResponseApiHelper::success('Successfully Retrived Data.', ProductResource::collection(Product::latest()->take(8)->get()));
    }

    public function show(string $id)
    {
        //
    }
}
