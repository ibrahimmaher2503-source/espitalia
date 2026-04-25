<?php

namespace Modules\Pharmacy\Http\Controllers\PharmacyPanel;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Pharmacy\Models\PharmacyOrder;
use Modules\Pharmacy\Services\OrderService;
use Modules\Pharmacy\Services\PharmacyCommissionService;

class PharmacyPanelOrderController extends Controller
{
    protected $orderService;

    protected $commissionService;

    public function __construct(OrderService $orderService, PharmacyCommissionService $commissionService)
    {
        $this->orderService = $orderService;
        $this->commissionService = $commissionService;
        $this->module_title = 'Orders';
        $this->module_name = 'orders';
        $this->module_path = 'pharmacy::backend.pharmacy-panel.orders';

        $this->middleware('role:pharmacy');
        $this->middleware('can:view_pharmacy_orders')->only(['index', 'index_data', 'show']);
        $this->middleware('can:edit_pharmacy_orders')->only(['accept', 'reject', 'advance_status']);
    }

    public function index()
    {
        $page_title = $this->module_title;
        $page_title_icon = 'fa fa-list';

        return view('pharmacy::backend.pharmacy-panel.orders.index', compact('page_title', 'page_title_icon'));
    }

    public function index_data(Request $request)
    {
        $pharmacyId = auth()->user()->pharmacy_id;
        $query = PharmacyOrder::where('pharmacy_id', $pharmacyId)
            ->with('user', 'pharmacy');

        if ($request->has('search') && ! empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%$search%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('first_name', 'like', "%$search%")
                            ->orWhere('last_name', 'like', "%$search%");
                    });
            });
        }

        if ($request->has('status') && ! empty($request->status)) {
            $query->where('status', $request->status);
        }

        $total = $query->count();
        $perPage = $request->get('per_page', 10);
        $orders = $query->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json([
            'data' => $orders->items(),
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $orders->currentPage(),
            'last_page' => $orders->lastPage(),
        ]);
    }

    public function show($id)
    {
        $pharmacyId = auth()->user()->pharmacy_id;
        $order = PharmacyOrder::where('id', $id)
            ->where('pharmacy_id', $pharmacyId)
            ->with('items', 'user', 'pharmacy', 'address')
            ->firstOrFail();

        $page_title = "Order #{$order->id}";
        $page_title_icon = 'fa fa-eye';

        return view('pharmacy::backend.pharmacy-panel.orders.show', compact(
            'page_title',
            'page_title_icon',
            'order'
        ));
    }

    public function accept($id)
    {
        $pharmacyId = auth()->user()->pharmacy_id;
        $order = PharmacyOrder::where('id', $id)
            ->where('pharmacy_id', $pharmacyId)
            ->firstOrFail();

        if ($order->status !== PharmacyOrder::STATUS_PENDING) {
            return response()->json([
                'status' => false,
                'message' => 'Only pending orders can be accepted',
            ], 422);
        }

        $order->update(['status' => PharmacyOrder::STATUS_CONFIRMED]);

        return response()->json([
            'status' => true,
            'message' => 'Order accepted successfully',
        ]);
    }

    public function reject($id)
    {
        $pharmacyId = auth()->user()->pharmacy_id;
        $order = PharmacyOrder::where('id', $id)
            ->where('pharmacy_id', $pharmacyId)
            ->firstOrFail();

        if ($order->status !== PharmacyOrder::STATUS_PENDING) {
            return response()->json([
                'status' => false,
                'message' => 'Only pending orders can be rejected',
            ], 422);
        }

        $this->orderService->cancelOrder($id, auth()->id(), 'Rejected by pharmacy');

        return response()->json([
            'status' => true,
            'message' => 'Order rejected and stock restored',
        ]);
    }

    public function advance_status($id, Request $request)
    {
        $pharmacyId = auth()->user()->pharmacy_id;
        $order = PharmacyOrder::where('id', $id)
            ->where('pharmacy_id', $pharmacyId)
            ->firstOrFail();

        $request->validate([
            'status' => 'required|in:confirmed,preparing,on_the_way,delivered',
        ]);

        $newStatus = $request->status;

        // Validate status flow
        $statusFlow = [
            PharmacyOrder::STATUS_CONFIRMED => PharmacyOrder::STATUS_PREPARING,
            PharmacyOrder::STATUS_PREPARING => PharmacyOrder::STATUS_ON_THE_WAY,
            PharmacyOrder::STATUS_ON_THE_WAY => PharmacyOrder::STATUS_DELIVERED,
        ];

        if (! isset($statusFlow[$order->status]) || $statusFlow[$order->status] !== $newStatus) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid status transition',
            ], 422);
        }

        $order->update(['status' => $newStatus]);

        // Calculate commission if order is delivered
        if ($newStatus === PharmacyOrder::STATUS_DELIVERED) {
            $this->commissionService->calculateCommission($order);
        }

        return response()->json([
            'status' => true,
            'message' => 'Order status updated successfully',
        ]);
    }
}
