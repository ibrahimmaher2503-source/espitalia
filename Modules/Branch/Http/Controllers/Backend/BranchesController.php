<?php

namespace Modules\Branch\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Branch\Models\Branch;
use Modules\Branch\Http\Requests\BranchFormRequest;

class BranchesController extends Controller
{
    protected $module_title;
    protected $module_name;
    protected $module_path;
    protected $module_icon;
    protected $module_model;

    public function __construct()
    {
        $this->module_title = __('branch.title');
        $this->module_name = 'branches';
        $this->module_path = 'branch::backend.branches';
        $this->module_icon = 'ph ph-buildings';
        $this->module_model = Branch::class;
    }

    public function index()
    {
        $module_title = $this->module_title;
        $module_name = $this->module_name;
        $module_path = $this->module_path;
        $module_icon = $this->module_icon;

        return view("{$module_path}.index", compact('module_title', 'module_name', 'module_path', 'module_icon'));
    }

    public function index_data(Request $request)
    {
        $query = Branch::SetRole()
            ->with(['governorate', 'governorateCity', 'vendor'])
            ->withCount('clinics');

        if ($request->has('type') && $request->type != '') {
            $query->where('type', $request->type);
        }

        if ($request->has('is_active') && $request->is_active !== '') {
            $query->where('is_active', $request->is_active);
        }

        return response()->json($query->orderBy('updated_at', 'desc')->paginate($request->input('per_page', 10)));
    }

    public function create()
    {
        $module_title = $this->module_title;
        $module_name = $this->module_name;
        $module_path = $this->module_path;
        $module_icon = $this->module_icon;

        return view("{$module_path}.create", compact('module_title', 'module_name', 'module_path', 'module_icon'));
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

        flash(__('branch.lbl_branch_created'))->success();
        return redirect()->route('backend.branches.index');
    }

    public function edit($id)
    {
        $module_title = $this->module_title;
        $module_name = $this->module_name;
        $module_path = $this->module_path;
        $module_icon = $this->module_icon;

        $data = Branch::SetRole()->findOrFail($id);

        return view("{$module_path}.create", compact('module_title', 'module_name', 'module_path', 'module_icon', 'data'));
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

        flash(__('branch.lbl_branch_updated'))->success();
        return redirect()->route('backend.branches.index');
    }

    public function destroy($id)
    {
        $branch = Branch::SetRole()->findOrFail($id);
        $branch->delete();

        flash(__('branch.lbl_branch_deleted'))->success();
        return redirect()->route('backend.branches.index');
    }

    public function update_status(Request $request, $id)
    {
        $branch = Branch::SetRole()->findOrFail($id);
        $branch->is_active = !$branch->is_active;
        $branch->save();

        return response()->json([
            'status' => true,
            'message' => __('branch.lbl_status_updated'),
        ]);
    }

    public function bulk_action(Request $request)
    {
        $ids = $request->input('ids', []);
        $action = $request->input('action_type', '');

        if (empty($ids)) {
            return response()->json(['status' => false, 'message' => 'No items selected']);
        }

        switch ($action) {
            case 'delete':
                Branch::SetRole()->whereIn('id', $ids)->delete();
                break;
            case 'activate':
                Branch::SetRole()->whereIn('id', $ids)->update(['is_active' => 1]);
                break;
            case 'deactivate':
                Branch::SetRole()->whereIn('id', $ids)->update(['is_active' => 0]);
                break;
        }

        return response()->json([
            'status' => true,
            'message' => __('messages.bulk_action_success'),
        ]);
    }
}
