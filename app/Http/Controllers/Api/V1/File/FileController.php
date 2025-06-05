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

class FileController extends Controller
{
    private $fileRepository;

    public function __construct(FileRepository $fileRepository) {
        $this->fileRepository = $fileRepository;
    }

    public function upload(FileStoreRequest $request, File $file)
    {
        $dataFiles = [];
        $user = AuthHelper::getUserFromToken(request()->bearerToken());

        $directory = $user?->role->slug === 'admin' ? 'product/' : 'profile/';

        // // Check user only can upload 1 file
        if ((!$user || $user?->role->slug === 'user') && count($request->file('files')) > 1) {
            // Log
            LoggerHelper::error('Failed to upload profile', [
                'email' => $user?->email,
                'upload_at' => now()
            ]);

            return ResponseApiHelper::error('Failed to upload profile, please try again later.');
        }

        try {
            DB::beginTransaction();
            
            $files = $request->file('files');

            foreach ($files as $file) {
                FileHelper::uploadFile($file->get(), [
                    'file_name' => 'name',
                    'field_name' => 'directory',
                    'extension' => $file->getClientOriginalExtension(),
                    'directory' => $directory,
                    'file_url' => 'file_url',
                    'upload_at' => 'upload_at'
                ], $request);

                $fileData = $request->only(['name', 'directory', 'upload_at', 'file_url']);
                $fileUploaded = $this->fileRepository->store($fileData);

                $dataFiles[] = [
                    'id' => $fileUploaded->id,
                    'file_url' => $fileUploaded->file_url
                ];
            }

            DB::commit();

            // Log
            LoggerHelper::info('File data successfully uploaded.', [
                'action' => 'Store',
                'model' => 'file',
                'data' => $dataFiles
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();

            // Log
            LoggerHelper::error('Failed to upload file data.', [
                'error' => $th->getMessage()
            ]);

            return ResponseApiHelper::error('An error occurred while processing your request for product data. Please try again later.');
        }

        return ResponseApiHelper::success('New File has been successfully uploaded.', [
            'files' => $dataFiles
        ]);
    }
}
