<?php

namespace Modules\Pharmacy\Http\Controllers\API;

use Illuminate\Routing\Controller;
use Modules\Pharmacy\Models\PharmacyCategory;
use Modules\Pharmacy\Transformers\PharmacyCategoryResource;

class CategoryApiController extends Controller
{
    /**
     * Get all active root categories
     */
    public function index()
    {
        $categories = PharmacyCategory::whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'status' => true,
            'data' => PharmacyCategoryResource::collection($categories),
        ]);
    }

    /**
     * Get active subcategories for a parent category
     */
    public function children($id)
    {
        $parent = PharmacyCategory::where('id', $id)
            ->where('is_active', true)
            ->firstOrFail();

        $children = PharmacyCategory::where('parent_id', $id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'status' => true,
            'data' => PharmacyCategoryResource::collection($children),
        ]);
    }
}
