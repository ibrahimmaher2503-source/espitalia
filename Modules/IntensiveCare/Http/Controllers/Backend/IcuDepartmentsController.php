<?php

namespace Modules\IntensiveCare\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\IntensiveCare\Http\Requests\IcuDepartmentFormRequest;
use Modules\IntensiveCare\Models\Hospital;
use Modules\IntensiveCare\Models\IcuDepartment;
use Yajra\DataTables\DataTables;

class IcuDepartmentsController extends Controller
{
    public function __construct()
    {
        $this->module_title = 'intensive_care.department_plural';
        $this->module_name = 'icu-departments';
        $this->module_icon = 'ph ph-bed';

        view()->share([
            'module_title' => $this->module_title,
            'module_icon' => $this->module_icon,
            'module_name' => $this->module_name,
        ]);

        $this->middleware(['permission:view_icu_departments'])->only('index', 'index_data', 'show');
        $this->middleware(['permission:add_icu_departments'])->only('create', 'store');
        $this->middleware(['permission:edit_icu_departments'])->only('update_status', 'update_beds');
        $this->middleware(['permission:delete_icu_departments'])->only('destroy', 'action', 'bulk_action');
    }

    public function index(Request $request)
    {
        $filter = [
            'status' => $request->status,
        ];
        $module_name = $this->module_name;
        $pageTitle = trans('intensive_care.department_plural');
        $assets = ['datatable'];

        return view('intensivecare::backend.departments.index', compact('pageTitle', 'module_name', 'assets', 'filter'));
    }

    public function index_data(DataTables $datatable, Request $request)
    {
        try {
            $module_name = $this->module_name;
            $query = IcuDepartment::with('hospital');

            // Coordinator filter
            if (auth()->user()->hasRole('hospital_coordinator')) {
                $hospital = Hospital::where('vendor_id', auth()->id())->first();
                if ($hospital) {
                    $query->where('hospital_id', $hospital->id);
                } else {
                    $query->whereRaw('1 = 0');
                }
            }

            if ($request->has('filter') && isset($request->filter['column_status'])) {
                $query->where('status', $request->filter['column_status']);
            }

            if (auth()->user()->hasAnyRole(['admin', 'demo_admin'])) {
                $query->withTrashed();
            }

            return $datatable->eloquent($query)
                ->addColumn('check', function ($data) {
                    return '<input type="checkbox" class="form-check-input select-table-row" id="datatable-row-'.$data->id.'" name="datatable_ids[]" value="'.$data->id.'" data-type="icu-department" onclick="dataTableRowCheck('.$data->id.',this)">';
                })
                ->addColumn('name_ar', function ($data) {
                    return e($data->name_ar ?? '-');
                })
                ->addColumn('name_en', function ($data) {
                    return e($data->name_en ?? '-');
                })
                ->addColumn('hospital_name', function ($data) {
                    return $data->hospital ? e($data->hospital->name) : '-';
                })
                ->filterColumn('hospital_name', function ($query, $keyword) {
                    $keyword = str_replace(['%', '_'], ['\%', '\_'], $keyword);
                    $query->whereHas('hospital', function ($q) use ($keyword) {
                        $q->where('name_en', 'LIKE', "%$keyword%")
                            ->orWhere('name_ar', 'LIKE', "%$keyword%");
                    });
                })
                ->editColumn('specialty_type', function ($data) {
                    return __('intensive_care.case_'.$data->specialty_type);
                })
                ->addColumn('beds', function ($data) {
                    $color = $data->available_beds > 0 ? 'success' : 'danger';

                    return '<span class="badge bg-'.$color.'">'.$data->available_beds.' / '.$data->total_beds.'</span>';
                })
                ->editColumn('daily_price', function ($data) {
                    return number_format($data->daily_price, 2);
                })
                ->editColumn('status', function ($row) {
                    $checked = '';
                    if ($row->status) {
                        $checked = 'checked="checked"';
                    }

                    return '
                        <div class="form-check form-switch ">
                            <input type="checkbox" data-url="'.route('backend.icu-departments.update_status', $row->id).'" data-token="'.csrf_token().'" class="switch-status-change form-check-input" id="datatable-row-'.$row->id.'" name="status" value="'.$row->id.'" '.$checked.'>
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
                    $department = $data;

                    return view('intensivecare::backend.departments.datatable.action_column', compact('department', 'module_name'))->render();
                })
                ->addIndexColumn()
                ->rawColumns(['check', 'beds', 'status', 'action'])
                ->toJson();
        } catch (\Exception $e) {
            return response()->json(['error' => __('messages.something_went_wrong')], 500);
        }
    }

