<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name', 'price', 'slug', 'stock', 'description', 'product_url', 
        'file_id', 'category_id'
    ];

    protected $casts = [
        'price' => 'string'
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

    public function productFiles(): HasMany
    {
        return $this->hasMany(ProductFile::class);
    }

    // Accessor
    public function getFileAttribute()
    {
        return $this->productFiles->first()?->file;
    }
}
