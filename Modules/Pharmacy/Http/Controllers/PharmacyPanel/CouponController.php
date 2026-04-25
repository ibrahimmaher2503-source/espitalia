<?php

namespace Modules\Pharmacy\Http\Controllers\PharmacyPanel;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Pharmacy\Http\Requests\CouponRequest;
use Modules\Pharmacy\Models\PharmacyCoupon;

class CouponController extends Controller
{
    public function __construct()
    {
        $this->middleware(['role:pharmacy']);
        $this->middleware('can:view_pharmacy_coupons')->only(['index', 'index_data']);
        $this->middleware('can:add_pharmacy_coupons')->only(['create', 'store']);
        $this->middleware('can:edit_pharmacy_coupons')->only(['edit', 'update', 'update_status']);
        $this->middleware('can:delete_pharmacy_coupons')->only(['destroy']);
    }

    public function index()
    {
        $page_title = 'Coupons';

        return view('pharmacy::backend.pharmacy-panel.coupons.index', compact('page_title'));
    }

    public function index_data(Request $request)
    {
        $pharmacyId = auth()->user()->pharmacy_id;
        $query = PharmacyCoupon::where('pharmacy_id', $pharmacyId);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('code', 'like', "%{$request->search}%")
                    ->orWhere('title_en', 'like', "%{$request->search}%");
            });
        }

        $total = $query->count();
        $perPage = $request->get('per_page', 10);
        $coupons = $query->orderByDesc('created_at')->paginate($perPage);

        return response()->json([
            'data' => $coupons->items(),
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $coupons->currentPage(),
            'last_page' => $coupons->lastPage(),
        ]);
    }

    public function create()
    {
        $page_title = 'Add Coupon';

        return view('pharmacy::backend.pharmacy-panel.coupons.create', compact('page_title'));
    }

    public function store(CouponRequest $request)
    {
        $data = $request->validated();
        $data['pharmacy_id'] = auth()->user()->pharmacy_id;

        PharmacyCoupon::create($data);

        return response()->json(['status' => true, 'message' => 'Coupon created']);
    }

    public function edit($id)
    {
        $coupon = PharmacyCoupon::where('pharmacy_id', auth()->user()->pharmacy_id)->findOrFail($id);
        $page_title = 'Edit Coupon';

        return view('pharmacy::backend.pharmacy-panel.coupons.create', compact('page_title', 'coupon'));
    }

    public function update($id, CouponRequest $request)
    {
        $coupon = PharmacyCoupon::where('pharmacy_id', auth()->user()->pharmacy_id)->findOrFail($id);
        $coupon->update($request->validated());

        return response()->json(['status' => true, 'message' => 'Coupon updated']);
    }

    public function destroy($id)
    {
        PharmacyCoupon::where('pharmacy_id', auth()->user()->pharmacy_id)->findOrFail($id)->delete();

        return response()->json(['status' => true, 'message' => 'Coupon deleted']);
    }

    public function update_status($id)
    {
        $coupon = PharmacyCoupon::where('pharmacy_id', auth()->user()->pharmacy_id)->findOrFail($id);
        $coupon->update(['is_active' => ! $coupon->is_active]);

        return response()->json(['status' => true, 'message' => 'Status updated']);
    }
}
