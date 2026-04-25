<?php

namespace Modules\RadiologyLab\Http\Controllers\Backend\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\RadiologyLab\Models\LabTest;
use Modules\RadiologyLab\Models\LabTestCategory;
use Modules\RadiologyLab\Transformers\LabTestCategoryResource;
use Modules\RadiologyLab\Transformers\LabTestResource;

class LabTestsApiController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = LabTest::with('category')->active();

            if ($request->has('category_id') && $request->category_id !== '') {
                $query->where('category_id', $request->category_id);
            }

            if ($request->has('department') && $request->department !== '') {
                $query->where('department', $request->department);
            }

            if ($request->has('search') && $request->search !== '') {
                $search = str_replace(['%', '_'], ['\%', '\_'], $request->search);
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('code', 'LIKE', "%{$search}%")
                      ->orWhere('description', 'LIKE', "%{$search}%");
                });
            }

            $perPage = min((int) ($request->per_page ?? 15), 100);
            $tests = $query->orderBy('name', 'asc')->paginate($perPage);

            return LabTestResource::collection($tests)->additional(['status' => true]);
        } catch (\Exception $e) {
            \Log::error('RadiologyLab API error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => __('messages.something_wrong')], 500);
        }
    }

    public function show($id)
    {
        try {
            $test = LabTest::with('category')->active()->findOrFail($id);

            return response()->json([
                'status' => true,
                'data' => new LabTestResource($test),
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => __('radiology_lab.test_not_found')], 404);
        }
    }

    public function categories(Request $request)
    {
        try {
            $categories = LabTestCategory::withCount('labTests')
                ->active()
                ->ordered()
                ->get();

            return response()->json([
                'status' => true,
                'data' => LabTestCategoryResource::collection($categories),
            ]);
        } catch (\Exception $e) {
            \Log::error('RadiologyLab API error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => __('messages.something_wrong')], 500);
        }
    }
}
