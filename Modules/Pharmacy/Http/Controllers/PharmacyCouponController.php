<?php

namespace Modules\Pharmacy\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Pharmacy\Http\Requests\CouponRequest;
use Modules\Pharmacy\Models\PharmacyCoupon;

class PharmacyCouponController extends Controller
{
    public function __construct()
    {
        $this->module_title = 'Pharmacy Coupons';
        $this->module_name = 'pharmacy_coupons';

        $this->middleware('can:view_pharmacy_coupons')->only(['index', 'index_data']);
        $this->middleware('can:add_pharmacy_coupons')->only(['create', 'store']);
        $this->middleware('can:edit_pharmacy_coupons')->only(['edit', 'update', 'update_status']);
        $this->middleware('can:delete_pharmacy_coupons')->only(['destroy', 'bulk_action']);
    }

    public function index()
    {
        $page_title = $this->module_title;
        $page_title_icon = 'fa fa-list';

        return view('pharmacy::backend.coupons.index', compact('page_title', 'page_title_icon'));
    }

    public function index_data(Request $request)
    {
        $query = PharmacyCoupon::with('pharmacy');

        // Vendor scoping
        $user = auth()->user();
        if ($user->hasRole('vendor')) {
            $query->whereHas('pharmacy', fn ($q) => $q->where('vendor_id', $user->id));
        }

        if ($request->filled('search')) {
            $search = str_replace(['%', '_'], ['\%', '\_'], $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%$search%")
                    ->orWhere('title_ar', 'like', "%$search%")
                    ->orWhere('title_en', 'like', "%$search%");
            });
        }

        $total = $query->count();
        $perPage = min((int) $request->get('per_page', 10), 50);
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
        $page_title = "Add {$this->module_title}";
        $page_title_icon = 'fa fa-plus';

        return view('pharmacy::backend.coupons.create', compact('page_title', 'page_title_icon'));
    }

    public function store(CouponRequest $request)
    {
        PharmacyCoupon::create($request->validated());

        return response()->json(['status' => true, 'message' => 'Coupon created successfully']);
    }

    public function edit($id)
    {
        $coupon = PharmacyCoupon::findOrFail($id);
        $page_title = "Edit {$this->module_title}";
        $page_title_icon = 'fa fa-edit';

        return view('pharmacy::backend.coupons.create', compact('page_title', 'page_title_icon', 'coupon'));
    }

    public function update($id, CouponRequest $request)
    {
        $coupon = PharmacyCoupon::findOrFail($id);
        $coupon->update($request->validated());

        return response()->json(['status' => true, 'message' => 'Coupon updated successfully']);
    }

    public function destroy($id)
    {
        PharmacyCoupon::findOrFail($id)->delete();

        return response()->json(['status' => true, 'message' => 'Coupon deleted successfully']);
    }

    public function bulk_action(Request $request)
    {
        $ids = $request->get('ids', []);
        if (empty($ids)) {
            return response()->json(['status' => false, 'message' => 'No items selected'], 422);
        }

        if ($request->get('action_type') === 'delete') {
            PharmacyCoupon::whereIn('id', $ids)->delete();

            return response()->json(['status' => true, 'message' => 'Items deleted successfully']);
        }

        return response()->json(['status' => false, 'message' => 'Invalid action'], 422);
    }

    public function update_status($id)
    {
        $coupon = PharmacyCoupon::findOrFail($id);
        $coupon->update(['is_active' => ! $coupon->is_active]);

        return response()->json(['status' => true, 'message' => 'Status updated', 'is_active' => $coupon->is_active]);
    }
}
