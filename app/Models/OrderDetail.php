<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDetail extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'quantity', 'unit_price', 
        'total_price', 'product_discount_amount'
    ];

    protected $casts = [
        'unit_price' => 'string',
        'total_price' => 'string',
        'product_discount_amount' => 'string'
    ];

    //Relationship
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}