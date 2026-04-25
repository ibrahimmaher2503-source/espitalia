<?php

namespace Modules\RadiologyLab\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\RadiologyLab\Http\Requests\FacilitySessionFormRequest;
use Modules\RadiologyLab\Models\FacilitySession;
use Modules\RadiologyLab\Models\Lab;
use Modules\RadiologyLab\Models\RadiologyCenter;
use Yajra\DataTables\DataTables;

class FacilitySessionsController extends Controller
{
    public function __construct()
    {
        $this->module_title = 'radiology_lab.session_plural';
        $this->module_name = 'facility-sessions';
        $this->module_icon = 'ph ph-calendar-blank';

        view()->share([
            'module_title' => $this->module_title,
            'module_icon' => $this->module_icon,
            'module_name' => $this->module_name,
        ]);

        $this->middleware(['permission:view_facility_sessions'])->only('index', 'index_data');
        $this->middleware(['permission:add_facility_sessions'])->only('create', 'store');
        $this->middleware(['permission:edit_facility_sessions'])->only('update_status');
        $this->middleware(['permission:delete_facility_sessions'])->only('destroy', 'action');
    }

    public function index(Request $request)
    {
        $filter = [
            'status' => $request->status,
            'facility_type' => $request->facility_type,
        ];
        $module_name = $this->module_name;
        $pageTitle = trans('radiology_lab.session_plural');
        $assets = ['datatable'];

        return view('radiologylab::backend.facility-sessions.index', compact('pageTitle', 'module_name', 'assets', 'filter'));
    }

