<?php

namespace Modules\Pharmacy\Http\Controllers\PharmacyPanel;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Pharmacy\Http\Requests\ProductBatchRequest;
use Modules\Pharmacy\Models\PharmacyProductBatch;

class BatchController extends Controller
{
    public function __construct()
    {
        $this->middleware(['role:pharmacy']);
        $this->middleware('can:view_pharmacy_batches')->only(['index', 'index_data']);
        $this->middleware('can:add_pharmacy_batches')->only(['create', 'store']);
        $this->middleware('can:edit_pharmacy_batches')->only(['edit', 'update']);
    }

    public function index()
    {
        $page_title = 'Product Batches';
        $page_title_icon = 'fa fa-boxes';

        return view('pharmacy::backend.pharmacy-panel.batches.index', compact('page_title', 'page_title_icon'));
    }

    public function index_data(Request $request)
    {
        $pharmacyId = auth()->user()->pharmacy_id;
        $query = PharmacyProductBatch::with(['inventory.product'])
            ->whereHas('inventory', fn ($q) => $q->where('pharmacy_id', $pharmacyId));

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('batch_number', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('expiry_filter')) {
            match ($request->expiry_filter) {
                'expired' => $query->expired(),
                'expiring_30' => $query->expiringSoon(30),
                'expiring_90' => $query->expiringSoon(90),
                default => null,
            };
        }

        $total = $query->count();
        $perPage = $request->get('per_page', 10);
        $batches = $query->orderBy('expiry_date', 'asc')->paginate($perPage);

        return response()->json([
            'data' => $batches->items(),
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $batches->currentPage(),
            'last_page' => $batches->lastPage(),
        ]);
    }

    public function create()
    {
        $page_title = 'Add Batch';
        $page_title_icon = 'fa fa-plus';

        return view('pharmacy::backend.pharmacy-panel.batches.create', compact('page_title', 'page_title_icon'));
    }

    public function store(ProductBatchRequest $request)
    {
        $batch = PharmacyProductBatch::create($request->validated());

        // Sync inventory stock_qty
        $inventory = $batch->inventory;
        $inventory->update([
            'stock_qty' => $inventory->batches()->where('is_active', true)->sum('quantity'),
        ]);

        return response()->json(['status' => true, 'message' => 'Batch added successfully']);
    }

    public function edit($id)
    {
        $pharmacyId = auth()->user()->pharmacy_id;
        $batch = PharmacyProductBatch::whereHas('inventory', fn ($q) => $q->where('pharmacy_id', $pharmacyId))
            ->findOrFail($id);
        $page_title = 'Edit Batch';
        $page_title_icon = 'fa fa-edit';

        return view('pharmacy::backend.pharmacy-panel.batches.create', compact('page_title', 'page_title_icon', 'batch'));
    }

    public function update($id, ProductBatchRequest $request)
    {
        $pharmacyId = auth()->user()->pharmacy_id;
        $batch = PharmacyProductBatch::whereHas('inventory', fn ($q) => $q->where('pharmacy_id', $pharmacyId))
            ->findOrFail($id);

        $batch->update($request->validated());

        // Sync inventory stock_qty
        $inventory = $batch->inventory;
        $inventory->update([
            'stock_qty' => $inventory->batches()->where('is_active', true)->sum('quantity'),
        ]);

        return response()->json(['status' => true, 'message' => 'Batch updated successfully']);
    }
}
