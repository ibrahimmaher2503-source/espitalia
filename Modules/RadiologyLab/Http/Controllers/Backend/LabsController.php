<?php

namespace Modules\RadiologyLab\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Modules\Location\Models\Governorate;
use Modules\Location\Models\GovernorateCity;
use Modules\RadiologyLab\Http\Requests\LabFormRequest;
use Modules\RadiologyLab\Models\Lab;
use Yajra\DataTables\DataTables;

class LabsController extends Controller
{
    public function __construct()
    {
        $this->module_title = 'radiology_lab.lab_plural';
        $this->module_name = 'labs';
        $this->module_icon = 'ph ph-flask';

        view()->share([
            'module_title' => $this->module_title,
            'module_icon' => $this->module_icon,
            'module_name' => $this->module_name,
        ]);

        $this->middleware(['permission:view_labs'])->only('index', 'index_data');
        $this->middleware(['permission:add_labs'])->only('create', 'store');
        $this->middleware(['permission:edit_labs'])->only('update_status');
        $this->middleware(['permission:delete_labs'])->only('destroy', 'action');
    }

    public function index(Request $request)
    {
        $filter = [
            'status' => $request->status,
        ];
        $module_name = $this->module_name;
        $pageTitle = trans('radiology_lab.lab_plural');
        $assets = ['datatable'];

        return view('radiologylab::backend.labs.index', compact('pageTitle', 'module_name', 'assets', 'filter'));
    }

    public function index_data(DataTables $datatable, Request $request)
    {
        try {
            $module_name = $this->module_name;
            $query = Lab::with(['governorate', 'city']);

            if ($request->has('filter') && isset($request->filter['column_status'])) {
                $query->where('status', $request->filter['column_status']);
            }

            if (auth()->user()->hasAnyRole(['admin', 'demo_admin'])) {
                $query->withTrashed();
            }

            return $datatable->eloquent($query)
                ->addColumn('check', function ($data) {
                    return '<input type="checkbox" class="form-check-input select-table-row" id="datatable-row-' . $data->id . '" name="datatable_ids[]" value="' . $data->id . '" data-type="lab" onclick="dataTableRowCheck(' . $data->id . ',this)">';
                })
                ->editColumn('name', function ($data) {
                    return e($data->name);
                })
                ->orderColumn('name', function ($query, $order) {
                    $col = app()->getLocale() === 'ar' ? 'name_ar' : 'name_en';
                    $query->orderBy($col, $order);
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
                            <input type="checkbox" data-url="' . route('backend.labs.update_status', $row->id) . '" data-token="' . csrf_token() . '" class="switch-status-change form-check-input" id="datatable-row-' . $row->id . '" name="status" value="' . $row->id . '" ' . $checked . '>
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
                    $lab = $data;
                    return view('radiologylab::backend.labs.datatable.action_column', compact('lab', 'module_name'))->render();
                })
                ->addIndexColumn()
                ->rawColumns(['check', 'status', 'action'])
                ->toJson();
        } catch (\Exception $e) {
            \Log::error('Labs index_data error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => __('messages.something_went_wrong')], 500);
        }
    }

    public function create(Request $request)
    {
        $id = $request->id;
        $data = Lab::find($id);

        $pageTitle = trans('radiology_lab.lbl_add_lab');

        if ($data) {
            $pageTitle = trans('radiology_lab.lbl_edit_lab');
        } else {
            $data = new Lab;
        }

        // PF-14: cache governorate list (slow-changing)
        $governorates = Cache::remember('location.governorates.all', 3600, fn () => Governorate::all());
        $cities = collect();
        if ($data->governorate_id) {
            $cities = GovernorateCity::where('governorate_id', $data->governorate_id)->get();
        }

        return view('radiologylab::backend.labs.create', compact('pageTitle', 'data', 'governorates', 'cities'));
    }

    public function store(LabFormRequest $request)
    {
        $labId = $request->id;

        if ($labId) {
            if (!auth()->user()->can('edit_labs')) {
                abort(403, __('messages.permission_denied'));
            }

            $lab = Lab::findOrFail($labId);

            $lab->update([
                'name_ar' => $request->name_ar,
                'name_en' => $request->name_en,
                'governorate_id' => $request->governorate_id,
                'city_id' => $request->city_id,
                'status' => $request->status ?? 1,
            ]);

            $message = __('radiology_lab.lab_update_success');
        } else {
            Lab::create([
                'name_ar' => $request->name_ar,
                'name_en' => $request->name_en,
                'governorate_id' => $request->governorate_id,
                'city_id' => $request->city_id,
                'status' => $request->status ?? 1,
            ]);

            $message = __('radiology_lab.lab_save_success');
        }

        return redirect()->route('backend.labs.index')->withSuccess($message);
    }

    public function destroy($id)
    {
        $lab = Lab::find($id);
        if ($lab) {
            $lab->delete();
            $msg = __('radiology_lab.lab_delete_success');
        } else {
            $msg = __('messages.msg_fail_to_delete', ['name' => __('radiology_lab.lab_singular')]);
        }

        return comman_custom_response(['message' => $msg, 'status' => $lab ? true : false]);
    }

    public function action(Request $request)
    {
        $id = $request->id;
        $lab = Lab::withTrashed()->where('id', $id)->first();

        if (!$lab) {
            return response()->json(['message' => __('messages.not_found'), 'status' => false], 404);
        }

        $msg = __('messages.not_found_entry', ['name' => __('radiology_lab.lab_singular')]);

        if ($request->type === 'restore') {
            $lab->restore();
            $msg = __('messages.restore_form');
        } elseif ($request->type === 'forcedelete') {
            $lab->forceDelete();
            $msg = __('messages.permanent_delete');
        }

        return comman_custom_response(['message' => $msg, 'status' => true]);
    }

    public function update_status(Request $request, Lab $id)
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

        if ($actionType === 'change-status' && !auth()->user()->can('edit_labs')) {
            return response()->json(['status' => false, 'message' => __('messages.permission_denied')], 403);
        }
        if (in_array($actionType, ['delete', 'permanently-delete', 'restore']) && !auth()->user()->can('delete_labs')) {
            return response()->json(['status' => false, 'message' => __('messages.permission_denied')], 403);
        }

        switch ($actionType) {
            case 'change-status':
                Lab::whereIn('id', $ids)->update(['status' => (int) $request->status]);
                $message = __('messages.bulk_service_update');
                break;

            case 'delete':
                if (config('app.demo_mode')) {
                    return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 200);
                }
                Lab::whereIn('id', $ids)->delete();
                $message = __('messages.bulk_service_delete');
                break;

            case 'restore':
                Lab::withTrashed()->whereIn('id', $ids)->restore();
                $message = __('messages.bulk_service_restore');
                break;

            case 'permanently-delete':
                if (config('app.demo_mode')) {
                    return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 200);
                }
                Lab::withTrashed()->whereIn('id', $ids)->forceDelete();
                $message = __('messages.bulk_service_permanently_deleted');
                break;

            default:
                return response()->json(['status' => false, 'message' => __('service_providers.invalid_action')]);
        }

        return response()->json(['status' => true, 'message' => $message]);
    }
}
