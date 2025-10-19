<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Models\Cart;
use App\Models\Product;
use App\Helpers\AuthHelper;
use Illuminate\Http\Request;
use App\Helpers\LoggerHelper;
use App\Services\CartService;
use GuzzleHttp\Psr7\Response;
use App\Helpers\ResponseApiHelper;
use App\Repository\CartRepository;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;
use App\Helpers\ProductDiscountHelper;
use App\Http\Requests\CartStoreRequest;
use App\Http\Requests\CartReplaceRequest;
use App\Http\Requests\CartDecreaseRequest;

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

        // Get cart summary
        $summary = $this->cartService->getCartSummary($user->id);
        
        return ResponseApiHelper::success('Carts retrieved successfully.', [
            'items' => CartResource::collection($carts),
            'summary' => $summary
        ]);
    }

    public function add(CartStoreRequest $request)
    {
        $user = AuthHelper::getUserFromToken($request->bearerToken());
    
        // Cart Service
        $addedCart = $this->cartService->addItem($user->id, $request->product_id, $request->quantity);

        return ResponseApiHelper::success('Product successfully added to cart.', new CartResource($addedCart));
    }

    public function decrease(CartDecreaseRequest $request, Cart $cart)
    {
        $user = AuthHelper::getUserFromToken($request->bearerToken());
        
        // Authorize cart belongs to user
        if ($cart->user_id !== $user->id) {
            return ResponseApiHelper::error('Unauthorized to modify this cart item.', [], 403);
        }
        
        // Cart Service
        $decreasedCart = $this->cartService->decreaseItem($user->id, $request->quantity, $cart);

        return ResponseApiHelper::success(
            $decreasedCart ? 'Product quantity decreased successfully.' : 'Product removed from cart.',
            $decreasedCart ? new CartResource($decreasedCart) : null
        );
    }

    public function replace(CartReplaceRequest $request, Cart $cart)
    {
        $user = AuthHelper::getUserFromToken($request->bearerToken());
        
        // Authorize cart belongs to user
        if ($cart->user_id !== $user->id) {
            return ResponseApiHelper::error('Unauthorized to modify this cart item.', [], 403);
        }
        
        // Cart Service
        $replacedCart = $this->cartService->replaceItem($user->id, $request->quantity, $cart);

        return ResponseApiHelper::success(
            $replacedCart ? 'Product quantity updated successfully.' : 'Product removed from cart.',
            $replacedCart ? new CartResource($replacedCart) : null
        );
    }

    public function remove(Request $request, Cart $cart)
    {
        $user = AuthHelper::getUserFromToken($request->bearerToken());
        
        // Authorize cart belongs to user
        if ($cart->user_id !== $user->id) {
            return ResponseApiHelper::error('Unauthorized to modify this cart item.', [], 403);
        }
        
        // Cart Service - Fixed: handle boolean return
        $isDeleted = $this->cartService->removeItem($user->id, $cart);

        return ResponseApiHelper::success('Product successfully removed from cart.', null);
    }

    // Clear all cart items
    public function clear(Request $request)
    {
        $user = AuthHelper::getUserFromToken($request->bearerToken());
        
        $isCleared = $this->cartService->clearCart($user->id);

        return ResponseApiHelper::success('Cart cleared successfully.', null);
    }

    // Get cart summary only
    public function summary(Request $request)
    {
        $user = AuthHelper::getUserFromToken($request->bearerToken());
        
        $summary = $this->cartService->getCartSummary($user->id);

        return ResponseApiHelper::success('Cart summary retrieved successfully.', $summary);
    }

    // Sync guest cart to authenticated user cart
    public function syncCart(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $user = AuthHelper::getUserFromToken($request->bearerToken());

        $result = $this->cartService->syncCart($user->id, $request->items);

        return ResponseApiHelper::success('Cart synchronized successfully.', [
            'synced_items' => CartResource::collection($result['synced_items']),
            'errors' => $result['errors'],
            'summary' => [
                'success_count' => $result['success_count'],
                'error_count' => $result['error_count'],
            ]
        ]);
    }

    //Get guest cart with current prices (for price validation)
    public function syncGuestCart(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $items = collect($request->items)->map(function ($item) {
            $product = Product::with('activeDiscount')->findOrFail($item['product_id']);
            $priceAtTime = ProductDiscountHelper::getPriceAtTime($product);
            
            return [
                'product_id' => $product->id,
                'product' => $product,
                'quantity' => $item['quantity'],
                'price_at_time' => $priceAtTime,
                'subtotal' => $item['quantity'] * $priceAtTime,
                'in_stock' => $product->stock >= $item['quantity'],
                'stock_available' => $product->stock,
            ];
        });

        $totalItems = $items->sum('quantity');
        $totalPrice = $items->sum('subtotal');
        $hasStockIssues = $items->contains('in_stock', false);

        return ResponseApiHelper::success('Guest cart synchronized successfully.', [
            'items' => $items,
            'summary' => [
                'total_items' => $totalItems,
                'total_price' => $totalPrice,
                'items_count' => $items->count(),
                'has_stock_issues' => $hasStockIssues,
            ]
        ]);
    }

    // Validate cart before checkout
    public function validateCheckout(Request $request)
    {
        $user = AuthHelper::getUserFromToken($request->bearerToken());
        
        $validation = $this->cartService->validateCartForCheckout($user->id);

        if (!$validation['is_valid']) {
            return ResponseApiHelper::error('Cart validation failed.', [
                'errors' => $validation['errors'],
                'valid_items_count' => count($validation['valid_items'])
            ], 422);
        }

        return ResponseApiHelper::success('Cart is valid for checkout.', [
            'valid_items' => CartResource::collection($validation['valid_items']),
            'total_items' => collect($validation['valid_items'])->sum('quantity'),
            'total_price' => collect($validation['valid_items'])->sum(function ($cart) {
                return $cart->quantity * $cart->price_at_time;
            })
        ]);
    }
}