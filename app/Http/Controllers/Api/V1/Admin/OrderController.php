<?php

namespace App\Http\Controllers\Api\V1\Admin;

use Illuminate\Http\Request;
use App\Helpers\ResponseApiHelper;
use App\Repository\OrderRepository;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderCollection;
use App\Models\Order;

class OrderController extends Controller
{
    private $orderRepository;

    public function __construct(OrderRepository $orderRepository) {
        $this->orderRepository = $orderRepository;
    }

    public function index()
    {
        $orders = $this->orderRepository->get([
            'with' => ['orderDetails']
        ]);

        // Message for reponse
        $message = request()->name
        ? 'Filtered orders retrieved successfully.'
        : 'All orders retrieved successfully.';
        
        return ResponseApiHelper::success($message, new OrderCollection($orders));
    }

    public function show(Order $order)
    {
        $order->load('orderDetails');

        return ResponseApiHelper::success('Order retrieved successfully.', $order);
    }

    public function update(Request $request, Order $order)
    {
        $order->update($request->all());

        return ResponseApiHelper::success('Order updated successfully.', $order);
    }

    public function destroy(Order $order)
    {
        $order->delete();

        return ResponseApiHelper::success('Order deleted successfully.');
    }
}
