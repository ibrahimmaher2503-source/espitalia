<?php

namespace Modules\Pharmacy\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Pharmacy\Http\Requests\PharmacyProductRequest;
use Modules\Pharmacy\Models\PharmacyBrand;
use Modules\Pharmacy\Models\PharmacyCategory;
use Modules\Pharmacy\Models\PharmacyProduct;
use Modules\Pharmacy\Models\PharmacyProductType;

class PharmacyProductController extends Controller
{
    public function __construct()
    {
        $this->module_title = 'Pharmacy Products';
        $this->module_name = 'pharmacy_products';
        $this->module_path = 'pharmacy::backend.products';

        $this->middleware('can:view_pharmacy_products')->only(['index', 'index_data']);
        $this->middleware('can:add_pharmacy_products')->only(['create', 'store']);
        $this->middleware('can:edit_pharmacy_products')->only(['edit', 'update', 'update_status']);
        $this->middleware('can:delete_pharmacy_products')->only(['destroy', 'bulk_action']);
    }

    public function index()
    {
        $page_title = $this->module_title;
        $page_title_icon = 'fa fa-list';

        return view('pharmacy::backend.products.index', compact('page_title', 'page_title_icon'));
    }

    public function index_data(Request $request)
    {
        $query = PharmacyProduct::with(['category', 'brand']);

        if ($request->has('search') && ! empty($request->search)) {
            $search = str_replace(['%', '_'], ['\%', '\_'], $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name_ar', 'like', "%$search%")
                    ->orWhere('name_en', 'like', "%$search%");
            });
        }

        $total = $query->count();
        $perPage = min((int) $request->get('per_page', 10), 50);
        $products = $query->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json([
            'data' => $products->items(),
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
        ]);
    }

    public function create()
    {
        $page_title = "Add {$this->module_title}";
        $page_title_icon = 'fa fa-plus';
        $categories = PharmacyCategory::where('is_active', true)->get();
        $brands = PharmacyBrand::where('is_active', true)->get();
        $productTypes = PharmacyProductType::all();

        return view('pharmacy::backend.products.create', compact(
            'page_title',
            'page_title_icon',
            'categories',
            'brands',
            'productTypes'
        ));
    }

    public function store(PharmacyProductRequest $request)
    {
        $data = $request->validated();
        $data['description'] = $request->description_en ?? $request->description_ar;
        $product = PharmacyProduct::create($data);

        if ($request->hasFile('image')) {
            $product->addMediaFromRequest('image')
                ->toMediaCollection('product_image');
        }

        return response()->json([
            'status' => true,
            'message' => "{$this->module_title} added successfully",
        ]);
    }

    public function edit($id)
    {
        $product = PharmacyProduct::findOrFail($id);
        $page_title = "Edit {$this->module_title}";
        $page_title_icon = 'fa fa-edit';
        $categories = PharmacyCategory::where('is_active', true)->get();
        $brands = PharmacyBrand::where('is_active', true)->get();
        $productTypes = PharmacyProductType::all();

        return view('pharmacy::backend.products.create', compact(
            'page_title',
            'page_title_icon',
            'product',
            'categories',
            'brands',
            'productTypes'
        ));
    }

    public function update($id, PharmacyProductRequest $request)
    {
        $product = PharmacyProduct::findOrFail($id);
        $data = $request->validated();
        $data['description'] = $request->description_en ?? $request->description_ar;
        $product->update($data);

        if ($request->hasFile('image')) {
            $product->clearMediaCollection('product_image');
            $product->addMediaFromRequest('image')
                ->toMediaCollection('product_image');
        }

        return response()->json([
            'status' => true,
            'message' => "{$this->module_title} updated successfully",
        ]);
    }

    public function destroy($id)
    {
        $product = PharmacyProduct::findOrFail($id);
        $product->delete();

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
            PharmacyProduct::whereIn('id', $ids)->delete();

            return response()->json(['status' => true, 'message' => 'Items deleted successfully']);
        }

        return response()->json(['status' => false, 'message' => 'Invalid action'], 422);
    }

    public function update_status($id, Request $request)
    {
        $product = PharmacyProduct::findOrFail($id);
        $product->update(['is_active' => ! $product->is_active]);

        return response()->json([
            'status' => true,
            'message' => 'Status updated successfully',
            'is_active' => $product->is_active,
        ]);
    }
}
