<?php

namespace Modules\Pharmacy\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Pharmacy\Http\Requests\PharmacyOrderRequest;
use Modules\Pharmacy\Models\PharmacyOrder;
use Modules\Pharmacy\Services\OrderService;
use Modules\Pharmacy\Transformers\PharmacyOrderDetailResource;
use Modules\Pharmacy\Transformers\PharmacyOrderResource;

class OrderApiController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Place a new order
     */
    public function store(PharmacyOrderRequest $request)
    {
        try {
            // Verify address belongs to user
            $address = \App\Models\Address::where('id', $request->address_id)
                ->where('addressable_id', auth()->id())
                ->where('addressable_type', 'App\Models\User')
                ->firstOrFail();

            $order = $this->orderService->placeOrder(
                auth()->id(),
                $request->pharmacy_id,
                $request->address_id,
                $request->payment_method,
                $request->input('delivery_type', 'delivery'),
                $request->delivery_notes,
                $request->coupon_code,
                $request->prescription_id
            );

            return response()->json([
                'status' => true,
                'data' => $order,
                'message' => 'Order placed successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get user's orders list
     */
    public function index(Request $request)
    {
        $query = PharmacyOrder::where('user_id', auth()->id())
            ->with('pharmacy')
            ->withCount('items');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $perPage = min((int) $request->get('per_page', 15), 50);
        $orders = $query->orderByDesc('created_at')->paginate($perPage);

        return response()->json([
            'status' => true,
            'data' => PharmacyOrderResource::collection($orders),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
                'last_page' => $orders->lastPage(),
            ],
        ]);
    }

    /**
     * Get order detail
     */
    public function show($id)
    {
        $order = PharmacyOrder::where('id', $id)
            ->where('user_id', auth()->id())
            ->with('items', 'pharmacy', 'address')
            ->firstOrFail();

        return response()->json([
            'status' => true,
            'data' => new PharmacyOrderDetailResource($order),
        ]);
    }

    /**
     * Cancel a pending order
     */
    public function cancel($id, Request $request)
    {
        try {
            $order = PharmacyOrder::where('id', $id)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            if ($order->status !== PharmacyOrder::STATUS_PENDING) {
                return response()->json([
                    'status' => false,
                    'message' => 'Only pending orders can be cancelled',
                ], 422);
            }

            $this->orderService->cancelOrder($id, auth()->id(), $request->input('reason'));

            return response()->json([
                'status' => true,
                'message' => 'Order cancelled successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found',
            ], 404);
        }
    }
}
