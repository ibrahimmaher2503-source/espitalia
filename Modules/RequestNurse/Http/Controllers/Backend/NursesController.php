<?php

namespace Modules\RequestNurse\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Modules\Location\Models\Governorate;
use Modules\Location\Models\GovernorateCity;
use Modules\RequestNurse\Models\Nurse;
use Yajra\DataTables\DataTables;

class NursesController extends Controller
{
    public function __construct()
    {
        $this->module_title = 'request_nurse.nurse_plural';
        $this->module_name = 'nurses';
        $this->module_icon = 'ph ph-user-list';

        // Permission middleware
        $this->middleware('can:view_nurses')->only(['index', 'index_data', 'show']);
        $this->middleware('can:add_nurses')->only(['create', 'store']);
        $this->middleware('can:edit_nurses')->only(['edit', 'update', 'update_status']);
        $this->middleware('can:delete_nurses')->only(['destroy', 'bulk_action', 'action']);

        view()->share([
            'module_title' => $this->module_title,
            'module_icon' => $this->module_icon,
            'module_name' => $this->module_name,
        ]);
    }

    public function index(Request $request)
    {
        $filter = [
            'status' => $request->status,
        ];
        $module_name = $this->module_name;
        $pageTitle = trans('request_nurse.nurse_plural');
        $assets = ['datatable'];

        $stats = [
            'total' => Nurse::count(),
            'active' => Nurse::where('status', 1)->count(),
            'available' => Nurse::where('status', 1)->where('availability_status', 'available')->count(),
            'busy' => Nurse::where('status', 1)->where('availability_status', 'busy')->count(),
        ];

        return view('requestnurse::backend.nurses.index', compact('pageTitle', 'module_name', 'assets', 'filter', 'stats'));
    }

    public function index_data(DataTables $datatable, Request $request)
    {
        try {
            $module_name = $this->module_name;
            $query = Nurse::with(['user', 'governorate', 'city']);

            if ($request->has('filter') && isset($request->filter['column_status'])) {
                $query->where('status', $request->filter['column_status']);
            }

            if (auth()->user()->hasAnyRole(['admin', 'demo_admin'])) {
                $query->withTrashed();
            }

            return $datatable->eloquent($query)
                ->addColumn('check', function ($data) {
                    return '<input type="checkbox" class="form-check-input select-table-row" id="datatable-row-'.$data->id.'" name="datatable_ids[]" value="'.$data->id.'" data-type="nurse" onclick="dataTableRowCheck('.$data->id.',this)">';
                })
                ->addColumn('nurse_name', function ($data) {
                    return $data->user ? $data->user->first_name.' '.$data->user->last_name : '-';
                })
                ->filterColumn('nurse_name', function ($query, $keyword) {
                    $query->whereHas('user', function ($q) use ($keyword) {
                        $q->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$keyword%"]);
                    });
                })
                ->editColumn('status', function ($row) {
                    $checked = '';
                    if ($row->status) {
                        $checked = 'checked="checked"';
                    }

                    return '
                        <div class="form-check form-switch ">
                            <input type="checkbox" data-url="'.route('backend.nurses.update_status', $row->id).'" data-token="'.csrf_token().'" class="switch-status-change form-check-input" id="datatable-row-'.$row->id.'" name="status" value="'.$row->id.'" '.$checked.'>
                        </div>
                    ';
                })
                ->editColumn('availability_status', function ($row) {
                    return match ($row->availability_status) {
                        'available' => '<span class="badge bg-success">'.__('request_nurse.status_available').'</span>',
                        'busy' => '<span class="badge bg-warning text-dark">'.__('request_nurse.status_busy').'</span>',
                        'off_duty' => '<span class="badge bg-secondary">'.__('request_nurse.status_off_duty').'</span>',
                        default => $row->availability_status,
                    };
                })
                ->addColumn('specialization', function ($data) {
                    return $data->specialization ?: '-';
                })
                ->addColumn('experience', function ($data) {
                    return $data->experience ?: '-';
                })
                ->editColumn('hourly_rate', function ($data) {
                    return $data->hourly_rate ? number_format($data->hourly_rate, 2) : '-';
                })
                ->addColumn('location', function ($data) {
                    $parts = array_filter([
                        $data->governorate ? $data->governorate->name : null,
                        $data->city ? $data->city->name : null,
                    ]);

                    return implode(' - ', $parts) ?: '-';
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
                    $nurse = $data;

                    return view('requestnurse::backend.nurses.datatable.action_column', compact('nurse', 'module_name'))->render();
                })
                ->addIndexColumn()
                ->rawColumns(['check', 'status', 'availability_status', 'specialization', 'experience', 'location', 'action'])
                ->toJson();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function create(Request $request)
    {
        $id = $request->id;
        $nurse = Nurse::with('user')->find($id);

        $pageTitle = trans('messages.add_button_form', ['form' => trans('request_nurse.nurse_singular')]);

        if ($nurse) {
            $pageTitle = trans('messages.update_form_title', ['form' => trans('request_nurse.nurse_singular')]);
        } else {
            $nurse = new Nurse;
        }

        $governorates = Governorate::all();
        $cities = $nurse->governorate_id
            ? GovernorateCity::where('governorate_id', $nurse->governorate_id)->get()
            : collect();

        $specializations = \Modules\Specialization\Models\Specialization::active()->orderBy('name_en')->get(['id', 'name_ar', 'name_en']);
        $selectedSpecializationIds = $nurse->exists ? $nurse->specializations->pluck('id')->toArray() : [];

        return view('requestnurse::backend.nurses.create', compact('pageTitle', 'nurse', 'governorates', 'cities', 'specializations', 'selectedSpecializationIds'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$request->user_id,
            'mobile' => 'nullable|string|max:20',
            'specialization_ar' => 'nullable|string|max:255',
            'specialization_en' => 'nullable|string|max:255',
            'experience_ar' => 'nullable|string|max:255',
            'experience_en' => 'nullable|string|max:255',
            'hourly_rate' => 'required|numeric|min:0',
            'governorate_id' => 'nullable|exists:governorates,id',
            'city_id' => 'nullable|exists:governorate_cities,id',
        ]);

        $nurseData = [
            'specialization_ar' => $request->specialization_ar,
            'specialization_en' => $request->specialization_en,
            'experience_ar' => $request->experience_ar,
            'experience_en' => $request->experience_en,
            'about_ar' => $request->about_ar,
            'about_en' => $request->about_en,
            'hourly_rate' => $request->hourly_rate,
            'service_area_ar' => $request->service_area_ar,
            'service_area_en' => $request->service_area_en,
            'governorate_id' => $request->governorate_id,
            'city_id' => $request->city_id,
            'availability_status' => $request->availability_status ?? 'available',
            'status' => $request->status ?? 1,
        ];

        $nurseId = $request->id;

        if ($nurseId) {
            // Update existing nurse
            $nurse = Nurse::findOrFail($nurseId);
            $user = User::findOrFail($nurse->nurse_id);

            $user->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'mobile' => $request->mobile,
            ]);

            if ($request->filled('password')) {
                $user->update(['password' => Hash::make($request->password)]);
            }

            $nurse->update($nurseData);

            $message = __('request_nurse.nurse_update_success');
        } else {
            // Create new nurse
            $request->validate([
                'password' => 'required|min:6',
            ]);

            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'mobile' => $request->mobile,
                'password' => Hash::make($request->password),
                'user_type' => 'nurse',
            ]);
            $user->assignRole('nurse');

