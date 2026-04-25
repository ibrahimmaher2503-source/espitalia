<?php

namespace Modules\RadiologyLab\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\RadiologyLab\Http\Requests\LabTestFormRequest;
use Modules\RadiologyLab\Models\LabTest;
use Modules\RadiologyLab\Models\LabTestCategory;
use Yajra\DataTables\DataTables;

class LabTestsController extends Controller
{
    public function __construct()
    {
        $this->module_title = 'radiology_lab.test_plural';
        $this->module_name = 'lab-tests';
        $this->module_icon = 'ph ph-flask';

        view()->share([
            'module_title' => $this->module_title,
            'module_icon' => $this->module_icon,
            'module_name' => $this->module_name,
        ]);

        $this->middleware(['permission:view_lab_tests'])->only('index', 'index_data');
        $this->middleware(['permission:add_lab_tests'])->only('create', 'store');
        $this->middleware(['permission:edit_lab_tests'])->only('update_status');
        $this->middleware(['permission:delete_lab_tests'])->only('destroy', 'action');
        // bulk_action is gated inline — it supports both edit (change-status) and delete actions
    }

    public function index(Request $request)
    {
        $filter = [
            'status' => $request->status,
        ];
        $module_name = $this->module_name;
        $pageTitle = trans('radiology_lab.test_plural');
        $assets = ['datatable'];

        // UI-10: use accessor-aware mapping so multilingual names render correctly
        $categories = LabTestCategory::active()->ordered()->get()->mapWithKeys(fn ($c) => [$c->id => $c->name]);

        return view('radiologylab::backend.tests.index', compact('pageTitle', 'module_name', 'assets', 'filter', 'categories'));
    }

