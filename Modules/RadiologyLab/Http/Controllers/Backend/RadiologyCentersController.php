<?php

namespace Modules\RadiologyLab\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Modules\Location\Models\Governorate;
use Modules\Location\Models\GovernorateCity;
use Modules\RadiologyLab\Http\Requests\RadiologyCenterFormRequest;
use Modules\RadiologyLab\Models\RadiologyCenter;
use Yajra\DataTables\DataTables;

class RadiologyCentersController extends Controller
{
    public function __construct()
    {
        $this->module_title = 'radiology_lab.radiology_center_plural';
        $this->module_name = 'radiology-centers';
        $this->module_icon = 'ph ph-scan';

        view()->share([
            'module_title' => $this->module_title,
            'module_icon' => $this->module_icon,
            'module_name' => $this->module_name,
        ]);

        $this->middleware(['permission:view_radiology_centers'])->only('index', 'index_data');
        $this->middleware(['permission:add_radiology_centers'])->only('create', 'store');
        $this->middleware(['permission:edit_radiology_centers'])->only('update_status');
        $this->middleware(['permission:delete_radiology_centers'])->only('destroy', 'action');
    }

    public function index(Request $request)
    {
        $filter = [
            'status' => $request->status,
        ];
        $module_name = $this->module_name;
        $pageTitle = trans('radiology_lab.radiology_center_plural');
        $assets = ['datatable'];

        return view('radiologylab::backend.radiology-centers.index', compact('pageTitle', 'module_name', 'assets', 'filter'));
    }