            $nurse = Nurse::create(array_merge($nurseData, [
                'nurse_id' => $user->id,
                'vendor_id' => auth()->user()->hasRole('vendor') ? auth()->id() : null,
            ]));

            $message = __('request_nurse.nurse_save_success');
        }

        $nurse->specializations()->sync($request->specialization_ids ?? []);

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            $nurse->clearMediaCollection('profile_image');
            $nurse->addMedia($request->file('profile_image'))->toMediaCollection('profile_image');
        }

        return redirect()->route('backend.nurses.index')->withSuccess($message);
    }

    public function destroy($id)
    {
        $nurse = Nurse::find($id);
        if ($nurse) {
            $nurse->delete();
            $msg = __('request_nurse.nurse_delete_success');
        } else {
            $msg = __('messages.msg_fail_to_delete', ['name' => __('request_nurse.nurse_singular')]);
        }

        return comman_custom_response(['message' => $msg, 'status' => $nurse ? true : false]);
    }

    public function action(Request $request)
    {
        $id = $request->id;
        $nurse = Nurse::withTrashed()->where('id', $id)->first();
        $msg = __('messages.not_found_entry', ['name' => __('request_nurse.nurse_singular')]);

        if ($request->type == 'restore') {
            $nurse->restore();
            $msg = __('request_nurse.nurse_restore');
        }

        if ($request->type === 'forcedelete') {
            $nurse->forceDelete();
            $msg = __('request_nurse.nurse_force_delete');
        }

        return comman_custom_response(['message' => $msg, 'status' => true]);
    }

    public function update_status(Request $request, Nurse $id)
    {
        $id->update(['status' => $request->status]);

        return response()->json(['status' => true, 'message' => __('request_nurse.status_update_success')]);
    }

    public function bulk_action(Request $request)
    {
        $ids = explode(',', $request->rowIds);
        $actionType = $request->action_type;
        $message = __('messages.bulk_update');

        switch ($actionType) {
            case 'change-status':
                Nurse::whereIn('id', $ids)->update(['status' => $request->status]);
                $message = __('messages.bulk_service_update');
                break;

            case 'delete':
                if (env('IS_DEMO')) {
                    return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 200);
                }
                Nurse::whereIn('id', $ids)->delete();
                $message = __('messages.bulk_service_delete');
                break;

            case 'restore':
                Nurse::withTrashed()->whereIn('id', $ids)->restore();
                $message = __('messages.bulk_service_restore');
                break;

            case 'permanently-delete':
                if (env('IS_DEMO')) {
                    return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 200);
                }
                Nurse::withTrashed()->whereIn('id', $ids)->forceDelete();
                $message = __('messages.bulk_service_permanently_deleted');
                break;

            default:
                return response()->json(['status' => false, 'message' => __('service_providers.invalid_action')]);
                break;
        }

        return response()->json(['status' => true, 'message' => $message]);
    }
}
