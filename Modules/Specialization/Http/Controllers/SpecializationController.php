<?php

namespace Modules\Specialization\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Specialization\Models\Specialization;
use Yajra\DataTables\DataTables;

class SpecializationController extends Controller
{
    public function __construct()
    {
        $this->module_title = __('specialization.lbl_specializations');
        $this->module_name = 'specializations';
    }

    public function index(Request $request)
    {
        $filter = [
            'status' => $request->status,
        ];

        $module_action = 'List';
        $module_title = $this->module_title;
        $module_name = $this->module_name;

        return view('specialization::backend.specializations.index', compact('module_action', 'module_title', 'module_name', 'filter'));
    }

    public function index_data(DataTables $datatable, Request $request)
    {
        $query = Specialization::query()->with('parent')->withTrashed();

        $filter = $request->filter;

        if (isset($filter)) {
            if (isset($filter['column_status'])) {
                $query->where('status', $filter['column_status']);
            }
        }

        return $datatable->eloquent($query)
            ->addColumn('check', function ($data) {
                return '<input type="checkbox" class="form-check-input select-table-row" id="datatable-row-'.$data->id.'" name="datatable_ids[]" value="'.$data->id.'" data-type="specializations" onclick="dataTableRowCheck('.$data->id.',this)">';
            })
            ->editColumn('name_en', function ($data) {
                $name = e($data->name);
                if ($data->icon) {
                    $name = '<i class="'.e($data->icon).' me-1"></i>'.$name;
                }

                return $name;
            })
            ->addColumn('parent_name', function ($data) {
                return $data->parent ? e($data->parent->name) : '—';
            })
            ->editColumn('status', function ($row) {
                $checked = $row->status ? 'checked="checked"' : '';
                $disabled = $row->trashed() ? 'disabled' : '';

                return '
                <div class="form-check form-switch">
                    <input type="checkbox" data-url="'.route('backend.specializations.update_status', $row->id).'" data-token="'.csrf_token().'" class="switch-status-change form-check-input" id="datatable-row-'.$row->id.'" name="status" value="'.$row->id.'" '.$checked.' '.$disabled.'>
                </div>';
            })
            ->addColumn('show_on_home', function ($row) {
                $checked = $row->show_on_home ? 'checked="checked"' : '';
                $disabled = $row->trashed() ? 'disabled' : '';

                return '
                <div class="form-check form-switch">
                    <input type="checkbox" data-url="'.route('backend.specializations.update_home_display', $row->id).'" data-token="'.csrf_token().'" class="switch-status-change form-check-input" '.$checked.' '.$disabled.'>
                </div>';
            })
            ->addColumn('display_order', fn ($row) => $row->display_order)
            ->editColumn('updated_at', fn ($data) => formatUpdatedAt($data->updated_at))
            ->addColumn('action', function ($data) {
                return view('specialization::backend.specializations.datatable.action_column', compact('data'));
            })
            ->rawColumns(['check', 'name_en', 'status', 'show_on_home', 'action'])
            ->orderColumns(['id'], '-:column $1')
            ->make(true);
    }

