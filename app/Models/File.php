<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class File extends Model
{
    protected $fillable = [
        'name', 'directory', 'upload_at', 'file_url'
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'upload_at' => 'datetime'
        ];
    }

    // Relationship
    public function userProfiles(): HasMany
    {
        return $this->hasMany(UserProfile::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function productFiles(): HasMany
    {
        return $this->hasMany(ProductFile::class);
    }
}
