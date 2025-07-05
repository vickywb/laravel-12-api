<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'order_status', 'total_price', 'sub_total', 'final_price',
        'discount_code', 'discount_type', 'global_discount_amount'
    ];

    protected $casts = [
        'sub_total' => 'string',
        'final_price' => 'string',
        'global_discount_amount' => 'string'
    ];

    // Relationship
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orderDetails(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }
}