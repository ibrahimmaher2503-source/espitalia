<?php

namespace Modules\Offer\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Offer\Models\OfferUsage;

class OfferUsageLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view_offer_analytics');
    }

    public function index()
    {
        $page_title = __('offer.lbl_offer_logs');

        return view('offer::backend.usage-logs.index', compact('page_title'));
    }

    public function index_data(Request $request)
    {
        $query = OfferUsage::with(['offer', 'user']);

        if ($request->filled('offer_id')) {
            $query->where('offer_id', $request->offer_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('from')) {
            $query->where('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->where('created_at', '<=', $request->to);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('coupon_code', 'LIKE', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('first_name', 'LIKE', "%{$search}%")
                        ->orWhere('last_name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%");
                  });
            });
        }

        $query->orderBy('created_at', 'desc');

        $perPage = $request->input('per_page', 15);
        $data = $query->paginate($perPage);

        return response()->json([
            'status' => true,
            'data' => $data->items(),
            'total' => $data->total(),
            'per_page' => $data->perPage(),
            'current_page' => $data->currentPage(),
            'last_page' => $data->lastPage(),
        ]);
    }
}
