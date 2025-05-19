<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductFile extends Model
{
    protected $fillable = [
        'file_id', 'product_id'
    ];

    // Relationship
    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);    
    }
}
