<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;

class File extends Model
{
    protected $fillable = [
        'name', 'directory', 'upload_at', 'file_url',
        'size', 'mime_type'
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'upload_at' => 'datetime',
            'size' => 'integer'
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

    // Accessor for readable format size
    protected function formattedSize(): Attribute
    {
        return Attribute::make(
            get: function () {
                $bytes = $this->size;
                $units = ['B', 'KB', 'MB', 'GB'];
                
                for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
                    $bytes /= 1024;
                }
                
                return round($bytes, 2) . ' ' . $units[$i];
            }
        );
    }

    // Accessor untuk check apakah image
    protected function isImage(): Attribute
    {
        return Attribute::make(
            get: fn() => str_starts_with($this->mime_type, 'image/')
        );
    }

    // Accessor untuk check apakah video
    protected function isVideo(): Attribute
    {
        return Attribute::make(
            get: fn() => str_starts_with($this->mime_type, 'video/')
        );
    }
}
