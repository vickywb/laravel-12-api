<?php

namespace App\Helpers;

use App\Models\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileHelper
{
    public static function uploadFileToStorage(UploadedFile $file, string $directory): array
    {
        // Create new name using uniqid + extension
        $filename = uniqid() . '.' . $file->getClientOriginalExtension();

        // Store to storage
        $path = $file->storeAs($directory, $filename, 'public');

        $storagePath = $directory . '/' . $filename;

        $url = asset('storage/' . $path);
        
        // Return URL and Directory
        return [
            'directory' => $storagePath,
            'file_url' => $url
        ];
    }
}