<?php

namespace Modules\Pharmacy\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Pharmacy\Models\PharmacyRefund;
use Modules\Pharmacy\Services\PharmacyAuditService;
use Modules\Pharmacy\Services\PharmacyCommissionService;
use Modules\Pharmacy\Services\PharmacyNotificationService;

class PharmacyRefundController extends Controller
{
    public function __construct()
    {
        $this->module_title = 'Pharmacy Refunds';

        $this->middleware('can:view_pharmacy_refunds')->only(['index', 'index_data', 'show']);
        $this->middleware('can:process_pharmacy_refunds')->only(['process']);
    }

    public function index()
    {
        $page_title = $this->module_title;
        $page_title_icon = 'fa fa-list';

        return view('pharmacy::backend.refunds.index', compact('page_title', 'page_title_icon'));
    }

    public function index_data(Request $request)
    {
        $query = PharmacyRefund::with(['order', 'user', 'processor']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('refund_number', 'like', "%$search%")
                    ->orWhere('order_id', 'like', "%$search%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $total = $query->count();
        $perPage = $request->get('per_page', 10);
        $refunds = $query->orderByDesc('created_at')->paginate($perPage);

        return response()->json([
            'data' => $refunds->items(),
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $refunds->currentPage(),
            'last_page' => $refunds->lastPage(),
        ]);
    }

    public function show($id)
    {
        $refund = PharmacyRefund::with(['order.items', 'user', 'processor'])->findOrFail($id);
        $page_title = "Refund #{$refund->refund_number}";
        $page_title_icon = 'fa fa-eye';

        return view('pharmacy::backend.refunds.show', compact('page_title', 'page_title_icon', 'refund'));
    }

    public function process($id, Request $request)
    {
        $request->validate([
            'status' => 'required|in:approved,declined,processed',
            'admin_notes' => 'nullable|string|max:2000',
        ]);

        $refund = PharmacyRefund::with('order')->findOrFail($id);

        $oldStatus = $refund->status;
        $refund->update([
            'status' => $request->status,
            'admin_notes' => $request->admin_notes,
            'processed_by' => auth()->id(),
            'processed_at' => now(),
        ]);

        // Update order refund status
        if (in_array($request->status, ['approved', 'processed'])) {
            $refund->order->update([
                'refund_status' => $refund->refund_type === 'full' ? 'refunded' : 'partial',
                'refund_amount' => $refund->refund_amount,
            ]);

            // Reverse commission
            if ($refund->refund_type === 'full') {
                (new PharmacyCommissionService)->reverseCommission($refund->order);
            }
        }

        // Audit
        PharmacyAuditService::log($refund->order->pharmacy_id, 'refund_processed', $refund, ['status' => $oldStatus], ['status' => $request->status]);

        // Notify
        (new PharmacyNotificationService)->notifyRefund($refund);

        return response()->json(['status' => true, 'message' => 'Refund processed successfully']);
    }
}
