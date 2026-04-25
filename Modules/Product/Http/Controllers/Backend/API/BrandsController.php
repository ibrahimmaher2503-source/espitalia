<?php

namespace Modules\Product\Http\Controllers\Backend\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Product\Models\Brands;
use Modules\Product\Transformers\BrandResource;

class BrandsController extends Controller
{
    public function product_brand(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $query = Brands::where('status', 1);

        if ($request->filled('brand_id')) {
            $brand = $query->where('id', $request->brand_id)->first();

            return response()->json([
                'status' => true,
                'data' => $brand ? new BrandResource($brand) : null,
                'message' => __('product.brand_list'),
            ], 200);
        }

        $brands = $query->paginate($perPage);

        return response()->json([
            'status' => true,
            'data' => BrandResource::collection($brands),
            'message' => __('product.brand_list'),
        ], 200);
    }
}
