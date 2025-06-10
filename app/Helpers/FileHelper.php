<?php

namespace App\Helpers;

use App\Models\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class FileHelper
{
    public static function uploadFile($fileContent, array $params = [], $request) 
    {    
        // Determine file extension
        $extension = ! empty($params['extension']) ? $params['extension'] : 'txt';

        // Generate Random Name
        $fileName = Str::random(16);

        // Define the path by which we will store the image
        $directoryName = 'file' . '/' . $fileName . '.' . $extension;
        
        // Generate DateTime
        $upload_at = now();

        if (isset($params['directory'])) {
            $directoryName = 'file' . '/' . $params['directory'] . $fileName . '.' . $extension;
        }

        // File Url
        $fileUrl = env('APP_URL') . 'storage/' . $directoryName;

        // Store File in the public storage
        Storage::put($directoryName,(string)$fileContent, 'public');

        // Merge the file name column to request
        $request->merge([
            $params['field_name'] => $directoryName,
            $params['file_name'] => $fileName,
            $params['file_url'] => $fileUrl,
            $params['upload_at'] => $upload_at
        ]);
    }

    public static function deleteUnusedFiles($unusedFileIds)
    {
        // Check if there are any unused file ids
        foreach ($unusedFileIds as $unusedFileId) {
            $file = File::find($unusedFileId);

            if ($file && Storage::exists($file->directory)) {
                Storage::delete($file->directory);
            }

            if ($file) {
                $file->delete();
            }
        }
    }
}