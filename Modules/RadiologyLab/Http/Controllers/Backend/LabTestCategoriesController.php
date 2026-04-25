<?php

namespace Modules\RadiologyLab\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\RadiologyLab\Http\Requests\LabTestCategoryFormRequest;
use Modules\RadiologyLab\Models\LabTestCategory;
use Yajra\DataTables\DataTables;

class LabTestCategoriesController extends Controller
{
    public function __construct()
    {
        $this->module_title = 'radiology_lab.category_plural';
        $this->module_name = 'lab-test-categories';
        $this->module_icon = 'ph ph-folders';

        view()->share([
            'module_title' => $this->module_title,
            'module_icon' => $this->module_icon,
            'module_name' => $this->module_name,
        ]);

        $this->middleware(['permission:view_lab_test_categories'])->only('index', 'index_data');
        $this->middleware(['permission:add_lab_test_categories'])->only('create', 'store');
        $this->middleware(['permission:edit_lab_test_categories'])->only('update_status');
        $this->middleware(['permission:delete_lab_test_categories'])->only('destroy', 'action');
    }

    public function index(Request $request)
    {
        $filter = [
            'status' => $request->status,
        ];
        $module_name = $this->module_name;
        $pageTitle = trans('radiology_lab.category_plural');
        $assets = ['datatable'];

        return view('radiologylab::backend.categories.index', compact('pageTitle', 'module_name', 'assets', 'filter'));
    }

