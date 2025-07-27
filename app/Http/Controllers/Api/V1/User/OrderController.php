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
use App\Http\Resources\OrderResource;
use App\Http\Requests\OrderStoreRequest;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class OrderController extends Controller
{
    private $orderRepository, $orderService;

    use AuthorizesRequests;

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
        $user = AuthHelper::getUserFromToken(request()->bearerToken());

        $orders = $this->orderRepository->get([
            'user_id' => $user->id,
            'order' => 'created_at desc',
            'with' => ['orderDetails.product'],
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

        // Order Service
        $order = $this->orderService->createOrderFromCart($user->id);
    
        return ResponseApiHelper::success('Order has been created successfully.', new OrderResource($order), 201);
    }

    public function show(Order $order)
    {
        $user = AuthHelper::getUserFromToken(request()->bearerToken());
        auth()->loginUsingId($user->id); // convert to auth user from token
        
        try {
            $this->authorize('view', $order);
        } catch (AuthorizationException $e) {
            return ResponseApiHelper::error('You do not have permission to view this order.', [], 403);
        }
        
        return ResponseApiHelper::success('Order retrived successfully.', new OrderResource($order));
    }
}
