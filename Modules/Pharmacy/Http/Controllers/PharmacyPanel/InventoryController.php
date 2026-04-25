<?php

namespace Modules\Pharmacy\Http\Controllers\PharmacyPanel;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Pharmacy\Http\Requests\InventoryRequest;
use Modules\Pharmacy\Models\PharmacyInventory;
use Modules\Pharmacy\Models\PharmacyProduct;

class InventoryController extends Controller
{
    public function __construct()
    {
        $this->module_title = 'Inventory';
        $this->module_name = 'inventory';
        $this->module_path = 'pharmacy::backend.pharmacy-panel.inventory';

        $this->middleware('role:pharmacy');
        $this->middleware('can:view_pharmacy_inventory')->only(['index', 'index_data']);
        $this->middleware('can:add_pharmacy_inventory')->only(['create', 'store']);
        $this->middleware('can:edit_pharmacy_inventory')->only(['edit', 'update', 'update_status']);
        $this->middleware('can:delete_pharmacy_inventory')->only(['destroy']);
    }

    public function index()
    {
        $page_title = $this->module_title;
        $page_title_icon = 'fa fa-list';

        return view('pharmacy::backend.pharmacy-panel.inventory.index', compact('page_title', 'page_title_icon'));
    }

    public function index_data(Request $request)
    {
        $pharmacyId = auth()->user()->pharmacy_id;
        $query = PharmacyInventory::where('pharmacy_id', $pharmacyId)
            ->with('product');

        if ($request->has('search') && ! empty($request->search)) {
            $search = $request->search;
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name_ar', 'like', "%$search%")
                    ->orWhere('name_en', 'like', "%$search%");
            });
        }

        $total = $query->count();
        $perPage = $request->get('per_page', 10);
        $inventory = $query->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json([
            'data' => $inventory->items(),
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $inventory->currentPage(),
            'last_page' => $inventory->lastPage(),
        ]);
    }

    public function create()
    {
        $page_title = "Add {$this->module_title}";
        $page_title_icon = 'fa fa-plus';
        $pharmacyId = auth()->user()->pharmacy_id;

        // Get products not already in inventory
        $addedProductIds = PharmacyInventory::where('pharmacy_id', $pharmacyId)
            ->pluck('product_id')
            ->toArray();

        $products = PharmacyProduct::where('is_active', true)
            ->whereNotIn('id', $addedProductIds)
            ->get();

        return view('pharmacy::backend.pharmacy-panel.inventory.create', compact(
            'page_title',
            'page_title_icon',
            'products'
        ));
    }

    public function store(InventoryRequest $request)
    {
        $data = $request->validated();
        $data['pharmacy_id'] = auth()->user()->pharmacy_id;

        PharmacyInventory::create($data);

        return response()->json([
            'status' => true,
            'message' => "{$this->module_title} added successfully",
        ]);
    }

    public function edit($id)
    {
        $pharmacyId = auth()->user()->pharmacy_id;
        $inventory = PharmacyInventory::where('id', $id)
            ->where('pharmacy_id', $pharmacyId)
            ->with('product')
            ->firstOrFail();

        $page_title = "Edit {$this->module_title}";
        $page_title_icon = 'fa fa-edit';

        return view('pharmacy::backend.pharmacy-panel.inventory.create', compact(
            'page_title',
            'page_title_icon',
            'inventory'
        ));
    }

    public function update($id, InventoryRequest $request)
    {
        $pharmacyId = auth()->user()->pharmacy_id;
        $inventory = PharmacyInventory::where('id', $id)
            ->where('pharmacy_id', $pharmacyId)
            ->firstOrFail();

        $inventory->update($request->validated());

        return response()->json([
            'status' => true,
            'message' => "{$this->module_title} updated successfully",
        ]);
    }

    public function destroy($id)
    {
        $pharmacyId = auth()->user()->pharmacy_id;
        $inventory = PharmacyInventory::where('id', $id)
            ->where('pharmacy_id', $pharmacyId)
            ->firstOrFail();

        $inventory->delete();

        return response()->json([
            'status' => true,
            'message' => "{$this->module_title} deleted successfully",
        ]);
    }

    public function update_status($id, Request $request)
    {
        $pharmacyId = auth()->user()->pharmacy_id;
        $inventory = PharmacyInventory::where('id', $id)
            ->where('pharmacy_id', $pharmacyId)
            ->firstOrFail();

        $inventory->update(['is_available' => ! $inventory->is_available]);

        return response()->json([
            'status' => true,
            'message' => 'Availability toggled successfully',
            'is_available' => $inventory->is_available,
        ]);
    }
}
