<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'order_status', 'total_price'
    ];

    protected $casts = [
        'total_price' => 'string'
    ];

    // Relationship
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}