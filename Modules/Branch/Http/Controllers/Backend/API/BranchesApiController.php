<?php

namespace Modules\Branch\Http\Controllers\Backend\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Branch\Models\Branch;
use Modules\Branch\Transformers\BranchResource;
use Modules\Branch\Http\Requests\BranchFormRequest;

class BranchesApiController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);

        $branches = Branch::SetRole()
            ->with(['governorate', 'governorateCity', 'vendor'])
            ->withCount('clinics');

        if ($request->has('type')) {
            $branches->byType($request->type);
        }

        if ($request->has('is_active')) {
            $branches->where('is_active', $request->is_active);
        }

        if ($request->has('governorate_id')) {
            $branches->where('governorate_id', $request->governorate_id);
        }

        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $branches->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%")
                  ->orWhere('phone', 'like', "%{$searchTerm}%");
            });
        }

        $branches = $branches->orderBy('updated_at', 'desc')->paginate($perPage);

        return response()->json([
            'status' => true,
            'data' => BranchResource::collection($branches),
            'message' => __('branch.lbl_branch_list'),
        ]);
    }

    public function show($id)
    {
        $branch = Branch::SetRole()
            ->with(['governorate', 'governorateCity', 'vendor', 'manager', 'clinics'])
            ->withCount('clinics')
            ->findOrFail($id);

        return response()->json([
            'status' => true,
            'data' => new BranchResource($branch),
            'message' => '',
        ]);
    }

    public function store(BranchFormRequest $request)
    {
        $branch = new Branch();
        $branch->fill($request->validated());
        $branch->vendor_id = $request->vendor_id ?? auth()->id();
        $branch->save();

        if ($request->hasFile('branch_image')) {
            $branch->addMediaFromRequest('branch_image')->toMediaCollection('branch_image');
        }

        return response()->json([
            'status' => true,
            'data' => new BranchResource($branch->load(['governorate', 'governorateCity', 'vendor'])),
            'message' => __('branch.lbl_branch_created'),
        ], 201);
    }

    public function update(BranchFormRequest $request, $id)
    {
        $branch = Branch::SetRole()->findOrFail($id);
        $branch->fill($request->validated());
        $branch->save();

        if ($request->hasFile('branch_image')) {
            $branch->clearMediaCollection('branch_image');
            $branch->addMediaFromRequest('branch_image')->toMediaCollection('branch_image');
        }

        return response()->json([
            'status' => true,
            'data' => new BranchResource($branch->load(['governorate', 'governorateCity', 'vendor'])),
            'message' => __('branch.lbl_branch_updated'),
        ]);
    }

    public function destroy($id)
    {
        $branch = Branch::SetRole()->findOrFail($id);
        $branch->delete();

        return response()->json([
            'status' => true,
            'data' => null,
            'message' => __('branch.lbl_branch_deleted'),
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $branch = Branch::SetRole()->findOrFail($id);
        $branch->is_active = !$branch->is_active;
        $branch->save();

        return response()->json([
            'status' => true,
            'data' => new BranchResource($branch),
            'message' => __('branch.lbl_status_updated'),
        ]);
    }
}
