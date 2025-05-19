<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    protected $fillable = [
        'name', 'directory', 'upload_at'
    ];

    protected $casts = [
        'upload_at' => 'datetime'
    ];
}