    public function create()
    {
        $module_title = __('specialization.lbl_add');
        $specializations = Specialization::active()->orderBy('name_en')->get();

        return view('specialization::backend.specializations.create', compact('module_title', 'specializations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'parent_id' => 'nullable|exists:specializations,id',
            'icon' => 'nullable|string|max:255',
            'icon_image' => 'nullable|image|mimes:png,svg,jpeg,jpg|max:1024',
            'status' => 'required|in:0,1',
            'show_on_home' => 'nullable|in:0,1',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $data = $request->only([
            'name_ar', 'name_en', 'description_ar', 'description_en',
            'parent_id', 'icon', 'status', 'show_on_home', 'display_order',
        ]);

        if ($request->hasFile('icon_image')) {
            $data['icon_image'] = $request->file('icon_image')->store('specializations', 'public');
        }

        Specialization::create($data);

        $message = __('messages.create_form', ['form' => __('specialization.lbl_specialization')]);

        return redirect()->route('backend.specializations.index')->with('success', $message);
    }

    public function edit($id)
    {
        $data = Specialization::findOrFail($id);
        $module_title = __('specialization.lbl_edit');
        $specializations = Specialization::active()
            ->where('id', '!=', $id)
            ->orderBy('name_en')
            ->get();

        return view('specialization::backend.specializations.create', compact('data', 'module_title', 'specializations'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'parent_id' => 'nullable|exists:specializations,id',
            'icon' => 'nullable|string|max:255',
            'icon_image' => 'nullable|image|mimes:png,svg,jpeg,jpg|max:1024',
            'status' => 'required|in:0,1',
            'show_on_home' => 'nullable|in:0,1',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $specialization = Specialization::findOrFail($id);

        $data = $request->only([
            'name_ar', 'name_en', 'description_ar', 'description_en',
            'parent_id', 'icon', 'status', 'show_on_home', 'display_order',
        ]);

        if ($request->hasFile('icon_image')) {
            if ($specialization->icon_image) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($specialization->icon_image);
            }
            $data['icon_image'] = $request->file('icon_image')->store('specializations', 'public');
        }

        $specialization->update($data);

        $message = __('messages.update_form', ['form' => __('specialization.lbl_specialization')]);

        return redirect()->route('backend.specializations.index')->with('success', $message);
    }

    public function destroy($id)
    {
        $specialization = Specialization::findOrFail($id);

        if ($specialization->children()->where('status', 1)->exists()) {
            return response()->json([
                'status' => false,
                'message' => __('specialization.lbl_delete_has_children'),
            ], 422);
        }

        $specialization->delete();

        $message = __('messages.delete_form', ['form' => __('specialization.lbl_specialization')]);

        return response()->json(['message' => $message, 'status' => true], 200);
    }

    public function update_status(Request $request, $id)
    {
        $specialization = Specialization::findOrFail($id);
        $specialization->update(['status' => $request->status]);

        return response()->json(['status' => true, 'message' => __('messages.status_updated')]);
    }

    public function update_home_display(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:0,1']);

        $specialization = Specialization::findOrFail($id);
        $newValue = (bool) $request->status;

        if ($newValue && ! $specialization->show_on_home) {
            $currentCount = Specialization::where('show_on_home', true)->count();
            if ($currentCount >= 8) {
                return response()->json([
                    'status' => false,
                    'message' => __('specialization.lbl_max_home_reached'),
                ], 422);
            }
        }

        $specialization->update(['show_on_home' => $newValue]);

        return response()->json(['status' => true, 'message' => __('messages.status_updated')]);
    }

    public function bulk_action(Request $request)
    {
        $ids = explode(',', $request->rowIds);
        $actionType = $request->action_type;
        $message = __('messages.bulk_update');

        switch ($actionType) {
            case 'change-status':
                Specialization::whereIn('id', $ids)->update(['status' => $request->status]);
                $message = __('messages.status_updated');
                break;
            case 'delete':
                $hasChildren = Specialization::whereIn('parent_id', $ids)->where('status', 1)->exists();
                if ($hasChildren) {
                    return response()->json([
                        'status' => false,
                        'message' => __('specialization.lbl_delete_has_children'),
                    ]);
                }
                Specialization::whereIn('id', $ids)->delete();
                $message = __('messages.delete_form', ['form' => __('specialization.lbl_specialization')]);
                break;
            case 'restore':
                Specialization::whereIn('id', $ids)->restore();
                $message = __('messages.restore_form', ['form' => __('specialization.lbl_specialization')]);
                break;
            case 'permanently-delete':
                Specialization::whereIn('id', $ids)->forceDelete();
                $message = __('messages.permanent_delete_form', ['form' => __('specialization.lbl_specialization')]);
                break;
            default:
                return response()->json(['status' => false, 'message' => __('messages.invalid_action')]);
        }

        return response()->json(['status' => true, 'message' => $message]);
    }

    public function restore($id)
    {
        $data = Specialization::withTrashed()->findOrFail($id);
        $data->restore();

        return response()->json(['message' => __('messages.restore_form', ['form' => __('specialization.lbl_specialization')]), 'status' => true], 200);
    }

    public function forceDelete($id)
    {
        $data = Specialization::withTrashed()->findOrFail($id);
        $data->forceDelete();

        return response()->json(['message' => __('messages.permanent_delete_form', ['form' => __('specialization.lbl_specialization')]), 'status' => true], 200);
    }

    public function index_list(Request $request)
    {
        $term = trim($request->q);

        $query = Specialization::active();

        if (! empty($term)) {
            $query->where(function ($q) use ($term) {
                $q->where('name_ar', 'LIKE', "%{$term}%")
                    ->orWhere('name_en', 'LIKE', "%{$term}%");
            });
        }

        $data = $query->orderBy('name_en')->get()->map(fn ($s) => [
            'id' => $s->id,
            'name' => $s->name,
        ]);

        return response()->json(['status' => true, 'data' => $data]);
    }
}