    public function index_data(DataTables $datatable, Request $request)
    {
        try {
            $module_name = $this->module_name;
            $query = RadiologyCenter::with(['governorate', 'city']);

            if ($request->has('filter') && isset($request->filter['column_status'])) {
                $query->where('status', $request->filter['column_status']);
            }

            if (auth()->user()->hasAnyRole(['admin', 'demo_admin'])) {
                $query->withTrashed();
            }

            return $datatable->eloquent($query)
                ->addColumn('check', function ($data) {
                    return '<input type="checkbox" class="form-check-input select-table-row" id="datatable-row-' . $data->id . '" name="datatable_ids[]" value="' . $data->id . '" data-type="radiology-center" onclick="dataTableRowCheck(' . $data->id . ',this)">';
                })
                ->editColumn('name', function ($data) {
                    return e($data->name);
                })
                ->orderColumn('name', function ($query, $order) {
                    $col = app()->getLocale() === 'ar' ? 'name_ar' : 'name_en';
                    $query->orderBy($col, $order);
                })
                ->editColumn('scan_type', function ($data) {
                    return $data->scan_type ? e($data->scan_type) : '-';
                })
                ->addColumn('governorate_name', function ($data) {
                    return $data->governorate ? e($data->governorate->name) : '-';
                })
                ->addColumn('city_name', function ($data) {
                    return $data->city ? e($data->city->name) : '-';
                })
                ->editColumn('status', function ($row) {
                    $checked = $row->status ? 'checked="checked"' : '';
                    return '
                        <div class="form-check form-switch">
                            <input type="checkbox" data-url="' . route('backend.radiology-centers.update_status', $row->id) . '" data-token="' . csrf_token() . '" class="switch-status-change form-check-input" id="datatable-row-' . $row->id . '" name="status" value="' . $row->id . '" ' . $checked . '>
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
                    $radiologyCenter = $data;
                    return view('radiologylab::backend.radiology-centers.datatable.action_column', compact('radiologyCenter', 'module_name'))->render();
                })
                ->addIndexColumn()
                ->rawColumns(['check', 'status', 'action'])
                ->toJson();
        } catch (\Exception $e) {
            \Log::error('RadiologyCenters index_data error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => __('messages.something_went_wrong')], 500);
        }
    }

    public function create(Request $request)
    {
        $id = $request->id;
        $data = RadiologyCenter::find($id);

        $pageTitle = trans('radiology_lab.lbl_add_radiology_center');

        if ($data) {
            $pageTitle = trans('radiology_lab.lbl_edit_radiology_center');
        } else {
            $data = new RadiologyCenter;
        }

        // PF-14: cache governorate list
        $governorates = Cache::remember('location.governorates.all', 3600, fn () => Governorate::all());
        $cities = collect();
        if ($data->governorate_id) {
            $cities = GovernorateCity::where('governorate_id', $data->governorate_id)->get();
        }

        return view('radiologylab::backend.radiology-centers.create', compact('pageTitle', 'data', 'governorates', 'cities'));
    }

    public function store(RadiologyCenterFormRequest $request)
    {
        $centerId = $request->id;

        if ($centerId) {
            if (!auth()->user()->can('edit_radiology_centers')) {
                abort(403, __('messages.permission_denied'));
            }

            $center = RadiologyCenter::findOrFail($centerId);

            $center->update([
                'name_ar' => $request->name_ar,
                'name_en' => $request->name_en,
                'scan_type' => $request->scan_type,
                'governorate_id' => $request->governorate_id,
                'city_id' => $request->city_id,
                'status' => $request->status ?? 1,
            ]);

            $message = __('radiology_lab.radiology_center_update_success');
        } else {
            RadiologyCenter::create([
                'name_ar' => $request->name_ar,
                'name_en' => $request->name_en,
                'scan_type' => $request->scan_type,
                'governorate_id' => $request->governorate_id,
                'city_id' => $request->city_id,
                'status' => $request->status ?? 1,
            ]);

            $message = __('radiology_lab.radiology_center_save_success');
        }

        return redirect()->route('backend.radiology-centers.index')->withSuccess($message);
    }

    public function destroy($id)
    {
        $center = RadiologyCenter::find($id);
        if ($center) {
            $center->delete();
            $msg = __('radiology_lab.radiology_center_delete_success');
        } else {
            $msg = __('messages.msg_fail_to_delete', ['name' => __('radiology_lab.radiology_center_singular')]);
        }

        return comman_custom_response(['message' => $msg, 'status' => $center ? true : false]);
    }

    public function action(Request $request)
    {
        $id = $request->id;
        $center = RadiologyCenter::withTrashed()->where('id', $id)->first();

        if (!$center) {
            return response()->json(['message' => __('messages.not_found'), 'status' => false], 404);
        }

        $msg = __('messages.not_found_entry', ['name' => __('radiology_lab.radiology_center_singular')]);

        if ($request->type === 'restore') {
            $center->restore();
            $msg = __('messages.restore_form');
        } elseif ($request->type === 'forcedelete') {
            $center->forceDelete();
            $msg = __('messages.permanent_delete');
        }

        return comman_custom_response(['message' => $msg, 'status' => true]);
    }

    public function update_status(Request $request, RadiologyCenter $id)
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

        if ($actionType === 'change-status' && !auth()->user()->can('edit_radiology_centers')) {
            return response()->json(['status' => false, 'message' => __('messages.permission_denied')], 403);
        }
        if (in_array($actionType, ['delete', 'permanently-delete', 'restore']) && !auth()->user()->can('delete_radiology_centers')) {
            return response()->json(['status' => false, 'message' => __('messages.permission_denied')], 403);
        }

        switch ($actionType) {
            case 'change-status':
                RadiologyCenter::whereIn('id', $ids)->update(['status' => (int) $request->status]);
                $message = __('messages.bulk_service_update');
                break;

            case 'delete':
                if (config('app.demo_mode')) {
                    return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 200);
                }
                RadiologyCenter::whereIn('id', $ids)->delete();
                $message = __('messages.bulk_service_delete');
                break;

            case 'restore':
                RadiologyCenter::withTrashed()->whereIn('id', $ids)->restore();
                $message = __('messages.bulk_service_restore');
                break;

            case 'permanently-delete':
                if (config('app.demo_mode')) {
                    return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 200);
                }
                RadiologyCenter::withTrashed()->whereIn('id', $ids)->forceDelete();
                $message = __('messages.bulk_service_permanently_deleted');
                break;

            default:
                return response()->json(['status' => false, 'message' => __('service_providers.invalid_action')]);
        }

        return response()->json(['status' => true, 'message' => $message]);
    }
}
