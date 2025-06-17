<?php

namespace App\Http\Controllers\Api\V1\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Helpers\LoggerHelper;
use App\Models\ProductDiscount;
use App\Helpers\ResponseApiHelper;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Repository\ProductDiscountRepository;
use App\Http\Requests\ProductDiscountStoreRequest;
use App\Http\Requests\ProductDiscountUpdateRequest;

class ProductDiscountController extends Controller
{
    private $productDiscountRepository;

    public function __construct(ProductDiscountRepository $productDiscountRepository)
    {
        $this->productDiscountRepository = $productDiscountRepository;
    }
    public function index()
    {
        $productDiscounts = $this->productDiscountRepository->get([
            'search' => [
                'product_name' => request()->product_name,
            ],
            'with' => ['product'],
            'page' => 5,
        ]);

        return ResponseApiHelper::success('Product discounts retrieved successfully.', $productDiscounts);
    }

    public function store(ProductDiscountStoreRequest $request)
    {
        $discountPrice = 0;

        if ($request->start_at <= now()) {
            return ResponseApiHelper::error('Start date must be in the future.');
        }

        $request->merge([
            'discount_price' => $request->discount_price ?: $discountPrice,
            'start_at' => Carbon::parse($request->start_at ?: now())->format('Y-m-d H:i:s'),
            'end_at' => Carbon::parse($request->end_at ?: now()->addHours(24))->format('Y-m-d H:i:s'),
        ]);

        $data = $request->only('product_id', 'discount_price', 'start_at', 'end_at');

        try {
            DB::beginTransaction();

            $productDiscount = new ProductDiscount($data);
            $productDiscount = $this->productDiscountRepository->store($productDiscount);

            DB::commit();

            // Log
            LoggerHelper::info('Product discount successfully stored in database.', [
                'action' => 'store',
                'model' => 'ProductDiscount',
                'data' => $data
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();

            // Log
            LoggerHelper::error('Failed to store product discount in database.', [
                'request_data' => $data,
                'error' => $th->getMessage()
            ]);

            return ResponseApiHelper::error('An error occurred while processing store product discount data. Please try again later.');
        }

        return ResponseApiHelper::success('Product discount successfully created.');
    }

    public function update(ProductDiscountUpdateRequest $request, ProductDiscount $productDiscount)
    {
        $discountPrice = 0;

        if ($request->start_at <= now()) {
            return ResponseApiHelper::error('Start date must be in the future.');
        }

        $request->merge([
            'discount_price' => $request->discount_price ?: $discountPrice,
            'start_at' => Carbon::parse($request->start_at ?: now())->format('Y-m-d H:i:s'),
            'end_at' => Carbon::parse($request->end_at ?: now()->addHours(24))->format('Y-m-d H:i:s')
        ]);

        $data = $request->only('product_id', 'discount_price', 'start_at', 'end_at');

        try {
            DB::beginTransaction();

            $productDiscount = $productDiscount->fill($data);
            $productDiscount = $this->productDiscountRepository->store($productDiscount);

            DB::commit();

            // Log
            LoggerHelper::info('Product discount successfully updated in database.', [
                'action' => 'update',
                'model' => 'ProductDiscount',
                'data' => $data
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();

            // Log
            LoggerHelper::error('Failed to update product discount in database.', [
                'request_data' => $data,
                'error' => $th->getMessage()
            ]);

            return ResponseApiHelper::error('An error occurred while processing update product discount data. Please try again later.');
        }

        return ResponseApiHelper::success('Product discount has been successfully updated.');
    }

    public function destroy(ProductDiscount $productDiscount)
    {
        try {
            DB::beginTransaction();

            // Check if the product discount exists
            if ($productDiscount->product()->exists()) {
                return ResponseApiHelper::error("Can't Delete Product Discount: This Product Discount has existing relationships with other entities.", [
                    'error' => 'This product discount is currently assigned to products and cannot be deleted.'
                ], 400);
            }

            $productDiscount->delete();

            DB::commit();

            // Log
            LoggerHelper::info('Product discount deleted successfully.', [
                'action' => 'delete',
                'model' => 'ProductDiscount',
                'deleted_id' => $productDiscount->id
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();

            // Log
            LoggerHelper::error('Failed to delete product discount from database.', [
                'data' => $productDiscount,
                'error' => $th->getMessage()
            ]);

            return ResponseApiHelper::error('Failed to delete product discount from database.');
        }

        return ResponseApiHelper::success('Product discount has been deleted successfully.');
    }
}
