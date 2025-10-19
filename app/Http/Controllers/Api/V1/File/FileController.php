<?php
namespace App\Http\Controllers\Api\V1\File;

use App\Helpers\AuthHelper;
use Illuminate\Http\Request;
use App\Helpers\LoggerHelper;
use App\Helpers\ResponseApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\FileStoreRequest;
use App\Services\UploadFileService;

class FileController extends Controller
{
    private $uploadFileService;

    public function __construct(UploadFileService $uploadFileService)
    {
        $this->uploadFileService = $uploadFileService;
    }

    /**
     * Upload file (profile atau product)
     */
    public function upload(FileStoreRequest $request)
    {
        $user = AuthHelper::getUserFromToken(request()->bearerToken());
        
        if (!$user) {
            return ResponseApiHelper::error('Unauthorized', [], 401);
        }

        $isAdmin = $user->role->slug === 'admin';
        $directory = $isAdmin ? 'product' : 'profile';
        
        // ✅ Validate: user only can uploaded 1 file
        if (!$isAdmin && count($request->file('files')) > 1) {
            LoggerHelper::warning('User tried to upload multiple files.', [
                'user_id' => $user->id,
                'files_count' => count($request->file('files'))
            ]);
            
            return ResponseApiHelper::error(
                'User hanya dapat upload 1 foto profil.', 
                ['max_files' => 1],
                422
            );
        }

        try {
            // ✅ For product, get product_id from request (optional)
            $productId = $isAdmin ? $request->input('product_id') : null;
            $userId = !$isAdmin ? $user->id : null;

            // ✅ Upload files
            $fileUpload = $this->uploadFileService->handleUploadFiles(
                $request, 
                $directory,
                $productId,
                $userId
            );

            return ResponseApiHelper::success(
                $isAdmin ? 'File produk berhasil diupload.' : 'Foto profil berhasil diupload.', 
                ['files' => $fileUpload]
            );
            
        } catch (\Throwable $th) {
            LoggerHelper::error('Failed to upload file.', [
                'user_id' => $user->id,
                'error' => $th->getMessage()
            ]);
            
            return ResponseApiHelper::error(
                'Gagal upload file, silakan coba lagi.', 
                ['error' => config('app.debug') ? $th->getMessage() : null],
                500
            );
        }
    }

    /**
     * Delete product file (for admin)
     * In frontend can deleted per file
     */
    public function deleteProductFile(Request $request, int $fileId)
    {
        $user = AuthHelper::getUserFromToken(request()->bearerToken());
        
        if (!$user || $user->role->slug !== 'admin') {
            return ResponseApiHelper::error('Unauthorized', [], 403);
        }

        try {
            $this->uploadFileService->deleteProductFile($fileId);

            return ResponseApiHelper::success('File produk berhasil dihapus.');
            
        } catch (\Throwable $th) {
            LoggerHelper::error('Failed to delete product file.', [
                'user_id' => $user->id,
                'file_id' => $fileId,
                'error' => $th->getMessage()
            ]);
            
            return ResponseApiHelper::error('Gagal menghapus file.', [], 500);
        }
    }
}