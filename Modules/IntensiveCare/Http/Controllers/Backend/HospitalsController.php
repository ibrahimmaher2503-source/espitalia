<?php

namespace Modules\IntensiveCare\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\IntensiveCare\Http\Requests\HospitalFormRequest;
use Modules\IntensiveCare\Models\Hospital;
use Modules\IntensiveCare\Models\IcuCommission;
use Modules\Location\Models\Governorate;
use Modules\Location\Models\GovernorateCity;
use Yajra\DataTables\DataTables;

class HospitalsController extends Controller
{
    public function __construct()
    {
        $this->module_title = 'intensive_care.hospital_plural';
        $this->module_name = 'hospitals';
        $this->module_icon = 'ph ph-buildings';

        view()->share([
            'module_title' => $this->module_title,
            'module_icon' => $this->module_icon,
            'module_name' => $this->module_name,
        ]);

        $this->middleware(['permission:view_hospitals'])->only('index', 'index_data', 'show');
        $this->middleware(['permission:add_hospitals'])->only('create', 'store');
        $this->middleware(['permission:edit_hospitals'])->only('update_status');
        $this->middleware(['permission:delete_hospitals'])->only('destroy', 'action', 'bulk_action');
    }

    public function index(Request $request)
    {
        $filter = [
            'status' => $request->status,
        ];
        $module_name = $this->module_name;
        $pageTitle = trans('intensive_care.hospital_plural');
        $assets = ['datatable'];

        return view('intensivecare::backend.hospitals.index', compact('pageTitle', 'module_name', 'assets', 'filter'));
    }

    public function index_data(DataTables $datatable, Request $request)
    {
        try {
            $module_name = $this->module_name;
            $query = Hospital::with(['governorate', 'city']);

            // Coordinator filter
            if (auth()->user()->hasRole('hospital_coordinator')) {
                $query->where('vendor_id', auth()->id());
            }

            if ($request->has('filter') && isset($request->filter['column_status'])) {
                $query->where('status', $request->filter['column_status']);
            }

            if (auth()->user()->hasAnyRole(['admin', 'demo_admin'])) {
                $query->withTrashed();
            }

            return $datatable->eloquent($query)
                ->addColumn('check', function ($data) {
                    return '<input type="checkbox" class="form-check-input select-table-row" id="datatable-row-'.$data->id.'" name="datatable_ids[]" value="'.$data->id.'" data-type="hospital" onclick="dataTableRowCheck('.$data->id.',this)">';
                })
                ->addColumn('name_ar', function ($data) {
                    return e($data->name_ar ?? '-');
                })
                ->addColumn('name_en', function ($data) {
                    return e($data->name_en ?? '-');
                })
                ->addColumn('governorate_name', function ($data) {
                    return $data->governorate ? e($data->governorate->name) : '-';
                })
                ->addColumn('city_name', function ($data) {
                    return $data->city ? e($data->city->name) : '-';
                })
                ->editColumn('hospital_type', function ($data) {
                    return __('intensive_care.type_'.$data->hospital_type);
                })
                ->editColumn('status', function ($row) {
                    $checked = '';
                    if ($row->status) {
                        $checked = 'checked="checked"';
                    }

                    return '
                        <div class="form-check form-switch ">
                            <input type="checkbox" data-url="'.route('backend.hospitals.update_status', $row->id).'" data-token="'.csrf_token().'" class="switch-status-change form-check-input" id="datatable-row-'.$row->id.'" name="status" value="'.$row->id.'" '.$checked.'>
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
                    $hospital = $data;

                    return view('intensivecare::backend.hospitals.datatable.action_column', compact('hospital', 'module_name'))->render();
                })
                ->addIndexColumn()
                ->rawColumns(['check', 'status', 'action'])
                ->toJson();
        } catch (\Exception $e) {
            return response()->json(['error' => __('messages.something_went_wrong')], 500);
        }
    }

    public function create(Request $request)
    {
        $id = $request->id;
        $data = Hospital::find($id);

        $pageTitle = trans('messages.add_button_form', ['form' => trans('intensive_care.hospital_singular')]);

        if ($data) {
            $pageTitle = trans('messages.update_form_title', ['form' => trans('intensive_care.hospital_singular')]);
        } else {
            $data = new Hospital;
        }

        $hospitalTypes = Hospital::HOSPITAL_TYPES;

        $governorates = Governorate::all();
        $cities = $data->governorate_id
            ? GovernorateCity::where('governorate_id', $data->governorate_id)->get()
            : collect();

        $commissions = IcuCommission::where('status', true)->get();

        return view('intensivecare::backend.hospitals.create', compact('pageTitle', 'data', 'hospitalTypes', 'governorates', 'cities', 'commissions'));
    }

