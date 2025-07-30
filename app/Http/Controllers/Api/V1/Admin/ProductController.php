<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Helpers\AuthHelper;
use App\Helpers\FileHelper;
use App\Helpers\LoggerHelper;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Helpers\ResponseApiHelper;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Repository\ProductRepository;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductCollection;
use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Models\Category;
use App\Models\File;
use App\Repository\FileRepository;
use App\Services\FileService;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    private $productRepository, $fileRepository, $fileService;

    public function __construct(
        ProductRepository $productRepository,
        FileRepository $fileRepository,
        FileService $fileService
    ) {
        $this->productRepository = $productRepository;
        $this->fileRepository = $fileRepository;
        $this->fileService = $fileService;
    }

    public function index()
    {
        $products = $this->productRepository->get([
            'search' => [
                'name' => request()->name,
                'category_name' => request()->category_name
            ],
            'page' => 5
        ]);

        // Message for reponse
        $message = request()->name || request()->category_name
        ? 'Filtered products retrieved successfully.'
        : 'All products retrieved successfully.';

        return ResponseApiHelper::success($message, new ProductCollection($products));
    }

    public function store(ProductStoreRequest $request)
    {
        $productFiles = [];
        $user = AuthHelper::getUserFromToken($request->bearerToken());

        $category = Category::where('id', $request->category_id)->first();
        $slug = Str::slug($request->name);
        $productURL = config('app.url') . '/' . 'products' . '/' . $slug;

        $request->merge([
            'slug' => $slug,
            'product_url' => $productURL,
            'category_id' => $category->id,
            'user_id' => $user->id,
        ]);

        $productData = $request->only([
            'name', 'slug', 'price', 'description', 'stock', 'product_url', 'category_id', 'user_id'
        ]);
        
        try {
            DB::beginTransaction();

            $product = new Product($productData);
            $product = $this->productRepository->store($product);

            foreach ($request->file_ids as $fileId) {
                $productFiles[] = [
                    'product_id' => $product->id,
                    'file_id' => $fileId
                ];
            }

            $product->productFiles()->createMany($productFiles);

            DB::commit();

            // Log
            LoggerHelper::info('Product data successfully stored in the database.', [
                'action' => 'store',
                'model' =>  'Product',
                'data' => $productData,
                'file_data' => $productFiles
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();

            // Log
            LoggerHelper::error('Failed to store product data in database.', [
                'request_data' => $productData,
                'file_data' => $productFiles,
                'error' => $th->getMessage()
            ]);

            return ResponseApiHelper::error('An error occurred while processing store product data. Please try again later.');
        }

        return ResponseApiHelper::success('New Product successfully created.', new ProductResource($product));
    }

    public function show(Product $product)
    {
        return ResponseApiHelper::success('Product retrived successfully', new ProductResource($product));
    }

    public function update(ProductUpdateRequest $request, Product $product)
    {
        $productFiles = [];

        $category = Category::where('id', $request->category_id)->first();
        $slug = Str::slug($request->name);
        $productURL = config('app.url') . '/' . 'products' . '/' . $slug;

        $request->merge([
            'slug' => $slug,
            'product_url' => $productURL,
            'category_id' => $category->id
        ]);

        $productData = $request->only([
            'name', 'slug', 'price', 'description', 'stock', 'product_url', 'category_id'
        ]);

        try {
            DB::beginTransaction();

            $product = $product->fill($productData);
            $product = $this->productRepository->store($product);
            
            // Store old file_id
            $oldFileIds = $product->productFiles()->pluck('file_id')->toArray();

            // Get new file id from request
            $newFileIds = $request->file_ids;

            foreach ($newFileIds as $fileId) {
                $productFiles[] = [
                    'product_id' => $product->id,
                    'file_id' => $fileId
                ];
            }

            $product->productFiles()->delete();
            $product->productFiles()->createMany($productFiles);

            DB::commit();

            // Log
            LoggerHelper::info('Product data successfully updated in database.', [
                'action' => 'update',
                'model' => 'Product',
                'data' => $productData,
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();

            // Log
            LoggerHelper::error('Failed to update product data in database.', [
                'request_data' => $productData,
                'file_data' => $productFiles,
                'error' => $th->getMessage()
            ]);

            return ResponseApiHelper::error('An error occurred while processing update product data. Please try again later.');

        } finally {
            
            // Check file_ids
            $unusedFileIds = array_diff($oldFileIds, $newFileIds);
            $deleteFile = $this->fileService->deleteUnusedFiles($unusedFileIds);
            // Log unused file ids
            LoggerHelper::info('Unused file id has been deleted.', [
                'action' => 'delete',
                'model' => 'File',
                'delete_file_id' => $unusedFileIds
            ]);
        }

        return ResponseApiHelper::success('Product has been successfully updated.', new ProductResource($product));
    }

    public function destroy(Product $product)
    {
        try {
            DB::beginTransaction();

            // Check is product has file
            if ($product->productFiles()->exists()) {
                foreach ($product->productFiles as $productFile) {
                    
                    if ($productFile->file_id) {
                        $oldFilePath = $productFile->file->directory;
                    }

                    if (isset($oldFilePath)) {
                        Storage::delete($oldFilePath);
                    }

                    $productFileId = File::find($productFile->file->id);
                    
                    if ($productFileId) {
                        $deleteFileId = $productFileId->id;
                        $productFileId->delete();
                    }
                }
            }

            $product->delete();

            DB::commit();

            // Log
            LoggerHelper::info('Product data successfully deleted.', [
                'action' => 'delete',
                'model' => 'Product',
                'delete_product_id' => $product->id,
                'delete_product_file_id' => $deleteFileId
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();

            // Log
            LoggerHelper::error('Failed to delete product data.', [
                'data' => $product,
                'error' => $th->getMessage()
            ]);

            return ResponseApiHelper::error('An error occurred while processing delete product data. Please try again later.');
        }

        return ResponseApiHelper::success('Product has been successfully deleted.');
    }
}
