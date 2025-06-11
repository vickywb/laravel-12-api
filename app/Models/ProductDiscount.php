<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductDiscount extends Model
{
    protected $fillable = [
        'product_id',
        'discount_price',
        'start_at',
        'end_at',
    ];

    protected $casts = [
        'start_at' => 'string',
        'end_at' => 'string',
    ];

    // Relationship
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
