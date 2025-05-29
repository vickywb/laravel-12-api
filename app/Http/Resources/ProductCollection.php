<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        $products = $this->collection->transform(function ($product) use ($request) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'slug'  => $product->slug,
                'price' => $product->price,
                'stock' => $product->stock,
                'description' => $product->description,
                'product_url' => $product->product_url,
                'category' => new CategoryResource($product->category),
                'product_file' => new FileResource($product->file)
            ];
        });

        return $products->toArray();
    }
}