    public function index_data(DataTables $datatable, Request $request)
    {
        try {
            $module_name = $this->module_name;
            $query = LabTest::with('category');

            if ($request->has('filter') && isset($request->filter['column_status'])) {
                $query->where('status', $request->filter['column_status']);
            }

            if ($request->has('filter') && isset($request->filter['category_id']) && $request->filter['category_id'] != '') {
                $query->where('category_id', $request->filter['category_id']);
            }

            if ($request->has('filter') && isset($request->filter['department']) && $request->filter['department'] != '') {
                $query->where('department', $request->filter['department']);
            }

            if (auth()->user()->hasAnyRole(['admin', 'demo_admin'])) {
                $query->withTrashed();
            }

            return $datatable->eloquent($query)
                ->addColumn('check', function ($data) {
                    return '<input type="checkbox" class="form-check-input select-table-row" id="datatable-row-' . $data->id . '" name="datatable_ids[]" value="' . $data->id . '" data-type="lab-test" onclick="dataTableRowCheck(' . $data->id . ',this)">';
                })
                ->editColumn('code', function ($data) {
                    return '<code>' . e($data->code) . '</code>';
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
                ->addColumn('category_name', function ($data) {
                    if (!$data->category) {
                        return '-';
                    }
                    $name = $data->category->name;
                    if (is_string($name) && json_decode($name) !== null) {
                        $decoded = json_decode($name, true);
                        $locale = app()->getLocale();
                        $name = $decoded[$locale] ?? $decoded['en'] ?? reset($decoded) ?: $name;
                    }
                    return e($name);
                })
                ->filterColumn('category_name', function ($query, $keyword) {
                    $escaped = str_replace(['%', '_'], ['\%', '\_'], $keyword);
                    $query->whereHas('category', function ($q) use ($escaped) {
                        $q->where('name', 'LIKE', "%$escaped%");
                    });
                })
                ->addColumn('department_badge', function ($data) {
                    return $data->department_badge;
                })
                ->editColumn('sample_type', function ($data) {
                    return $data->sample_type_label;
                })
                ->addColumn('default_price', function ($data) {
                    return $data->formatted_price;
                })
                ->editColumn('status', function ($row) {
                    $checked = '';
                    if ($row->status) {
                        $checked = 'checked="checked"';
                    }
                    return '
                        <div class="form-check form-switch ">
                            <input type="checkbox" data-url="' . route('backend.lab-tests.update_status', $row->id) . '" data-token="' . csrf_token() . '" class="switch-status-change form-check-input" id="datatable-row-' . $row->id . '" name="status" value="' . $row->id . '" ' . $checked . '>
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
                    $test = $data;
                    return view('radiologylab::backend.tests.datatable.action_column', compact('test', 'module_name'))->render();
                })
                ->addIndexColumn()
                ->rawColumns(['check', 'code', 'department_badge', 'status', 'action'])
                ->toJson();
        } catch (\Exception $e) {
            \Log::error('LabTests index_data error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => __('messages.something_went_wrong')], 500);
        }
    }

    public function create(Request $request)
    {
        $id = $request->id;
        $data = LabTest::find($id);

        $pageTitle = trans('messages.add_button_form', ['form' => trans('radiology_lab.test_singular')]);

        if ($data) {
            $pageTitle = trans('messages.update_form_title', ['form' => trans('radiology_lab.test_singular')]);
        } else {
            $data = new LabTest;
        }

        $categories = LabTestCategory::active()->ordered()->get()->mapWithKeys(fn ($c) => [$c->id => $c->name]);

        return view('radiologylab::backend.tests.create', compact('pageTitle', 'data', 'categories'));
    }

    public function store(LabTestFormRequest $request)
    {
        $testId = $request->id;

        if ($testId) {
            if (!auth()->user()->can('edit_lab_tests')) {
                abort(403, __('messages.permission_denied'));
            }

            $test = LabTest::findOrFail($testId);

            $test->update([
                'name' => $request->name,
                'code' => $request->code,
                'category_id' => $request->category_id,
                'department' => $request->department,
                'sample_type' => $request->sample_type,
                'description' => $request->description,
                'preparation_instructions' => $request->preparation_instructions,
                'default_price' => $request->default_price,
                'turnaround_time' => $request->turnaround_time,
                'status' => $request->status ?? 1,
            ]);

            $message = __('radiology_lab.test_update_success');
        } else {
            LabTest::create([
                'name' => $request->name,
                'code' => $request->code,
                'category_id' => $request->category_id,
                'department' => $request->department,
                'sample_type' => $request->sample_type,
                'description' => $request->description,
                'preparation_instructions' => $request->preparation_instructions,
                'default_price' => $request->default_price,
                'turnaround_time' => $request->turnaround_time,
                'status' => $request->status ?? 1,
            ]);

            $message = __('radiology_lab.test_save_success');
        }

        return redirect()->route('backend.lab-tests.index')->withSuccess($message);
    }

    public function destroy($id)
    {
        $test = LabTest::find($id);
        if ($test) {
            $test->delete();
            $msg = __('radiology_lab.test_delete_success');
        } else {
            $msg = __('messages.msg_fail_to_delete', ['name' => __('radiology_lab.test_singular')]);
        }

        return comman_custom_response(['message' => $msg, 'status' => $test ? true : false]);
    }

    public function action(Request $request)
    {
        $id = $request->id;
        $test = LabTest::withTrashed()->where('id', $id)->first();

        if (!$test) {
            return response()->json(['message' => __('messages.not_found'), 'status' => false], 404);
        }

        $msg = __('messages.not_found_entry', ['name' => __('radiology_lab.test_singular')]);

        if ($request->type === 'restore') {
            $test->restore();
            $msg = __('messages.restore_form');
        } elseif ($request->type === 'forcedelete') {
            $test->forceDelete();
            $msg = __('messages.permanent_delete');
        }

        return comman_custom_response(['message' => $msg, 'status' => true]);
    }

    public function update_status(Request $request, LabTest $id)
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

        if (in_array($actionType, ['change-status']) && !auth()->user()->can('edit_lab_tests')) {
            return response()->json(['status' => false, 'message' => __('messages.permission_denied')], 403);
        }
        if (in_array($actionType, ['delete', 'permanently-delete', 'restore']) && !auth()->user()->can('delete_lab_tests')) {
            return response()->json(['status' => false, 'message' => __('messages.permission_denied')], 403);
        }

        switch ($actionType) {
            case 'change-status':
                LabTest::whereIn('id', $ids)->update(['status' => (int) $request->status]);
                $message = __('messages.bulk_service_update');
                break;

            case 'delete':
                if (config('app.demo_mode')) {
                    return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 200);
                }
                LabTest::whereIn('id', $ids)->delete();
                $message = __('messages.bulk_service_delete');
                break;

            case 'restore':
                LabTest::withTrashed()->whereIn('id', $ids)->restore();
                $message = __('messages.bulk_service_restore');
                break;

            case 'permanently-delete':
                if (config('app.demo_mode')) {
                    return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 200);
                }
                LabTest::withTrashed()->whereIn('id', $ids)->forceDelete();
                $message = __('messages.bulk_service_permanently_deleted');
                break;

            default:
                return response()->json(['status' => false, 'message' => __('service_providers.invalid_action')]);
        }

        return response()->json(['status' => true, 'message' => $message]);
    }
}