    public function store(HospitalFormRequest $request)
    {
        $hospitalId = $request->id;

        $saveData = [
            'name_ar' => $request->name_ar,
            'name_en' => $request->name_en,
            'description_ar' => $request->description_ar,
            'description_en' => $request->description_en,
            'hospital_type' => $request->hospital_type,
            'address_ar' => $request->address_ar,
            'address_en' => $request->address_en,
            'governorate_id' => $request->governorate_id,
            'city_id' => $request->city_id,
            'commission_id' => $request->commission_id,
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country,
            'postal_code' => $request->postal_code,
            'phone' => $request->phone,
            'email' => $request->email,
            'website' => $request->website,
            'license_number' => $request->license_number,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'accepted_insurance' => $request->accepted_insurance,
            'status' => $request->status ?? 1,
        ];

        if ($hospitalId) {
            $hospital = Hospital::findOrFail($hospitalId);

            abort_if(! auth()->user()->can('edit_hospitals'), 403);

            if ($request->has('vendor_id')) {
                $saveData['vendor_id'] = auth()->user()->hasRole('hospital_coordinator') ? auth()->id() : $request->vendor_id;
            }

            $hospital->update($saveData);

            if ($request->hasFile('logo')) {
                storeMediaFile($hospital, $request->file('logo'), 'logo');
            }

            $message = __('intensive_care.hospital_update_success');
        } else {
            $saveData['vendor_id'] = auth()->user()->hasRole('hospital_coordinator') ? auth()->id() : ($request->vendor_id ?? null);

            $hospital = Hospital::create($saveData);

            if ($request->hasFile('logo')) {
                storeMediaFile($hospital, $request->file('logo'), 'logo');
            }

            $message = __('intensive_care.hospital_save_success');
        }

        return redirect()->route('backend.hospitals.index')->withSuccess($message);
    }

    public function show($id)
    {
        $hospital = Hospital::with(['icuDepartments', 'vendor', 'governorate', 'city', 'commission'])->findOrFail($id);
        $pageTitle = __('intensive_care.hospital_detail');

        return view('intensivecare::backend.hospitals.show', compact('hospital', 'pageTitle'));
    }

    public function destroy($id)
    {
        $hospital = Hospital::find($id);
        if ($hospital) {
            $hospital->delete();
            $msg = __('intensive_care.hospital_delete_success');
        } else {
            $msg = __('messages.msg_fail_to_delete', ['name' => __('intensive_care.hospital_singular')]);
        }

        return comman_custom_response(['message' => $msg, 'status' => $hospital ? true : false]);
    }

    public function action(Request $request)
    {
        $id = $request->id;
        $hospital = Hospital::withTrashed()->where('id', $id)->first();

        if (! $hospital) {
            return response()->json(['message' => __('messages.not_found'), 'status' => false], 404);
        }

        $msg = __('messages.not_found_entry', ['name' => __('intensive_care.hospital_singular')]);

        if ($request->type === 'restore') {
            $hospital->restore();
            $msg = __('messages.restore_form');
        } elseif ($request->type === 'forcedelete') {
            $hospital->forceDelete();
            $msg = __('messages.permanent_delete');
        }

        return comman_custom_response(['message' => $msg, 'status' => true]);
    }

    public function update_status(Request $request, Hospital $id)
    {
        $request->validate(['status' => 'required|in:0,1']);

        $id->update(['status' => $request->status]);

        return response()->json(['status' => true, 'message' => __('intensive_care.status_update_success')]);
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
                Hospital::whereIn('id', $ids)->update(['status' => $request->status]);
                $message = __('messages.bulk_service_update');
                break;

            case 'delete':
                if (config('app.demo_mode')) {
                    return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 200);
                }
                Hospital::whereIn('id', $ids)->delete();
                $message = __('messages.bulk_service_delete');
                break;

            case 'restore':
                Hospital::withTrashed()->whereIn('id', $ids)->restore();
                $message = __('messages.bulk_service_restore');
                break;

            case 'permanently-delete':
                if (config('app.demo_mode')) {
                    return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 200);
                }
                Hospital::withTrashed()->whereIn('id', $ids)->forceDelete();
                $message = __('messages.bulk_service_permanently_deleted');
                break;

            default:
                return response()->json(['status' => false, 'message' => __('service_providers.invalid_action')]);
        }

        return response()->json(['status' => true, 'message' => $message]);
    }
}
