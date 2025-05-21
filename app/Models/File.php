<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    protected $fillable = [
        'name', 'directory', 'upload_at'
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'upload_at' => 'datetime'
        ];
    }
}
