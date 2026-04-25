<?php

namespace Modules\Pharmacy\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Pharmacy\Models\PharmacyAuditLog;

class PharmacyAuditController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view_pharmacy_audit');
    }

    public function index()
    {
        $page_title = 'Pharmacy Audit Log';
        $page_title_icon = 'fa fa-clipboard';

        return view('pharmacy::backend.audit.index', compact('page_title', 'page_title_icon'));
    }

    public function index_data(Request $request)
    {
        $query = PharmacyAuditLog::with(['pharmacy', 'user']);

        if ($request->filled('pharmacy_id')) {
            $query->where('pharmacy_id', $request->pharmacy_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('search')) {
            $query->where('action', 'like', "%{$request->search}%");
        }

        $total = $query->count();
        $perPage = $request->get('per_page', 10);
        $logs = $query->orderByDesc('created_at')->paginate($perPage);

        return response()->json([
            'data' => $logs->items(),
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $logs->currentPage(),
            'last_page' => $logs->lastPage(),
        ]);
    }
}
