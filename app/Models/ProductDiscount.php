<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductDiscount extends Model
{
    protected $fillable = [
        'product_id',
        'discount_value',
        'discount_type',
        'is_active',
        'start_at',
        'end_at',
    ];

    protected $casts = [
        'start_at' => 'string',
        'end_at' => 'string',
    ];

    // Relationship
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