    public function create(Request $request)
    {
        $id = $request->id;
        $data = IcuDepartment::find($id);

        $pageTitle = trans('messages.add_button_form', ['form' => trans('intensive_care.department_singular')]);

        if ($data) {
            $pageTitle = trans('messages.update_form_title', ['form' => trans('intensive_care.department_singular')]);
        } else {
            $data = new IcuDepartment;
        }

        // Hospitals list for select
        if (auth()->user()->hasRole('hospital_coordinator')) {
            $hospitals = Hospital::where('vendor_id', auth()->id())->active()->get();
        } else {
            $hospitals = Hospital::active()->get();
        }

        $specialtyTypes = IcuDepartment::SPECIALTY_TYPES;
        $equipmentLevels = IcuDepartment::EQUIPMENT_LEVELS;

        return view('intensivecare::backend.departments.create', compact('pageTitle', 'data', 'hospitals', 'specialtyTypes', 'equipmentLevels'));
    }

    public function store(IcuDepartmentFormRequest $request)
    {
        $departmentId = $request->id;

        $data = [
            'hospital_id' => $request->hospital_id,
            'name_ar' => $request->name_ar,
            'name_en' => $request->name_en,
            'description_ar' => $request->description_ar,
            'description_en' => $request->description_en,
            'specialty_type' => $request->specialty_type,
            'total_beds' => $request->total_beds,
            'available_beds' => $request->available_beds,
            'has_ventilator' => $request->has_ventilator ?? 0,
            'has_oxygen' => $request->has_oxygen ?? 0,
            'equipment_level' => $request->equipment_level,
            'daily_price' => $request->daily_price,
            'status' => $request->status ?? 1,
        ];

        if ($departmentId) {
            abort_if(! auth()->user()->can('edit_icu_departments'), 403);

            $department = IcuDepartment::findOrFail($departmentId);
            $department->update($data);

            $message = __('intensive_care.department_update_success');
        } else {
            IcuDepartment::create($data);

            $message = __('intensive_care.department_save_success');
        }

        return redirect()->route('backend.icu-departments.index')->withSuccess($message);
    }

    public function show($id)
    {
        $department = IcuDepartment::with(['hospital', 'admissionRequests'])->findOrFail($id);
        $pageTitle = __('intensive_care.department_detail');

        return view('intensivecare::backend.departments.show', compact('department', 'pageTitle'));
    }

    public function destroy($id)
    {
        $department = IcuDepartment::find($id);
        if ($department) {
            $department->delete();
            $msg = __('intensive_care.department_delete_success');
        } else {
            $msg = __('messages.msg_fail_to_delete', ['name' => __('intensive_care.department_singular')]);
        }

        return comman_custom_response(['message' => $msg, 'status' => $department ? true : false]);
    }

    public function action(Request $request)
    {
        $id = $request->id;
        $department = IcuDepartment::withTrashed()->where('id', $id)->first();

        if (! $department) {
            return response()->json(['message' => __('messages.not_found'), 'status' => false], 404);
        }

        $msg = __('messages.not_found_entry', ['name' => __('intensive_care.department_singular')]);

        if ($request->type === 'restore') {
            $department->restore();
            $msg = __('messages.restore_form');
        } elseif ($request->type === 'forcedelete') {
            $department->forceDelete();
            $msg = __('messages.permanent_delete');
        }

        return comman_custom_response(['message' => $msg, 'status' => true]);
    }

    public function update_status(Request $request, IcuDepartment $id)
    {
        $request->validate(['status' => 'required|in:0,1']);

        $id->update(['status' => $request->status]);

        return response()->json(['status' => true, 'message' => __('intensive_care.status_update_success')]);
    }

    public function update_beds(Request $request, $id)
    {
        $request->validate([
            'total_beds' => 'required|integer|min:0',
            'available_beds' => 'required|integer|min:0|lte:total_beds',
        ]);

        $department = IcuDepartment::findOrFail($id);

        $department->update([
            'total_beds' => $request->total_beds,
            'available_beds' => $request->available_beds,
        ]);

        return response()->json(['status' => true, 'message' => __('intensive_care.beds_update_success')]);
    }

    public function bulk_action(Request $request)
    {
        $request->validate([
            'rowIds' => 'required|string',
            'type' => 'required|string',
        ]);

        $ids = explode(',', $request->rowIds);
        $actionType = $request->action_type;
        $message = __('messages.bulk_update');

        switch ($actionType) {
            case 'change-status':
                IcuDepartment::whereIn('id', $ids)->update(['status' => $request->status]);
                $message = __('messages.bulk_service_update');
                break;

            case 'delete':
                if (config('app.demo_mode')) {
                    return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 200);
                }
                IcuDepartment::whereIn('id', $ids)->delete();
                $message = __('messages.bulk_service_delete');
                break;

            case 'restore':
                IcuDepartment::withTrashed()->whereIn('id', $ids)->restore();
                $message = __('messages.bulk_service_restore');
                break;

            case 'permanently-delete':
                if (config('app.demo_mode')) {
                    return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 200);
                }
                IcuDepartment::withTrashed()->whereIn('id', $ids)->forceDelete();
                $message = __('messages.bulk_service_permanently_deleted');
                break;

            default:
                return response()->json(['status' => false, 'message' => __('service_providers.invalid_action')]);
        }

        return response()->json(['status' => true, 'message' => $message]);
    }
}
