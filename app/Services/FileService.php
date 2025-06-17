<?php 

namespace App\Services;

use App\Models\File;
use Illuminate\Support\Facades\Storage;

class FileService
{
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