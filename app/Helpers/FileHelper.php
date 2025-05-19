<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileHelper
{
    public function __construct($file, array $params = [], $request) 
    {    
        // Determine file extension
        $extension = ! empty($params['extension']) ? $params['extension'] : 'txt';

        // Generate Random Name
        $fileName = Str::random(16);

        // Generate DateTime
        $upload_at = now();

        // Define the path by which we will store the image
        $directoryName = 'file' . '/' . $fileName . '.' . $extension;

        if (isset($params['directory'])) {
            $directoryName = 'file' . '/' . $params['directory'] . $fileName . '.' . $extension;
        }

        // Store File in the public storage
        Storage::put($directoryName,file_get_contents($file), 'public');

        // Merge the file name column to request
        $request->merge([
            $params['field_name'] => $directoryName,
            $params['file_name'] => $fileName,
            $params['upload_at'] => $upload_at
        ]);
    }
}