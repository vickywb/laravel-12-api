<?php

namespace App\Http\Controllers\Api\V1\File;

use App\Models\File;
use App\Helpers\AuthHelper;
use App\Helpers\FileHelper;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Helpers\LoggerHelper;
use App\Helpers\ResponseApiHelper;
use App\Repository\FileRepository;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\FileStoreRequest;
use App\Services\UploadFileService;

class FileController extends Controller
{
    private $uploadFileService;

    public function __construct(
        UploadFileService $uploadFileService
) {
        $this->uploadFileService = $uploadFileService;
    }

    public function upload(FileStoreRequest $request, File $file)
    {
        $user = AuthHelper::getUserFromToken(request()->bearerToken());

        $directory = $user?->role->slug === 'admin' ? 'product' : 'profile';

        // // Check user only can upload 1 file
        if ((!$user || $user?->role->slug === 'user') && count($request->file('files')) > 1) {
            // Log
            LoggerHelper::error('Failed to upload profile, please upload only 1 file.', [
                'email' => $user?->email,
                'upload_at' => now()
            ]);

            return ResponseApiHelper::error('Failed to upload profile, please try again later.');
        }

        try {
            
            // Upload File Service
            $fileUpload = $this->uploadFileService->handleUploadFiles($request, $directory);

        } catch (\Throwable $th) {
            LoggerHelper::error('Failed to upload file.', [
                'email' => $user?->email,
                'upload_at' => now(),
                'error' => $th->getMessage()
            ]);

            return ResponseApiHelper::error('Failed to upload file, please try again later.');
        }

        return ResponseApiHelper::success('New File has been successfully uploaded.', [
            'files' => $fileUpload
        ]);
    }
}
