<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Models\Order;
use App\Helpers\AuthHelper;
use Illuminate\Http\Request;
use App\Services\OrderService;
use App\Helpers\ResponseApiHelper;
use Illuminate\Support\Facades\DB;
use App\Repository\OrderRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\OrderStoreRequest;
use App\Http\Resources\OrderResource;

class OrderController extends Controller
{
    private $orderRepository, $orderService;

    public function __construct(
        OrderRepository $orderRepository,
        OrderService $orderService
    ) 
    {
        $this->orderRepository = $orderRepository;
        $this->orderService = $orderService;
    }
    
    public function index()
    {
        $orders = $this->orderRepository->get([
            'order' => 'created_at desc',
            'search' => [
                'status' => request()->order_status
            ],
            'page' => 5
        ]);

        return ResponseApiHelper::success('Order retrived successfully.', OrderResource::collection($orders));
    }

    public function store(OrderStoreRequest $request)
    {
        $user = AuthHelper::getUserFromToken($request->bearerToken());

        try {

            // Order Serivce
            $order = $this->orderService->createOrderFromCart($user->id);
        
        } catch (\Throwable $th) {

            return ResponseApiHelper::error('An error occurred while proccess store order data. Please try again later.');
        }

        return ResponseApiHelper::success('Order has been created successfully.', new OrderResource($order));
    }

    public function show(Order $order)
    {
        return ResponseApiHelper::success('Order retrived successfully.', new OrderResource($order));
    }

    public function update(Request $request, string $id)
    {
        //
    }
    public function destroy(string $id)
    {
        //
    }
}
