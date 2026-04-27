<?php

namespace Modules\Pharmacy\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Pharmacy\Models\PharmacyBrand;
use Modules\Pharmacy\Models\PharmacyProductType;
use Modules\Pharmacy\Transformers\PharmacyBrandResource;
use Modules\Pharmacy\Transformers\PharmacyProductTypeResource;

class FilterApiController extends Controller
{
    /**
     * Get brands with products in a subcategory (or all brands when no subcategory given)
     */
    public function brands(Request $request)
    {
        $request->validate([
            'sub_category_id' => 'nullable|exists:pharmacy_categories,id',
        ]);

        $brands = PharmacyBrand::where('is_active', true)
            ->whereHas('products', function ($q) use ($request) {
                if ($request->filled('sub_category_id')) {
                    $q->where('sub_category_id', $request->sub_category_id);
                }
                $q->where('is_active', true)
                    ->whereHas('inventories', function ($inv) {
                        $inv->where('stock_qty', '>', 0)
                            ->where('is_available', true);
                    });
            })
            ->get();

        return response()->json([
            'status' => true,
            'data' => PharmacyBrandResource::collection($brands),
        ]);
    }

    /**
     * Get product types with products in a subcategory (or all types when no subcategory given)
     */
    public function productTypes(Request $request)
    {
        $request->validate([
            'sub_category_id' => 'nullable|exists:pharmacy_categories,id',
        ]);

        $query = PharmacyProductType::whereHas('products', function ($q) use ($request) {
            if ($request->filled('sub_category_id')) {
                $q->where('sub_category_id', $request->sub_category_id);
            }
            $q->where('is_active', true)
                ->whereHas('inventories', function ($inv) {
                    $inv->where('stock_qty', '>', 0)
                        ->where('is_available', true);
                });
        });

        return response()->json([
            'status' => true,
            'data' => PharmacyProductTypeResource::collection($query->get()),
        ]);
    }
}
