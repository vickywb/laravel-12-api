<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    protected $fillable = [
        'name', 'price', 'slug', 'stock', 'description', 'product_url', 
        'category_id', 'user_id'
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Accessor
    public function getProductFileAttribute()
    {
        foreach ($this->productFiles as $productFile) {
            return $productFile->file;
        }
    }

    public function activeDiscount(): HasOne
    {
        return $this->hasOne(ProductDiscount::class)
            ->where('start_at', '<=', now())
            ->where('end_at', '>=', now());
    }
}
