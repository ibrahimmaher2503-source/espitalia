<?php

namespace Modules\Pharmacy\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Pharmacy\Http\Requests\PharmacyCategoryRequest;
use Modules\Pharmacy\Models\PharmacyCategory;

class PharmacyCategoryController extends Controller
{
    public function __construct()
    {
        $this->module_title = 'Pharmacy Categories';
        $this->module_name = 'pharmacy_categories';
        $this->module_path = 'pharmacy::backend.categories';

        $this->middleware('can:view_pharmacy_categories')->only(['index', 'index_data']);
        $this->middleware('can:add_pharmacy_categories')->only(['create', 'store']);
        $this->middleware('can:edit_pharmacy_categories')->only(['edit', 'update', 'update_status']);
        $this->middleware('can:delete_pharmacy_categories')->only(['destroy', 'bulk_action']);
    }

    public function index()
    {
        $page_title = $this->module_title;
        $page_title_icon = 'fa fa-list';

        return view('pharmacy::backend.categories.index', compact('page_title', 'page_title_icon'));
    }

    public function index_data(Request $request)
    {
        $query = PharmacyCategory::query();

        if ($request->has('search') && ! empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name_ar', 'like', "%$search%")
                    ->orWhere('name_en', 'like', "%$search%");
            });
        }

        $total = $query->count();
        $perPage = $request->get('per_page', 10);
        $categories = $query->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json([
            'data' => $categories->items(),
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $categories->currentPage(),
            'last_page' => $categories->lastPage(),
        ]);
    }

    public function create()
    {
        $page_title = "Add {$this->module_title}";
        $page_title_icon = 'fa fa-plus';
        $parents = PharmacyCategory::whereNull('parent_id')->where('is_active', true)->get();

        return view('pharmacy::backend.categories.create', compact('page_title', 'page_title_icon', 'parents'));
    }

    public function store(PharmacyCategoryRequest $request)
    {
        $data = $request->validated();
        $category = PharmacyCategory::create($data);

        if ($request->hasFile('image')) {
            $category->addMediaFromRequest('image')
                ->toMediaCollection('category_image');
        }

        return response()->json([
            'status' => true,
            'message' => "{$this->module_title} added successfully",
        ]);
    }

    public function edit($id)
    {
        $category = PharmacyCategory::findOrFail($id);
        $page_title = "Edit {$this->module_title}";
        $page_title_icon = 'fa fa-edit';
        $parents = PharmacyCategory::whereNull('parent_id')
            ->where('is_active', true)
            ->where('id', '!=', $id)
            ->get();

        return view('pharmacy::backend.categories.create', compact('page_title', 'page_title_icon', 'category', 'parents'));
    }

    public function update($id, PharmacyCategoryRequest $request)
    {
        $category = PharmacyCategory::findOrFail($id);
        $data = $request->validated();
        $category->update($data);

        if ($request->hasFile('image')) {
            $category->clearMediaCollection('category_image');
            $category->addMediaFromRequest('image')
                ->toMediaCollection('category_image');
        }

        return response()->json([
            'status' => true,
            'message' => "{$this->module_title} updated successfully",
        ]);
    }

    public function destroy($id)
    {
        $category = PharmacyCategory::findOrFail($id);
        $category->delete();

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
            PharmacyCategory::whereIn('id', $ids)->delete();

            return response()->json(['status' => true, 'message' => 'Items deleted successfully']);
        }

        return response()->json(['status' => false, 'message' => 'Invalid action'], 422);
    }

    public function update_status($id, Request $request)
    {
        $category = PharmacyCategory::findOrFail($id);
        $category->update(['is_active' => ! $category->is_active]);

        return response()->json([
            'status' => true,
            'message' => 'Status updated successfully',
            'is_active' => $category->is_active,
        ]);
    }
}
