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
     * Get brands with products in a subcategory
     */
    public function brands(Request $request)
    {
        $request->validate([
            'sub_category_id' => 'required|exists:pharmacy_categories,id',
        ]);

        $brands = PharmacyBrand::where('is_active', true)
            ->whereHas('products', function ($q) use ($request) {
                $q->where('sub_category_id', $request->sub_category_id)
                    ->where('is_active', true)
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
     * Get product types with products in a subcategory
     */
    public function productTypes(Request $request)
    {
        $request->validate([
            'sub_category_id' => 'required|exists:pharmacy_categories,id',
        ]);

        $types = PharmacyProductType::whereHas('products', function ($q) use ($request) {
            $q->where('sub_category_id', $request->sub_category_id)
                ->where('is_active', true)
                ->whereHas('inventories', function ($inv) {
                    $inv->where('stock_qty', '>', 0)
                        ->where('is_available', true);
                });
        })->get();

        return response()->json([
            'status' => true,
            'data' => PharmacyProductTypeResource::collection($types),
        ]);
    }
}
