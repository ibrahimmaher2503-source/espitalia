<?php

namespace Modules\Pharmacy\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Pharmacy\Http\Requests\RefundRequest;
use Modules\Pharmacy\Models\PharmacyOrder;
use Modules\Pharmacy\Models\PharmacyRefund;
use Modules\Pharmacy\Services\PharmacyAuditService;
use Modules\Pharmacy\Services\PharmacyNotificationService;
use Modules\Pharmacy\Transformers\RefundResource;

class RefundApiController extends Controller
{
    /**
     * Request a refund for an order (patient)
     */
    public function store($orderId, RefundRequest $request)
    {
        $order = PharmacyOrder::where('id', $orderId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if (in_array($order->status, [PharmacyOrder::STATUS_CANCELLED])) {
            return response()->json([
                'status' => false,
                'message' => 'Cannot request refund for a cancelled order',
            ], 422);
        }

        if ($order->refund_status === 'refunded') {
            return response()->json([
                'status' => false,
                'message' => 'This order has already been refunded',
            ], 422);
        }

        $existingRefund = PharmacyRefund::where('order_id', $order->id)
            ->whereIn('status', [PharmacyRefund::STATUS_PENDING, PharmacyRefund::STATUS_APPROVED])
            ->first();

        if ($existingRefund) {
            return response()->json([
                'status' => false,
                'message' => 'A refund request is already in progress for this order',
            ], 422);
        }

        $refundAmount = $request->refund_type === 'full' ? $order->total : $request->refund_amount;

        if ($refundAmount > $order->total) {
            return response()->json([
                'status' => false,
                'message' => 'Refund amount cannot exceed order total',
            ], 422);
        }

        $refund = PharmacyRefund::create([
            'order_id' => $order->id,
            'user_id' => auth()->id(),
            'reason' => $request->reason,
            'refund_type' => $request->refund_type,
            'refund_amount' => $refundAmount,
            'refund_method' => $request->refund_method ?? 'original_method',
            'status' => PharmacyRefund::STATUS_PENDING,
        ]);

        $order->update(['refund_status' => 'pending']);

        (new PharmacyNotificationService)->notifyRefund($refund);

        PharmacyAuditService::log(
            $order->pharmacy_id,
            'refund_requested',
            $refund,
            null,
            ['refund_amount' => $refundAmount, 'refund_type' => $request->refund_type]
        );

        return response()->json([
            'status' => true,
            'data' => new RefundResource($refund->load('order', 'user')),
            'message' => 'Refund request submitted successfully',
        ], 201);
    }

    /**
     * List patient's refund requests
     */
    public function index(Request $request)
    {
        $query = PharmacyRefund::where('user_id', auth()->id())
            ->with('order');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = min((int) $request->get('per_page', 15), 50);
        $refunds = $query->orderByDesc('created_at')->paginate($perPage);

        return response()->json([
            'status' => true,
            'data' => RefundResource::collection($refunds),
            'meta' => [
                'current_page' => $refunds->currentPage(),
                'per_page' => $refunds->perPage(),
                'total' => $refunds->total(),
                'last_page' => $refunds->lastPage(),
            ],
        ]);
    }

    /**
     * Show refund detail (patient)
     */
    public function show($id)
    {
        $refund = PharmacyRefund::where('user_id', auth()->id())
            ->with('order', 'processor')
            ->findOrFail($id);

        return response()->json([
            'status' => true,
            'data' => new RefundResource($refund),
        ]);
    }
}
