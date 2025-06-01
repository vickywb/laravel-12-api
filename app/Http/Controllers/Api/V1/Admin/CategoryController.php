<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Helpers\LoggerHelper;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Helpers\ResponseApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryCollection;
use App\Http\Requests\CategoryStoreRequest;
use App\Http\Requests\CategoryUpdateRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Repository\CategoryRepository;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    private $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository) {
        $this->categoryRepository = $categoryRepository;
    }

    public function index()
    {
        $categories = $this->categoryRepository->get([
            'search' => [
                'name' => request()->name
            ],
            'page' => 5
        ]);

        $message = request()->name
        ? 'Filtered category retrieved successfully.'
        : 'All categories retrieved successfully.';

        return ResponseApiHelper::success($message, new CategoryCollection($categories));
    }

    public function store(CategoryStoreRequest $request)
    {
        $request->merge([
            'slug' => Str::slug($request->name)
        ]);

        $data = $request->only([
            'name', 'slug'
        ]);

        try {
            DB::beginTransaction();

            $category = new Category($data);
            $category = $this->categoryRepository->store($category);

            DB::commit();

            // Log
            LoggerHelper::info('Category data successfully stored in the database.', [
                'action' => 'store',
                'model' => 'Category',
                'data' => $category
            ]);


        } catch (\Throwable $th) {
            DB::rollBack();

            // Log
            LoggerHelper::error('Failed to store data in the database', [
                'request_data' => $data,
                'error' => $th->getMessage()
            ]);

            return ResponseApiHelper::error('An error occurred while processing store category data. Please try again later.');
        }

        return ResponseApiHelper::success('New Category successfully created.', [
            'category' => [
                'id' => $category->id,
                'slug' => $category->slug,
            ]
        ]);
    }

    public function show(Category $category)
    {
        return ResponseApiHelper::success('Category retrived successfully.', new CategoryResource($category));
    }

    public function update(CategoryUpdateRequest $request, Category $category)
    {
        $request->merge([
            'slug' => Str::slug($request->name)
        ]);

        $data = $request->only([
            'name', 'slug'
        ]);

        try {
            DB::beginTransaction();

            $category = $category->fill($data);
            $category = $this->categoryRepository->store($category);

            DB::commit();

            // Log
            LoggerHelper::info('Category data successfully updated.', [
                'action' => 'update',
                'model' => 'Category',
                'data' => $category
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();

            // Log
            LoggerHelper::error('Failed to update category data in database.', [
                'request_data' => $data,
                'error' => $th->getMessage()
            ]);

            return ResponseApiHelper::error('An error occurred while proccess update category data. Please try again later.');
        }

        return ResponseApiHelper::success('Category has been successfully updated.', [
            'category' => [
                'id' => $category->id,
                'slug' => $category->slug
            ]
        ]);
    }

    public function destroy(Category $category)
    {
        // Prevent deletion if the category has existing relationships with products
        if ($category->products()->exists()) {
            return ResponseApiHelper::error("Can't Delete Category: This Category has existing relationship with other entities.", [
                'error' => 'This category is currently assigned to products and cannot be deleted.'
            ], 409);
        }

        try {
            DB::beginTransaction();
            
            $category->delete();

            DB::commit();

            // Log
            LoggerHelper::info('Category data successfully deleted from database.', [
                'action' => 'delete',
                'model' => 'Category',
                'delete_id' => $category->id 
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();

            // Log
            LoggerHelper::error('Failed to delete category data.', [
                'data' => $category,
                'error' => $th->getMessage()
            ]);

            return ResponseApiHelper::error('An error occurred while proccessing delete category data. Please try again later.');
        }

        return ResponseApiHelper::success('Category data has been successfully deleted.');
    }
}
