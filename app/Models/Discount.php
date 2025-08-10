<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    protected $fillable = [
        'code', 'discount_type', 'discount_amount',
        'usage_limit', 'is_active',
        'minimum_order_total', 'start_at', 'end_at'
    ];

    protected $casts = [
        'start_at' => 'string',
        'end_at' => 'string',
        'discount_amount' => 'string',
        'minimum_order_total' => 'string',
        'usage_limit' => 'integer',
    ];
}