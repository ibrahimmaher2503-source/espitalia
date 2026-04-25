<?php

namespace Modules\Clinic\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\Clinic\Http\Requests\DoctorCallServiceFormRequest;
use Modules\Clinic\Models\DoctorCallService;
use Yajra\DataTables\DataTables;

class DoctorCallServiceController extends Controller
{
    public function __construct()
    {
        // Admin permissions or doctor's own management
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if ($user->hasAnyRole(['admin', 'demo_admin'])) {
                return $next($request);
            }
            if ($user->hasRole('doctor') && $user->can('manage_own_call_services')) {
                return $next($request);
            }
            abort(403);
        })->only(['store', 'update', 'destroy', 'bulk_action', 'update_status', 'create']);

        $this->module_title = 'sidebar.call_services';
        $this->module_name = 'doctor-call-service';
        $this->module_icon = 'ph ph-phone-call';

        view()->share([
            'module_title' => $this->module_title,
            'module_icon' => $this->module_icon,
            'module_name' => $this->module_name,
        ]);
    }

    public function index(Request $request)
    {
        $module_action = 'List';
        $filter = [
            'doctor_id' => $request->doctor_id,
            'status' => $request->status,
        ];

        return view('clinic::backend.doctor-call-service.index', compact('filter', 'module_action'));
    }

    public function index_data(Request $request)
    {
        $query = DoctorCallService::query()->with('doctor');

        // Doctor scoping: doctors can only see their own data
        if (auth()->user()->hasRole('doctor')) {
            $query->where('doctor_id', auth()->id());
        } elseif ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return DataTables::of($query)
            ->addColumn('doctor_name', function ($data) {
                return $data->doctor ? $data->doctor->full_name : '-';
            })
            ->addColumn('action', function ($data) {
                return view('clinic::backend.doctor-call-service.datatable.action_column', compact('data'));
            })
            ->editColumn('charges', function ($data) {
                return number_format($data->charges, 2);
            })
            ->editColumn('call_type', function ($data) {
                return $data->call_type === 'video'
                    ? '<span class="badge bg-info"><i class="ph ph-video-camera"></i> ' . __('clinic.lbl_video_call') . '</span>'
                    : '<span class="badge bg-primary"><i class="ph ph-phone"></i> ' . __('clinic.lbl_phone_call') . '</span>';
            })
            ->editColumn('status', function ($data) {
                return $data->status == 1
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>';
            })
            ->rawColumns(['action', 'status', 'call_type'])
            ->make(true);
    }

    public function create()
    {
        $module_action = 'Create';
        $pageTitle = __('messages.add') . ' ' . __($this->module_title);
        $data = null;
        $doctors = [];

        // Admin can select any doctor, doctors are auto-assigned
        if (auth()->user()->hasAnyRole(['admin', 'demo_admin'])) {
            $doctors = User::role('doctor')->where('status', 1)->get();
        }

        $callTypes = ['video' => __('clinic.lbl_video_call'), 'phone' => __('clinic.lbl_phone_call')];

        return view('clinic::backend.doctor-call-service.create', compact('module_action', 'pageTitle', 'data', 'doctors', 'callTypes'));
    }

    public function store(DoctorCallServiceFormRequest $request)
    {
        $data = $request->validated();

        // For doctor role, auto-set doctor_id
        if (auth()->user()->hasRole('doctor')) {
            $data['doctor_id'] = auth()->id();
        }

        $service = DoctorCallService::create($data);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => true,
                'message' => __('messages.create_form', ['form' => __('sidebar.call_services')]),
                'data' => $service,
            ]);
        }

        return redirect()->route('backend.doctor-call-service.index')
            ->with('success', __('messages.create_form', ['form' => __('sidebar.call_services')]));
    }

    public function edit($id)
    {
        $data = DoctorCallService::findOrFail($id);

        // Ownership validation for doctor role
        if (auth()->user()->hasRole('doctor') && $data->doctor_id !== auth()->id()) {
            abort(403);
        }

        if (request()->wantsJson()) {
            return response()->json([
                'status' => true,
                'data' => $data,
            ]);
        }

        $module_action = 'Edit';
        $pageTitle = __('messages.edit') . ' ' . __($this->module_title);
        $doctors = [];

        if (auth()->user()->hasAnyRole(['admin', 'demo_admin'])) {
            $doctors = User::role('doctor')->where('status', 1)->get();
        }

        $callTypes = ['video' => __('clinic.lbl_video_call'), 'phone' => __('clinic.lbl_phone_call')];

        return view('clinic::backend.doctor-call-service.create', compact('module_action', 'pageTitle', 'data', 'doctors', 'callTypes'));
    }

    public function update(DoctorCallServiceFormRequest $request, $id)
    {
        $service = DoctorCallService::findOrFail($id);

        // Ownership validation for doctor role
        if (auth()->user()->hasRole('doctor') && $service->doctor_id !== auth()->id()) {
            abort(403);
        }

        $data = $request->validated();

        // For doctor role, prevent changing doctor_id
        if (auth()->user()->hasRole('doctor')) {
            unset($data['doctor_id']);
        }

        $service->update($data);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => true,
                'message' => __('messages.update_form', ['form' => __('sidebar.call_services')]),
                'data' => $service,
            ]);
        }

        return redirect()->route('backend.doctor-call-service.index')
            ->with('success', __('messages.update_form', ['form' => __('sidebar.call_services')]));
    }

    public function destroy($id)
    {
        $service = DoctorCallService::findOrFail($id);

        // Ownership validation for doctor role
        if (auth()->user()->hasRole('doctor') && $service->doctor_id !== auth()->id()) {
            abort(403);
        }

        $service->delete();

        return response()->json([
            'status' => true,
            'message' => __('messages.delete_form', ['form' => __('sidebar.call_services')]),
        ]);
    }

    public function bulk_action(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer',
            'action_type' => 'required|in:delete,change-status',
        ]);

        $ids = $request->ids;
        $action_type = $request->action_type;

        // Ownership validation for doctor role
        if (auth()->user()->hasRole('doctor')) {
            $ids = DoctorCallService::whereIn('id', $ids)
                ->where('doctor_id', auth()->id())
                ->pluck('id')
                ->toArray();
        }

        if (empty($ids)) {
            return response()->json([
                'status' => false,
                'message' => __('messages.no_permission'),
            ], 403);
        }

        if ($action_type == 'delete') {
            DoctorCallService::whereIn('id', $ids)->delete();
        } elseif ($action_type == 'change-status') {
            DoctorCallService::whereIn('id', $ids)->update(['status' => $request->status]);
        }

        return response()->json([
            'status' => true,
            'message' => __('messages.action_success'),
        ]);
    }

    public function update_status($id, Request $request)
    {
        $service = DoctorCallService::findOrFail($id);

        // Ownership validation for doctor role
        if (auth()->user()->hasRole('doctor') && $service->doctor_id !== auth()->id()) {
            abort(403);
        }

        $service->update(['status' => $request->status]);

        return response()->json([
            'status' => true,
            'message' => __('messages.status_updated'),
        ]);
    }
}
