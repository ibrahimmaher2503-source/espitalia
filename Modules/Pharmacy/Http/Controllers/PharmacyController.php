<?php

namespace Modules\Pharmacy\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Pharmacy\Http\Requests\PharmacyRequest;
use Modules\Pharmacy\Models\Pharmacy;
use Modules\Pharmacy\Models\PharmacyCommission;

class PharmacyController extends Controller
{
    public function __construct()
    {
        $this->module_title = 'Pharmacies';
        $this->module_name = 'pharmacies';
        $this->module_path = 'pharmacy::backend.pharmacies';

        $this->middleware('can:view_pharmacies')->only(['index', 'index_data']);
        $this->middleware('can:add_pharmacies')->only(['create', 'store']);
        $this->middleware('can:edit_pharmacies')->only(['edit', 'update', 'update_status']);
        $this->middleware('can:delete_pharmacies')->only(['destroy', 'bulk_action']);
    }

    public function index()
    {
        $page_title = $this->module_title;
        $page_title_icon = 'fa fa-list';

        return view('pharmacy::backend.pharmacies.index', compact('page_title', 'page_title_icon'));
    }

    public function index_data(Request $request)
    {
        $query = Pharmacy::withCount('staff');

        if ($request->has('search') && ! empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%");
            });
        }

        $total = $query->count();
        $perPage = $request->get('per_page', 10);
        $pharmacies = $query->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json([
            'data' => $pharmacies->items(),
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $pharmacies->currentPage(),
            'last_page' => $pharmacies->lastPage(),
        ]);
    }

    public function create()
    {
        $page_title = "Add {$this->module_title}";
        $page_title_icon = 'fa fa-plus';
        $commissions = PharmacyCommission::active()->get();

        return view('pharmacy::backend.pharmacies.create', compact('page_title', 'page_title_icon', 'commissions'));
    }

    public function store(PharmacyRequest $request)
    {
        $data = $request->validated();
        $pharmacy = Pharmacy::create($data);

        if ($request->hasFile('logo')) {
            $pharmacy->addMediaFromRequest('logo')
                ->toMediaCollection('logo');
        }

        return response()->json([
            'status' => true,
            'message' => "{$this->module_title} added successfully",
        ]);
    }

    public function edit($id)
    {
        $pharmacy = Pharmacy::findOrFail($id);
        $page_title = "Edit {$this->module_title}";
        $page_title_icon = 'fa fa-edit';
        $commissions = PharmacyCommission::active()->get();

        return view('pharmacy::backend.pharmacies.create', compact(
            'page_title',
            'page_title_icon',
            'pharmacy',
            'commissions'
        ));
    }

    public function update($id, PharmacyRequest $request)
    {
        $pharmacy = Pharmacy::findOrFail($id);
        $data = $request->validated();
        $pharmacy->update($data);

        if ($request->hasFile('logo')) {
            $pharmacy->clearMediaCollection('logo');
            $pharmacy->addMediaFromRequest('logo')
                ->toMediaCollection('logo');
        }

        return response()->json([
            'status' => true,
            'message' => "{$this->module_title} updated successfully",
        ]);
    }

    public function destroy($id)
    {
        $pharmacy = Pharmacy::findOrFail($id);
        $pharmacy->delete();

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
            Pharmacy::whereIn('id', $ids)->delete();

            return response()->json(['status' => true, 'message' => 'Items deleted successfully']);
        }

        return response()->json(['status' => false, 'message' => 'Invalid action'], 422);
    }

    public function update_status($id, Request $request)
    {
        $pharmacy = Pharmacy::findOrFail($id);
        $pharmacy->update(['is_active' => ! $pharmacy->is_active]);

        return response()->json([
            'status' => true,
            'message' => 'Status updated successfully',
            'is_active' => $pharmacy->is_active,
        ]);
    }
}
