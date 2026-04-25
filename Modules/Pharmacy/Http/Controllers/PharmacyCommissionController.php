<?php

namespace Modules\Pharmacy\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Pharmacy\Http\Requests\PharmacyCommissionRequest;
use Modules\Pharmacy\Models\PharmacyCommission;

class PharmacyCommissionController extends Controller
{
    public function __construct()
    {
        $this->module_title = 'Commission Templates';
        $this->module_name = 'pharmacy_commissions';
        $this->module_path = 'pharmacy::backend.commissions';

        $this->middleware('can:view_pharmacy_commission')->only(['index', 'index_data']);
        $this->middleware('can:add_pharmacy_commission')->only(['create', 'store']);
        $this->middleware('can:edit_pharmacy_commission')->only(['edit', 'update', 'update_status']);
        $this->middleware('can:delete_pharmacy_commission')->only(['destroy', 'bulk_action']);
    }

    public function index()
    {
        $page_title = $this->module_title;
        $page_title_icon = 'fa fa-list';

        return view('pharmacy::backend.commissions.index', compact('page_title', 'page_title_icon'));
    }

    public function index_data(Request $request)
    {
        $query = PharmacyCommission::withCount('pharmacies');

        if ($request->has('search') && ! empty($request->search)) {
            $search = $request->search;
            $query->where('title', 'like', "%$search%");
        }

        $total = $query->count();
        $perPage = $request->get('per_page', 10);
        $commissions = $query->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json([
            'data' => $commissions->items(),
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $commissions->currentPage(),
            'last_page' => $commissions->lastPage(),
        ]);
    }

    public function create()
    {
        $page_title = "Add {$this->module_title}";
        $page_title_icon = 'fa fa-plus';

        return view('pharmacy::backend.commissions.create', compact('page_title', 'page_title_icon'));
    }

    public function store(PharmacyCommissionRequest $request)
    {
        PharmacyCommission::create($request->validated());

        return response()->json([
            'status' => true,
            'message' => __('pharmacy.msg_commission_created'),
        ]);
    }

    public function edit($id)
    {
        $commission = PharmacyCommission::findOrFail($id);
        $page_title = "Edit {$this->module_title}";
        $page_title_icon = 'fa fa-edit';

        return view('pharmacy::backend.commissions.create', compact('page_title', 'page_title_icon', 'commission'));
    }

    public function update($id, PharmacyCommissionRequest $request)
    {
        $commission = PharmacyCommission::findOrFail($id);
        $commission->update($request->validated());

        return response()->json([
            'status' => true,
            'message' => "{$this->module_title} updated successfully",
        ]);
    }

    public function destroy($id)
    {
        $commission = PharmacyCommission::findOrFail($id);
        $commission->delete();

        return response()->json([
            'status' => true,
            'message' => "{$this->module_title} deleted successfully",
        ]);
    }

    public function bulk_action(Request $request)
    {
        $action = $request->get('action_type');
        $ids = $request->get('ids', []);

        if (empty($ids)) {
            return response()->json(['status' => false, 'message' => 'No items selected'], 422);
        }

        if ($action === 'delete') {
            PharmacyCommission::whereIn('id', $ids)->delete();

            return response()->json(['status' => true, 'message' => 'Items deleted successfully']);
        }

        return response()->json(['status' => false, 'message' => 'Invalid action'], 422);
    }

    public function update_status($id, Request $request)
    {
        $commission = PharmacyCommission::findOrFail($id);
        $commission->update(['status' => ! $commission->status]);

        return response()->json([
            'status' => true,
            'message' => 'Status updated successfully',
            'status_value' => $commission->status,
        ]);
    }
}
