<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [ 
        'user_id',
        'order_id',
        'invoice_number',
        'invoice_url',
        'payment_method',
        'total_price',
        'payment_status',
        'transaction_status',
        'fraud_status',
        'va_number',
        'bank',
        'midtrans_transaction_id',
        'paid_at'
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'total_price' => 'string',
        'payment_status' => PaymentStatus::class
    ];

    //Relatioship
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function transactionDetails(): HasMany
    {
        return $this->hasMany(TransactionDetail::class);
    }
}
