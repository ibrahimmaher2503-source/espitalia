<?php

namespace Modules\Pharmacy\Http\Controllers\PharmacyPanel;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Pharmacy\Models\PharmacyExpiryAlert;
use Modules\Pharmacy\Models\PharmacyStockAlert;

class AlertController extends Controller
{
    public function __construct()
    {
        $this->middleware(['role:pharmacy']);
    }

    public function index()
    {
        $page_title = 'Alerts';

        return view('pharmacy::backend.pharmacy-panel.alerts.index', compact('page_title'));
    }

    public function index_data(Request $request)
    {
        $pharmacyId = auth()->user()->pharmacy_id;
        $type = $request->get('alert_category', 'stock');

        if ($type === 'expiry') {
            $query = PharmacyExpiryAlert::with(['batch.inventory.product'])
                ->where('pharmacy_id', $pharmacyId);
        } else {
            $query = PharmacyStockAlert::with(['inventory.product'])
                ->where('pharmacy_id', $pharmacyId);
        }

        if ($request->filled('resolved')) {
            $query->where('is_resolved', $request->boolean('resolved'));
        } else {
            $query->where('is_resolved', false);
        }

        $total = $query->count();
        $perPage = $request->get('per_page', 10);
        $alerts = $query->orderByDesc('created_at')->paginate($perPage);

        return response()->json([
            'data' => $alerts->items(),
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $alerts->currentPage(),
            'last_page' => $alerts->lastPage(),
        ]);
    }
}
