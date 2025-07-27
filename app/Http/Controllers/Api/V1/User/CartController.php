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

        $carts = $this->cartRepository->get([
            'user_id' => $user->id,
            'with' => ['product', 'product.activeDiscount'],
            'page' => 10
        ]);
        
        return ResponseApiHelper::success('Carts retrieved successfully.', CartResource::collection($carts));
    }

    public function add(CartStoreRequest $request)
    {
        $user = AuthHelper::getUserFromToken($request->bearerToken());
    
        // Cart Service
        $addedCart = $this->cartService->addItem($user->id, $request->product_id, $request->quantity);

        return ResponseApiHelper::success('Product on cart successfully added.', new CartResource($addedCart));
    }

    public function decrease(CartDecreaseRequest $request, Cart $cart)
    {
        $user = AuthHelper::getUserFromToken($request->bearerToken());
        
        // Cart Service
        $decreasedCart = $this->cartService->decreaseItem($user->id, $request->quantity, $cart);

        return ResponseApiHelper::success($decreasedCart ? 'Product on cart successfully decreased' : 'Product on cart successfully deleted.', $decreasedCart ? new CartResource($decreasedCart) : null);
    }

    public function replace(CartReplaceRequest $request, Cart $cart)
    {
        $user = AuthHelper::getUserFromToken($request->bearerToken());
        
        // Cart Service
        $replacedCart = $this->cartService->replaceItem($user->id,  $request->quantity, $cart);

        return ResponseApiHelper::success($replacedCart ? 'Product on cart successfully replaced.' : 'Product on cart successfully deleted.', $replacedCart ? new CartResource($replacedCart) : null);
    }

    public function remove(Request $request, Cart $cart)
    {
        $user = AuthHelper::getUserFromToken($request->bearerToken());
        
        // Cart Service
        $deletedCart = $this->cartService->removeItem($user->id, $cart);

        return ResponseApiHelper::success('Product on cart successfully removed.' . ($deletedCart ? '' : ' Cart is empty now.'), $deletedCart ? new CartResource($deletedCart) : null);
    }
}