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
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('start_at')
                    ->orWhere('start_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('end_at')
                    ->orWhere('end_at', '>=', now());
            });
    }

    public function getFinalPriceAttribute()
    {
        $finalPrice = $this->price;

        // Check if discount is active
        if ($this->activeDiscount) {
            if ($this->activeDiscount->discount_type === 'percentage') {
                $finalPrice = bcsub($finalPrice, bcmul($this->price, $this->activeDiscount->discount_value / 100));
            } elseif ($this->activeDiscount->discount_type === 'fixed') {
                $finalPrice = bcsub($finalPrice, $this->activeDiscount->discount_value);
            }
        }

        return max($finalPrice, 0); // Ensure final price is not negative
    }
}
