<?php

namespace Modules\Pharmacy\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Pharmacy\Models\PharmacyProduct;
use Modules\Pharmacy\Transformers\PharmacyProductDetailResource;
use Modules\Pharmacy\Transformers\PharmacyProductResource;

class ProductApiController extends Controller
{
    /**
     * Get paginated list of active products with optional filters
     */
    public function index(Request $request)
    {
        $query = PharmacyProduct::where('is_active', true)
            ->with('brand', 'productType', 'inventories');

        // The Flutter catalogue opens a root category with `category_id` and
        // subcategories with `sub_category_id`; support both contracts.
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('sub_category_id')) {
            $query->where('sub_category_id', $request->sub_category_id);
        }

        // Search by name
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name_ar', 'like', "%$search%")
                    ->orWhere('name_en', 'like', "%$search%");
            });
        }

        // Filter by brands
        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        } elseif ($request->has('brand_ids') && is_array($request->brand_ids)) {
            $query->whereIn('brand_id', $request->brand_ids);
        }

        // Filter by product types
        if ($request->filled('product_type_id')) {
            $query->where('product_type_id', $request->product_type_id);
        } elseif ($request->has('product_type_ids') && is_array($request->product_type_ids)) {
            $query->whereIn('product_type_id', $request->product_type_ids);
        }

        if ($request->filled('prescription_required')) {
            $query->where('requires_prescription', $request->boolean('prescription_required'));
        }

        if ($request->filled('price_min') || $request->filled('price_max')) {
            $query->whereHas('inventories', function ($inventory) use ($request) {
                if ($request->filled('price_min')) {
                    $inventory->where('price', '>=', $request->price_min);
                }
                if ($request->filled('price_max')) {
                    $inventory->where('price', '<=', $request->price_max);
                }
            });
        }

        // Only show products with stock at some pharmacy
        $query->whereHas('inventories', function ($q) {
            $q->where('stock_qty', '>', 0)->where('is_available', true);
        });

        if ($request->input('sort') === 'price_asc') {
            $query->orderByRaw('(select min(price) from pharmacy_inventory where pharmacy_inventory.product_id = pharmacy_products.id and stock_qty > 0 and is_available = 1) asc');
        } elseif ($request->input('sort') === 'price_desc') {
            $query->orderByRaw('(select min(price) from pharmacy_inventory where pharmacy_inventory.product_id = pharmacy_products.id and stock_qty > 0 and is_available = 1) desc');
        } else {
            $query->latest('pharmacy_products.created_at');
        }

        $perPage = min((int) $request->get('per_page', 15), 50);
        $products = $query->paginate($perPage);

        return response()->json([
            'status' => true,
            'data' => PharmacyProductResource::collection($products),
            'meta' => [
                'current_page' => $products->currentPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'last_page' => $products->lastPage(),
            ],
        ]);
    }

    /**
     * Get product detail
     */
    public function show($id)
    {
        $product = PharmacyProduct::where('id', $id)
            ->where('is_active', true)
            ->with('category', 'subcategory', 'brand', 'productType', 'inventories')
            ->firstOrFail();

        return response()->json([
            'status' => true,
            'data' => new PharmacyProductDetailResource($product),
        ]);
    }
}
