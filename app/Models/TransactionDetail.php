<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionDetail extends Model
{
    protected $fillable = [
        'transaction_id',
        'product_id',
        'quantity',
        'unit_price',
        'total_price',
        'product_name',
        'product_discount_amount'
    ];

    protected $casts = [
        'unit_price' => 'string',
        'total_price' => 'string',
        'product_discount_amount' => 'string'
    ];

    // Relationship
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
