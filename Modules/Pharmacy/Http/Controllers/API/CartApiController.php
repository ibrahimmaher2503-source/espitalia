<?php

namespace Modules\Pharmacy\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Pharmacy\Http\Requests\CartItemRequest;
use Modules\Pharmacy\Services\CartService;

class CartApiController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Get user's cart
     */
    public function index()
    {
        $cart = $this->cartService->getCart(auth()->id());

        return response()->json([
            'status' => true,
            'data' => $cart,
        ]);
    }

    /**
     * Add item to cart
     */
    public function store(CartItemRequest $request)
    {
        try {
            $cart = $this->cartService->addItem(
                auth()->id(),
                $request->product_id,
                $request->quantity
            );

            return response()->json([
                'status' => true,
                'data' => $cart,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'errors' => ['product_id' => [$e->getMessage()]],
            ], 422);
        }
    }

    /**
     * Update cart item quantity
     */
    public function update($id, Request $request)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            $cart = $this->cartService->updateItem($id, $request->quantity, auth()->id());

            return response()->json([
                'status' => true,
                'data' => $cart,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'errors' => ['quantity' => [$e->getMessage()]],
            ], 422);
        }
    }

    /**
     * Remove item from cart
     */
    public function destroy($id)
    {
        try {
            $this->cartService->removeItem($id, auth()->id());

            return response()->json([
                'status' => true,
                'message' => 'Item removed from cart',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Item not found',
            ], 404);
        }
    }
}
