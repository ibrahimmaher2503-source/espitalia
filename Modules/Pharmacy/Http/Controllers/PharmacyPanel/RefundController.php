<?php

namespace Modules\Pharmacy\Http\Controllers\PharmacyPanel;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Pharmacy\Models\PharmacyRefund;

class RefundController extends Controller
{
    public function __construct()
    {
        $this->middleware(['role:pharmacy']);
        $this->middleware('can:view_pharmacy_refunds');
    }

    public function index()
    {
        $page_title = 'Refund Requests';

        return view('pharmacy::backend.pharmacy-panel.refunds.index', compact('page_title'));
    }

    public function index_data(Request $request)
    {
        $pharmacyId = auth()->user()->pharmacy_id;
        $query = PharmacyRefund::with(['order', 'user'])
            ->whereHas('order', fn ($q) => $q->where('pharmacy_id', $pharmacyId));

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
        $pharmacyId = auth()->user()->pharmacy_id;
        $refund = PharmacyRefund::with(['order.items', 'user'])
            ->whereHas('order', fn ($q) => $q->where('pharmacy_id', $pharmacyId))
            ->findOrFail($id);
        $page_title = "Refund #{$refund->refund_number}";

        return view('pharmacy::backend.pharmacy-panel.refunds.show', compact('page_title', 'refund'));
    }
}