    public function index_data(DataTables $datatable, Request $request)
    {
        try {
            $module_name = $this->module_name;
            $query = FacilitySession::query();

            if ($request->has('filter')) {
                if (isset($request->filter['column_status'])) {
                    $query->where('status', $request->filter['column_status']);
                }
                if (!empty($request->filter['facility_type'])) {
                    $query->where('facility_type', $request->filter['facility_type']);
                }
            }

            if (auth()->user()->hasAnyRole(['admin', 'demo_admin'])) {
                $query->withTrashed();
            }

            return $datatable->eloquent($query)
                ->addColumn('check', function ($data) {
                    return '<input type="checkbox" class="form-check-input select-table-row" id="datatable-row-' . $data->id . '" name="datatable_ids[]" value="' . $data->id . '" data-type="facility-session" onclick="dataTableRowCheck(' . $data->id . ',this)">';
                })
                ->addColumn('facility_name', function ($data) {
                    return e($data->facility_name);
                })
                ->editColumn('facility_type', function ($data) {
                    $badge = $data->facility_type === 'lab'
                        ? '<span class="badge bg-soft-primary text-primary">' . __('radiology_lab.type_lab') . '</span>'
                        : '<span class="badge bg-soft-info text-info">' . __('radiology_lab.type_radiology') . '</span>';
                    return $badge;
                })
                ->editColumn('day', function ($data) {
                    return ucfirst($data->day);
                })
                ->addColumn('time_range', function ($data) {
                    return $data->start_time . ' - ' . $data->end_time;
                })
                ->editColumn('slot_duration', function ($data) {
                    return $data->slot_duration . ' min';
                })
                ->addColumn('is_holiday_badge', function ($data) {
                    return $data->is_holiday
                        ? '<span class="badge bg-soft-danger text-danger">' . __('messages.yes') . '</span>'
                        : '<span class="badge bg-soft-success text-success">' . __('messages.no') . '</span>';
                })
                ->editColumn('status', function ($row) {
                    $checked = $row->status ? 'checked="checked"' : '';
                    return '
                        <div class="form-check form-switch">
                            <input type="checkbox" data-url="' . route('backend.facility-sessions.update_status', $row->id) . '" data-token="' . csrf_token() . '" class="switch-status-change form-check-input" id="datatable-row-' . $row->id . '" name="status" value="' . $row->id . '" ' . $checked . '>
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
                    $session = $data;
                    return view('radiologylab::backend.facility-sessions.datatable.action_column', compact('session', 'module_name'))->render();
                })
                ->addIndexColumn()
                ->rawColumns(['check', 'facility_type', 'is_holiday_badge', 'status', 'action'])
                ->toJson();
        } catch (\Exception $e) {
            \Log::error('FacilitySessions index_data error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => __('messages.something_went_wrong')], 500);
        }
    }

    public function create(Request $request)
    {
        $id = $request->id;
        $data = FacilitySession::find($id);

        $pageTitle = trans('radiology_lab.lbl_add_session');

        if ($data) {
            $pageTitle = trans('radiology_lab.lbl_edit_session');
        } else {
            $data = new FacilitySession;
        }

        $labs = Lab::where('status', 1)->get();
        $radiologyCenters = RadiologyCenter::where('status', 1)->get();

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        return view('radiologylab::backend.facility-sessions.create', compact('pageTitle', 'data', 'labs', 'radiologyCenters', 'days'));
    }

    public function store(FacilitySessionFormRequest $request)
    {
        $sessionId = $request->id;

        if ($sessionId) {
            if (!auth()->user()->can('edit_facility_sessions')) {
                abort(403, __('messages.permission_denied'));
            }

            $session = FacilitySession::findOrFail($sessionId);

            $session->update([
                'facility_type' => $request->facility_type,
                'facility_id' => $request->facility_id,
                'day' => $request->day,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'slot_duration' => $request->slot_duration,
                'breaks' => $request->breaks ?? [],
                'is_holiday' => $request->is_holiday ?? false,
                'status' => $request->status ?? 1,
            ]);

            $message = __('radiology_lab.session_update_success');
        } else {
            FacilitySession::create([
                'facility_type' => $request->facility_type,
                'facility_id' => $request->facility_id,
                'day' => $request->day,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'slot_duration' => $request->slot_duration,
                'breaks' => $request->breaks ?? [],
                'is_holiday' => $request->is_holiday ?? false,
                'status' => $request->status ?? 1,
            ]);

            $message = __('radiology_lab.session_save_success');
        }

        return redirect()->route('backend.facility-sessions.index')->withSuccess($message);
    }

    public function destroy($id)
    {
        $session = FacilitySession::find($id);
        if ($session) {
            $session->delete();
            $msg = __('radiology_lab.session_delete_success');
        } else {
            $msg = __('messages.msg_fail_to_delete', ['name' => __('radiology_lab.session_singular')]);
        }

        return comman_custom_response(['message' => $msg, 'status' => $session ? true : false]);
    }

    public function action(Request $request)
    {
        $id = $request->id;
        $session = FacilitySession::withTrashed()->where('id', $id)->first();

        if (!$session) {
            return response()->json(['message' => __('messages.not_found'), 'status' => false], 404);
        }

        $msg = __('messages.not_found_entry', ['name' => __('radiology_lab.session_singular')]);

        if ($request->type === 'restore') {
            $session->restore();
            $msg = __('messages.restore_form');
        } elseif ($request->type === 'forcedelete') {
            $session->forceDelete();
            $msg = __('messages.permanent_delete');
        }

        return comman_custom_response(['message' => $msg, 'status' => true]);
    }

    public function update_status(Request $request, FacilitySession $id)
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

        if ($actionType === 'change-status' && !auth()->user()->can('edit_facility_sessions')) {
            return response()->json(['status' => false, 'message' => __('messages.permission_denied')], 403);
        }
        if (in_array($actionType, ['delete', 'permanently-delete', 'restore']) && !auth()->user()->can('delete_facility_sessions')) {
            return response()->json(['status' => false, 'message' => __('messages.permission_denied')], 403);
        }

        switch ($actionType) {
            case 'change-status':
                FacilitySession::whereIn('id', $ids)->update(['status' => (int) $request->status]);
                $message = __('messages.bulk_service_update');
                break;

            case 'delete':
                if (config('app.demo_mode')) {
                    return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 200);
                }
                FacilitySession::whereIn('id', $ids)->delete();
                $message = __('messages.bulk_service_delete');
                break;

            case 'restore':
                FacilitySession::withTrashed()->whereIn('id', $ids)->restore();
                $message = __('messages.bulk_service_restore');
                break;

            case 'permanently-delete':
                if (config('app.demo_mode')) {
                    return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 200);
                }
                FacilitySession::withTrashed()->whereIn('id', $ids)->forceDelete();
                $message = __('messages.bulk_service_permanently_deleted');
                break;

            default:
                return response()->json(['status' => false, 'message' => __('service_providers.invalid_action')]);
        }

        return response()->json(['status' => true, 'message' => $message]);
    }
}
