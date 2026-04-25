<?php

namespace Modules\Pharmacy\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Pharmacy\Models\PharmacyOrder;
use Modules\Pharmacy\Services\OrderService;
use Modules\Pharmacy\Services\PharmacyCommissionService;
use Modules\Pharmacy\Transformers\PharmacyOrderResource;

class PharmacyOrderController extends Controller
{
    protected $orderService;

    protected $commissionService;

    public function __construct(OrderService $orderService, PharmacyCommissionService $commissionService)
    {
        $this->orderService = $orderService;
        $this->commissionService = $commissionService;
        $this->module_title = 'Pharmacy Orders';
        $this->module_name = 'pharmacy_order';
        $this->module_path = 'pharmacy.orders';

        $this->middleware('can:view_pharmacy_orders')->only(['index', 'index_data', 'show']);
        $this->middleware('can:edit_pharmacy_orders')->only(['update_status']);
    }

    /**
     * Display all pharmacy orders
     */
    public function index()
    {
        $this->data['page_title'] = $this->module_title;

        return view('pharmacy::backend.orders.index', $this->data);
    }

    /**
     * Get orders for DataTable (server-side)
     */
    public function index_data(Request $request)
    {
        $query = PharmacyOrder::with('user', 'pharmacy');

        // Search: order ID or customer name
        if ($request->has('search') && $request->search) {
            $search = str_replace(['%', '_'], ['\%', '\_'], $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('pharmacy_orders.id', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        // Status filter
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $perPage = min((int) $request->get('per_page', 15), 50);
        $orders = $query->orderByDesc('created_at')->paginate($perPage);

        return response()->json([
            'data' => PharmacyOrderResource::collection($orders),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
                'last_page' => $orders->lastPage(),
            ],
        ]);
    }

    /**
     * Show single order with details
     */
    public function show($id)
    {
        $order = PharmacyOrder::with('user', 'pharmacy', 'address', 'items')
            ->findOrFail($id);

        $this->data['page_title'] = 'Order #'.$order->id;
        $this->data['order'] = $order;
        $this->data['statuses'] = [
            PharmacyOrder::STATUS_PENDING => 'Pending',
            PharmacyOrder::STATUS_CONFIRMED => 'Confirmed',
            PharmacyOrder::STATUS_PREPARING => 'Preparing',
            PharmacyOrder::STATUS_ON_THE_WAY => 'On the Way',
            PharmacyOrder::STATUS_DELIVERED => 'Delivered',
            PharmacyOrder::STATUS_CANCELLED => 'Cancelled',
        ];

        return view('pharmacy::backend.orders.show', $this->data);
    }

    /**
     * Update order status (admin can set any status)
     */
    public function update_status($id, Request $request)
    {
        try {
            $order = PharmacyOrder::findOrFail($id);
            $newStatus = $request->input('status');

            // Validate status is one of the allowed values
            $validStatuses = [
                PharmacyOrder::STATUS_PENDING,
                PharmacyOrder::STATUS_CONFIRMED,
                PharmacyOrder::STATUS_PREPARING,
                PharmacyOrder::STATUS_ON_THE_WAY,
                PharmacyOrder::STATUS_DELIVERED,
                PharmacyOrder::STATUS_CANCELLED,
            ];

            if (! in_array($newStatus, $validStatuses)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid status',
                ], 422);
            }

            // Enforce forward-only state machine (except cancellation which is always allowed)
            $statusOrder = [
                PharmacyOrder::STATUS_PENDING => 1,
                PharmacyOrder::STATUS_CONFIRMED => 2,
                PharmacyOrder::STATUS_PREPARING => 3,
                PharmacyOrder::STATUS_ON_THE_WAY => 4,
                PharmacyOrder::STATUS_DELIVERED => 5,
                PharmacyOrder::STATUS_CANCELLED => 99,
            ];

            $currentRank = $statusOrder[$order->status] ?? 0;
            $newRank = $statusOrder[$newStatus] ?? 0;

            if ($newStatus !== PharmacyOrder::STATUS_CANCELLED && $newRank <= $currentRank) {
                return response()->json([
                    'status' => false,
                    'message' => 'Cannot transition from ' . $order->status . ' to ' . $newStatus . '. Only forward transitions are allowed.',
                ], 422);
            }

            // If transitioning to cancelled, restore stock
            if ($newStatus === PharmacyOrder::STATUS_CANCELLED && $order->status !== PharmacyOrder::STATUS_CANCELLED) {
                $this->orderService->cancelOrder($order->id, auth()->id(), null, true);
            } else {
                // Otherwise just update status
                $order->update(['status' => $newStatus]);

                // Calculate commission if order is delivered
                if ($newStatus === PharmacyOrder::STATUS_DELIVERED) {
                    $this->commissionService->calculateCommission($order);
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Order status updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
