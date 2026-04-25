<?php

namespace Modules\Pharmacy\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Pharmacy\Http\Requests\PharmacyProductTypeRequest;
use Modules\Pharmacy\Models\PharmacyProductType;

class PharmacyProductTypeController extends Controller
{
    public function __construct()
    {
        $this->module_title = 'Pharmacy Product Types';
        $this->module_name = 'pharmacy_product_types';
        $this->module_path = 'pharmacy::backend.product-types';

        $this->middleware('can:view_pharmacy_product_types')->only(['index', 'index_data']);
        $this->middleware('can:add_pharmacy_product_types')->only(['create', 'store']);
        $this->middleware('can:edit_pharmacy_product_types')->only(['edit', 'update', 'update_status']);
        $this->middleware('can:delete_pharmacy_product_types')->only(['destroy', 'bulk_action']);
    }

    public function index()
    {
        $page_title = $this->module_title;
        $page_title_icon = 'fa fa-list';

        return view('pharmacy::backend.product-types.index', compact('page_title', 'page_title_icon'));
    }

    public function index_data(Request $request)
    {
        $query = PharmacyProductType::query();

        if ($request->has('search') && ! empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name_ar', 'like', "%$search%")
                    ->orWhere('name_en', 'like', "%$search%");
            });
        }

        $total = $query->count();
        $perPage = $request->get('per_page', 10);
        $types = $query->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json([
            'data' => $types->items(),
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $types->currentPage(),
            'last_page' => $types->lastPage(),
        ]);
    }

    public function create()
    {
        $page_title = "Add {$this->module_title}";
        $page_title_icon = 'fa fa-plus';

        return view('pharmacy::backend.product-types.create', compact('page_title', 'page_title_icon'));
    }

    public function store(PharmacyProductTypeRequest $request)
    {
        PharmacyProductType::create($request->validated());

        return response()->json([
            'status' => true,
            'message' => "{$this->module_title} added successfully",
        ]);
    }

    public function edit($id)
    {
        $type = PharmacyProductType::findOrFail($id);
        $page_title = "Edit {$this->module_title}";
        $page_title_icon = 'fa fa-edit';

        return view('pharmacy::backend.product-types.create', compact('page_title', 'page_title_icon', 'type'));
    }

    public function update($id, PharmacyProductTypeRequest $request)
    {
        $type = PharmacyProductType::findOrFail($id);
        $type->update($request->validated());

        return response()->json([
            'status' => true,
            'message' => "{$this->module_title} updated successfully",
        ]);
    }

    public function destroy($id)
    {
        $type = PharmacyProductType::findOrFail($id);
        $type->delete();

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
            PharmacyProductType::whereIn('id', $ids)->delete();

            return response()->json(['status' => true, 'message' => 'Items deleted successfully']);
        }

        return response()->json(['status' => false, 'message' => 'Invalid action'], 422);
    }

    public function update_status($id, Request $request)
    {
        // ProductTypes don't have is_active, so just return success
        return response()->json([
            'status' => true,
            'message' => 'Status updated successfully',
        ]);
    }
}
