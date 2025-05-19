<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $fillable = [
        'name', 'price', 'stock', 'description', 'url_product', 
        'file_id', 'category_id'
    ];

    // Relationship
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }
}
