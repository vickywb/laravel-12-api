<?php

namespace App\Services;

use App\Helpers\FileHelper;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Helpers\LoggerHelper;
use App\Repository\FileRepository;
use Illuminate\Support\Facades\DB;

class UploadFileService
{
    protected $fileRepository;

    public function __construct(FileRepository $fileRepository) 
    {
        $this->fileRepository = $fileRepository;
    }

    public function handleUploadFiles(Request $request, string $directory): array
    {
        $dataFiles = [];
        
        DB::beginTransaction();
            
        try {

            $files = $request->file('files');

            foreach ($files as $file) {

                // Upload file to storage and get the path/url
                $fileUrl = FileHelper::uploadFileToStorage($file, $directory);

                $name = now()->format('dmY-His') . '-' . $file->getClientOriginalName();

               // Store request data in variable filedata
                $fileData = [
                    'name' => $name,
                    'directory' => $fileUrl['directory'],
                    'file_url' => $fileUrl['file_url'],
                    'upload_at' => now(),
                ];

                // Store filedata to database
                $fileUploaded = $this->fileRepository->store($fileData);

                $dataFiles[] = [
                    'id' => $fileUploaded->id,
                    'file_url' => $fileUploaded->file_url,
                ];
            }

            DB::commit();
        
            // Log
            LoggerHelper::info('File data successfully uploaded.', [
                'action' => 'Store',
                'model' => 'file',
                'data' => $dataFiles
            ]);

            return $dataFiles;

        } catch (\Throwable $th) {
            DB::rollBack();

            // Log
            LoggerHelper::error('Failed to upload file data.', [
                'error' => $th->getMessage()
            ]);

            throw $th; // Rethrow the exception to be handled by the controller
        }
    }
}