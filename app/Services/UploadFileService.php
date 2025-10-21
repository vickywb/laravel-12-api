<?php

namespace App\Services;

use Exception;
use App\Models\File;
use App\Models\UserProfile;
use App\Models\ProductFile;
use App\Helpers\FileHelper;
use Illuminate\Http\Request;
use App\Helpers\LoggerHelper;
use App\Exceptions\ApiException;
use App\Repository\FileRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UploadFileService
{
    protected $fileRepository;

    public function __construct(FileRepository $fileRepository) 
    {
        $this->fileRepository = $fileRepository;
    }

    /**
     * Handle upload files untuk user profile atau product
     * 
     * @param Request $request
     * @param string $directory ('profile' atau 'product')
     * @param int|null $productId
     * @param int|null $userId
     * @return array
     */
    public function handleUploadFiles(Request $request, string $directory, ?int $productId = null, ?int $userId = null): array 
    {
        $dataFiles = [];
            
        try {
            DB::beginTransaction();

            // ✅ Delete old file jika user profile
            if ($userId && $directory === 'profile') {
                $this->deleteOldUserProfile($userId);
            }

            $files = $request->file('files');

            foreach ($files as $file) {
                // Upload file ke storage
                $fileUrl = FileHelper::uploadFileToStorage($file, $directory);
                $name = now()->format('dmY-His') . '-' . $file->getClientOriginalName();

                // Prepare data untuk disimpan ke database
                $fileData = [
                    'name' => $name,
                    'directory' => $fileUrl['directory'],
                    'file_url' => $fileUrl['file_url'],
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'upload_at' => now(),
                ];

                // Store ke table files
                $fileUploaded = $this->fileRepository->store($fileData);

                // Create relasi berdasarkan context
                if ($productId) {
                    ProductFile::create([
                        'product_id' => $productId,
                        'file_id' => $fileUploaded->id
                    ]);
                }

                if ($userId) {
                    UserProfile::create([
                        'user_id' => $userId,
                        'file_id' => $fileUploaded->id
                    ]);
                }

                // Prepare response data
                $dataFiles[] = [
                    'id' => $fileUploaded->id,
                    'name' => $fileUploaded->name,
                    'file_url' => $fileUploaded->file_url,
                    'size' => $fileUploaded->size,
                    'formatted_size' => $fileUploaded->formatted_size,
                    'mime_type' => $fileUploaded->mime_type,
                    'is_image' => $fileUploaded->is_image,
                    'uploaded_at' => $fileUploaded->upload_at
                ];
            }

            DB::commit();
        
            // Log success
            LoggerHelper::info('File data successfully uploaded.', [
                'action' => 'Store',
                'model' => 'file',
                'product_id' => $productId,
                'user_id' => $userId,
                'files_count' => count($dataFiles)
            ]);

            return $dataFiles;

        } catch (\Throwable $th) {
            DB::rollBack();

            // Log error
            LoggerHelper::error('Failed to upload file data.', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ]);

            throw new ApiException('Failed to upload file data: ' . $th->getMessage());
        }
    }

    /**
     * Delete old user profile photo
     * Menghapus foto lama user sebelum upload foto baru
     * 
     * @param int $userId
     * @return void
     */
    private function deleteOldUserProfile(int $userId): void
    {
        try {
            // Cari profile lama dengan eager loading
            $oldProfile = UserProfile::where('user_id', $userId)
                ->with('file')
                ->first();
            
            if (!$oldProfile || !$oldProfile->file) {
                LoggerHelper::info('No old profile found to delete.', [
                    'user_id' => $userId
                ]);
                return;
            }

            $oldFile = $oldProfile->file;
            $oldFilePath = $oldFile->directory;

            // Delete file dari storage
            if (Storage::disk('public')->exists($oldFilePath)) {
                Storage::disk('public')->delete($oldFilePath);
                
                LoggerHelper::info('Old profile photo deleted from storage.', [
                    'user_id' => $userId,
                    'file_path' => $oldFilePath
                ]);
            }

            // Delete dari database
            $oldProfile->delete(); // Delete relasi di user_profiles
            $oldFile->delete(); // Delete record di files

            LoggerHelper::info('Old profile successfully deleted.', [
                'user_id' => $userId,
                'file_id' => $oldFile->id
            ]);

        } catch (\Throwable $th) {
            // Log warning tapi jangan throw exception
            // Karena kalau delete gagal, masih bisa lanjut upload
            LoggerHelper::warning('Failed to delete old profile, continuing with upload.', [
                'user_id' => $userId,
                'error' => $th->getMessage()
            ]);
        }
    }

    /**
     * Delete specific product file by ID
     * Method ini untuk nanti ketika admin mau delete per file
     * 
     * @param int $fileId
     * @return bool
     */
    public function deleteProductFile(int $fileId): bool
    {
        try {
            DB::beginTransaction();

            // Find file record
            $file = $this->fileRepository->findById($fileId);
            
            if (!$file) {
                throw new Exception('File not found');
            }

            // Delete dari storage
            if (Storage::disk('public')->exists($file->directory)) {
                Storage::disk('public')->delete($file->directory);
            }

            // Delete relasi di product_files (cascade)
            ProductFile::where('file_id', $fileId)->delete();

            // Delete record di files
            $this->fileRepository->delete($fileId);

            DB::commit();

            LoggerHelper::info('Product file successfully deleted.', [
                'file_id' => $fileId
            ]);

            return true;

        } catch (\Throwable $th) {
            DB::rollBack();

            LoggerHelper::error('Failed to delete product file.', [
                'file_id' => $fileId,
                'error' => $th->getMessage()
            ]);

            throw new Exception('Failed to delete product file: ' . $th->getMessage());
        }
    }
}