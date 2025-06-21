<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Models\Cart;
use App\Models\Product;
use App\Helpers\AuthHelper;
use Illuminate\Http\Request;
use App\Helpers\LoggerHelper;
use GuzzleHttp\Psr7\Response;
use App\Helpers\ResponseApiHelper;
use App\Repository\CartRepository;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\CartDecreaseRequest;
use App\Http\Requests\CartReplaceRequest;
use App\Http\Requests\CartStoreRequest;
use App\Http\Resources\CartResource;
use App\Services\CartService;

class CartController extends Controller
{
    private $cartRepository, $cartService;

    public function __construct(
        CartRepository $cartRepository,
        CartService $cartService
    )
    {
        $this->cartRepository = $cartRepository;
        $this->cartService = $cartService;
    }
    
    public function index()
    {
        $user = AuthHelper::getUserFromToken(request()->bearerToken());

        $carts = Cart::with(['product.activeDiscount'])
            ->where('user_id', $user->id)
            ->get();
        
        return ResponseApiHelper::success('Carts retrieved successfully.', CartResource::collection($carts));
    }

    public function add(CartStoreRequest $request)
    {
        $user = AuthHelper::getUserFromToken($request->bearerToken());
        
        try {
            // Cart Service
            $cart = $this->cartService->addItem($user->id, $request->product_id, $request->quantity);

        } catch (\Throwable $th) {

            return ResponseApiHelper::error('Failed to create cart.', [], 500);
        }

        return ResponseApiHelper::success('Product on cart successfully added.');
    }

    public function decrease(CartDecreaseRequest $request, Cart $cart)
    {
        $user = AuthHelper::getUserFromToken($request->bearerToken());
        
        try {
            // Cart Service
            $cart = $this->cartService->decreaseItem($user->id, $request->quantity, $cart);

        } catch (\Throwable $th) {

            return ResponseApiHelper::error('Failed to decrease product on cart.', [], 500);
        }

        return ResponseApiHelper::success('Product on cart successfully decreased.');
    }

    public function replace(CartReplaceRequest $request, Cart $cart)
    {
        $user = AuthHelper::getUserFromToken($request->bearerToken());
        
        try {
            // Cart Service
            $cart = $this->cartService->replaceItem($user->id,  $request->quantity, $cart);

        } catch (\Throwable $th) {

            return ResponseApiHelper::error('Failed to replace product on cart.', [], 500);
        }

        return ResponseApiHelper::success('Product on cart successfully replaced.');
    }

    public function remove(Request $request, Cart $cart)
    {
        $user = AuthHelper::getUserFromToken($request->bearerToken());
        
        try {
            // Cart Service
            $cart = $this->cartService->removeItem($user->id, $cart);

        } catch (\Throwable $th) {
            
            return ResponseApiHelper::error('Failed to remove cart.', [], 500);
        }

        return ResponseApiHelper::success('Product on cart successfully removed.');
    }
}