    public function index_data(DataTables $datatable, Request $request)
    {
        try {
            $module_name = $this->module_name;
            $query = LabTestCategory::withCount('labTests');

            if ($request->has('filter') && isset($request->filter['column_status'])) {
                $query->where('status', $request->filter['column_status']);
            }

            if (auth()->user()->hasAnyRole(['admin', 'demo_admin'])) {
                $query->withTrashed();
            }

            return $datatable->eloquent($query)
                ->addColumn('check', function ($data) {
                    return '<input type="checkbox" class="form-check-input select-table-row" id="datatable-row-' . $data->id . '" name="datatable_ids[]" value="' . $data->id . '" data-type="lab-test-category" onclick="dataTableRowCheck(' . $data->id . ',this)">';
                })
                ->editColumn('name', function ($data) {
                    $name = $data->name;
                    if (is_string($name) && json_decode($name) !== null) {
                        $decoded = json_decode($name, true);
                        $locale = app()->getLocale();
                        $name = $decoded[$locale] ?? $decoded['en'] ?? reset($decoded) ?: $name;
                    }
                    return e($name);
                })
                ->editColumn('icon', function ($data) {
                    if ($data->icon) {
                        return '<i class="' . e($data->icon) . ' fs-5"></i> <code class="small">' . e($data->icon) . '</code>';
                    }
                    return '-';
                })
                ->addColumn('tests_count', function ($data) {
                    return '<span class="badge bg-primary">' . $data->lab_tests_count . '</span>';
                })
                ->editColumn('status', function ($row) {
                    $checked = '';
                    if ($row->status) {
                        $checked = 'checked="checked"';
                    }
                    return '
                        <div class="form-check form-switch ">
                            <input type="checkbox" data-url="' . route('backend.lab-test-categories.update_status', $row->id) . '" data-token="' . csrf_token() . '" class="switch-status-change form-check-input" id="datatable-row-' . $row->id . '" name="status" value="' . $row->id . '" ' . $checked . '>
                        </div>
                    ';
                })
                ->editColumn('updated_at', function ($data) {
                    $diff = Carbon::now()->diffInHours($data->updated_at);
                    if ($diff < 25) {
                        return $data->updated_at->diffForHumans();
                    } else {
                        return $data->updated_at->isoFormat('llll');
                    }
                })
                ->addColumn('action', function ($data) use ($module_name) {
                    $category = $data;
                    return view('radiologylab::backend.categories.datatable.action_column', compact('category', 'module_name'))->render();
                })
                ->addIndexColumn()
                ->rawColumns(['check', 'icon', 'tests_count', 'status', 'action'])
                ->toJson();
        } catch (\Exception $e) {
            \Log::error('LabTestCategories index_data error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => __('messages.something_went_wrong')], 500);
        }
    }

    public function create(Request $request)
    {
        $id = $request->id;
        $data = LabTestCategory::find($id);

        $pageTitle = trans('messages.add_button_form', ['form' => trans('radiology_lab.category_singular')]);

        if ($data) {
            $pageTitle = trans('messages.update_form_title', ['form' => trans('radiology_lab.category_singular')]);
        } else {
            $data = new LabTestCategory;
        }

        return view('radiologylab::backend.categories.create', compact('pageTitle', 'data'));
    }

    public function store(LabTestCategoryFormRequest $request)
    {
        $categoryId = $request->id;

        if ($categoryId) {
            if (!auth()->user()->can('edit_lab_test_categories')) {
                abort(403, __('messages.permission_denied'));
            }

            $category = LabTestCategory::findOrFail($categoryId);

            $category->update([
                'name' => $request->name,
                'description' => $request->description,
                'icon' => $request->icon,
                'display_order' => $request->display_order ?? 0,
                'status' => $request->status ?? 1,
            ]);

            $message = __('radiology_lab.category_update_success');
        } else {
            LabTestCategory::create([
                'name' => $request->name,
                'description' => $request->description,
                'icon' => $request->icon,
                'display_order' => $request->display_order ?? 0,
                'status' => $request->status ?? 1,
            ]);

            $message = __('radiology_lab.category_save_success');
        }

        return redirect()->route('backend.lab-test-categories.index')->withSuccess($message);
    }

    public function destroy($id)
    {
        $category = LabTestCategory::find($id);
        if ($category) {
            $category->delete();
            $msg = __('radiology_lab.category_delete_success');
        } else {
            $msg = __('messages.msg_fail_to_delete', ['name' => __('radiology_lab.category_singular')]);
        }

        return comman_custom_response(['message' => $msg, 'status' => $category ? true : false]);
    }

    public function action(Request $request)
    {
        $id = $request->id;
        $category = LabTestCategory::withTrashed()->where('id', $id)->first();

        if (!$category) {
            return response()->json(['message' => __('messages.not_found'), 'status' => false], 404);
        }

        $msg = __('messages.not_found_entry', ['name' => __('radiology_lab.category_singular')]);

        if ($request->type === 'restore') {
            $category->restore();
            $msg = __('messages.restore_form');
        } elseif ($request->type === 'forcedelete') {
            $category->forceDelete();
            $msg = __('messages.permanent_delete');
        }

        return comman_custom_response(['message' => $msg, 'status' => true]);
    }

    public function update_status(Request $request, LabTestCategory $id)
    {
        $request->validate(['status' => 'required|in:0,1']);

        $id->update(['status' => $request->status]);

        return response()->json(['status' => true, 'message' => __('radiology_lab.status_update_success')]);
    }

    public function bulk_action(Request $request)
    {
        $request->validate([
            'rowIds' => 'required|string',
            'action_type' => 'required|string|in:change-status,delete,restore,permanently-delete',
            'status' => 'required_if:action_type,change-status|in:0,1',
        ]);

        $ids = array_filter(array_map('intval', explode(',', $request->rowIds)));
        $actionType = $request->action_type;
        $message = __('messages.bulk_update');

        if ($actionType === 'change-status' && !auth()->user()->can('edit_lab_test_categories')) {
            return response()->json(['status' => false, 'message' => __('messages.permission_denied')], 403);
        }
        if (in_array($actionType, ['delete', 'permanently-delete', 'restore']) && !auth()->user()->can('delete_lab_test_categories')) {
            return response()->json(['status' => false, 'message' => __('messages.permission_denied')], 403);
        }

        switch ($actionType) {
            case 'change-status':
                LabTestCategory::whereIn('id', $ids)->update(['status' => (int) $request->status]);
                $message = __('messages.bulk_service_update');
                break;

            case 'delete':
                if (config('app.demo_mode')) {
                    return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 200);
                }
                LabTestCategory::whereIn('id', $ids)->delete();
                $message = __('messages.bulk_service_delete');
                break;

            case 'restore':
                LabTestCategory::withTrashed()->whereIn('id', $ids)->restore();
                $message = __('messages.bulk_service_restore');
                break;

            case 'permanently-delete':
                if (config('app.demo_mode')) {
                    return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 200);
                }
                LabTestCategory::withTrashed()->whereIn('id', $ids)->forceDelete();
                $message = __('messages.bulk_service_permanently_deleted');
                break;

            default:
                return response()->json(['status' => false, 'message' => __('service_providers.invalid_action')]);
        }

        return response()->json(['status' => true, 'message' => $message]);
    }
}
