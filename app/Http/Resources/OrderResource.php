<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'order_status' => $this->order_status,
            'discount_code' => $this->discount_code ?? null,
            'discount_type' => $this->discount_type ?? null,
            'global_discount_amount' => $this->global_discount_amount ?? null,
            'sub_total' => $this->sub_total,
            'final_price' => $this->final_price,
            'created_at' => $this->created_at->format('d-m-Y H:i:s'),
            'update_at' => $this->updated_at->format('d-m-Y H:i:s'),
            'order_details' => OrderDetailResource::collection($this->orderDetails)
        ];
    }
}
