<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductDiscountResource extends JsonResource
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
            'discount_value' => (string) $this->discount_value,
            'discount_type' => $this->discount_type,
            'start_at' => $this->start_at,
            'end_at' => $this->end_at
        ];
    }
}