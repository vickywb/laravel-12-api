<?php

namespace App\Http\Controllers\Api\V1\Admin;

use Carbon\Carbon;
use App\Models\Discount;
use Illuminate\Http\Request;
use App\Helpers\LoggerHelper;
use App\Helpers\ResponseApiHelper;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Repository\DiscountRepository;
use App\Http\Requests\DiscountStoreRequest;
use App\Http\Requests\DiscountUpdateRequest;

class DiscountController extends Controller
{
    private $discountRepository;

    public function __construct(DiscountRepository $discountRepository)
    {
        $this->discountRepository = $discountRepository;
    }

    public function index()
    {
        $discounts = $this->discountRepository->get([
            'search' => [
                'code' => request()->code,
                'product_name' => request()->product_name,
            ],
            'page' => 5,
        ]);

        return ResponseApiHelper::success('Discounts retrieved successfully.', $discounts);
    }

    public function store(DiscountStoreRequest $request)
    {
        $minOrderTotal = 0;

        if($request->start_at <= now()) {
            return ResponseApiHelper::error('Start date must be in the future.');
        }

        $request->merge([
            'start_at' => Carbon::parse($request->start_at ?: now())->format('Y-m-d H:i:s'),
            'end_at' => Carbon::parse($request->end_at ?: now()->addHours(24))->format('Y-m-d H:i:s'),
            'minimum_order_total' => $request->minimum_order_total ?: $minOrderTotal,
            'code' => strtoupper($request->code),
        ]);

        $data = $request->only('code', 'discount_type', 'discount_amount', 'start_at', 'end_at', 'minimum_order_total');

        try {
            DB::beginTransaction();

            $discount = new Discount($data);
            $discount = $this->discountRepository->store($discount);

            DB::commit();
            
            // Log
            LoggerHelper::info('Discount data successfully stored in database.', [
                'action' => 'store',
                'model' => 'Discount',
                'data' => $data
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();

            // Log
            LoggerHelper::error('Failed to store discount data in database.', [
                'request_data' => $data,
                'error' => $th->getMessage()
            ]);

            return ResponseApiHelper::error('An error occurred while processing store discount data. Please try again later.');
        }

        return ResponseApiHelper::success('Discount has been successfully created.');
    }

    public function update(DiscountUpdateRequest $request, Discount $discount)
    {
        $minOrderTotal = 0;

        if($request->start_at <= now()) {
            return ResponseApiHelper::error('Start date must be in the future.');
        }

        $request->merge([
            'start_at' => Carbon::parse($request->start_at ?: now())->format('Y-m-d H:i:s'),
            'end_at' => Carbon::parse($request->end_at ?: now()->addHours(24))->format('Y-m-d H:i:s'),
            'minimum_order_total' => $request->minimum_order_total ?: $minOrderTotal,
            'code' => strtoupper($request->code),
        ]);

        $data = $request->only('code', 'discount_type', 'discount_amount', 'start_at', 'end_at', 'minimum_order_total');
        
        try {
            DB::beginTransaction();

            $discount = $discount->fill($data);
            $discount = $this->discountRepository->store($discount);

            DB::commit();
            
            // Log
            LoggerHelper::info('Discount data successfully updated.', [
                'action' => 'update',
                'model' => 'Discount',
                'data' => $data
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();

            // Log
            LoggerHelper::error('Failed to update discount data in database.', [
                'request_data' => $data,
                'error' => $th->getMessage()
            ]);

            return ResponseApiHelper::error('An error occurred while processing update discount data. Please try again later.');
        }

        return ResponseApiHelper::success('Discount has been successfully updated.');
    }

    public function destroy(Discount $discount)
    {
        try {
            DB::beginTransaction();

            if ($discount->start_at <= now() && $discount->end_at >= now()) {
                return ResponseApiHelper::error("Can't Delete Discount: This Discount is currently active.", [
                    'error' => 'This discount is currently active and cannot be deleted.'
                ], 400);
            }

            $discount->delete();

            DB::commit();

            // Log
            LoggerHelper::info('Discount data successfully deleted from database.', [
                'action' => 'delete',
                'model' => 'Discount',
                'deleted_id' => $discount->id
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();

            // Log
            LoggerHelper::error('Failed to delete discount data from database.', [
                'data' => $discount,
                'error' => $th->getMessage()
            ]);

            return ResponseApiHelper::error('An error occurred while processing delete discount data. Please try again later.');
        }

        return ResponseApiHelper::success('Discount has been successfully deleted.');
    }
}
