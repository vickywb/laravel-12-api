<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            'price_at_time' => (string) $this->price_at_time,
            'product' => [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'price' => (string) $this->product->price,
                'product_file' => new FileResource($this->product->productFile ?? null),
                'product_discount' => new ProductDiscountResource($this->product->activeDiscount ?? null)
            ]
        ];
    }
}
