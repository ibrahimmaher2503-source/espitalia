<?php

namespace Modules\Clinic\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\Clinic\Http\Requests\DoctorCallSessionFormRequest;
use Modules\Clinic\Models\DoctorCallSession;
use Yajra\DataTables\DataTables;

class DoctorCallSessionController extends Controller
{
    public function __construct()
    {
        // Admin permissions or doctor's own management
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if ($user->hasAnyRole(['admin', 'demo_admin'])) {
                return $next($request);
            }
            if ($user->hasRole('doctor') && $user->can('manage_own_call_sessions')) {
                return $next($request);
            }
            abort(403);
        })->only(['store', 'update', 'destroy', 'bulk_action', 'update_status', 'create']);

        $this->module_title = 'sidebar.call_sessions';
        $this->module_name = 'doctor-call-session';
        $this->module_icon = 'ph ph-clock';

        view()->share([
            'module_title' => $this->module_title,
            'module_icon' => $this->module_icon,
            'module_name' => $this->module_name,
        ]);
    }

    public function index(Request $request)
    {
        $module_action = 'List';
        $filter = ['doctor_id' => $request->doctor_id];

        return view('clinic::backend.doctor-call-session.index', compact('filter', 'module_action'));
    }

    public function index_data(Request $request)
    {
        $query = DoctorCallSession::query()->with('doctor');

        // Doctor scoping: doctors can only see their own data
        if (auth()->user()->hasRole('doctor')) {
            $query->where('doctor_id', auth()->id());
        } elseif ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        return DataTables::of($query)
            ->addColumn('doctor_name', function ($data) {
                return $data->doctor ? $data->doctor->full_name : '-';
            })
            ->addColumn('action', function ($data) {
                return view('clinic::backend.doctor-call-session.datatable.action_column', compact('data'));
            })
            ->editColumn('is_holiday', function ($data) {
                return $data->is_holiday ? '<span class="badge bg-warning">Holiday</span>' : '<span class="badge bg-success">Working</span>';
            })
            ->editColumn('status', function ($data) {
                return $data->status == 1
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>';
            })
            ->rawColumns(['action', 'is_holiday', 'status'])
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

        $weekDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        return view('clinic::backend.doctor-call-session.create', compact('module_action', 'pageTitle', 'data', 'doctors', 'weekDays'));
    }

    public function store(DoctorCallSessionFormRequest $request)
    {
        $data = $request->validated();

        // For doctor role, auto-set doctor_id
        if (auth()->user()->hasRole('doctor')) {
            $data['doctor_id'] = auth()->id();
        }

        // Delete existing session for same doctor + day, then create new
        DoctorCallSession::where('doctor_id', $data['doctor_id'])
            ->where('day', $data['day'])
            ->delete();

        $session = DoctorCallSession::create($data);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => true,
                'message' => __('messages.create_form', ['form' => __('sidebar.call_sessions')]),
                'data' => $session,
            ]);
        }

        return redirect()->route('backend.doctor-call-session.index')
            ->with('success', __('messages.create_form', ['form' => __('sidebar.call_sessions')]));
    }

    public function edit($id)
    {
        $data = DoctorCallSession::findOrFail($id);

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

        $weekDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        return view('clinic::backend.doctor-call-session.create', compact('module_action', 'pageTitle', 'data', 'doctors', 'weekDays'));
    }

    public function update(DoctorCallSessionFormRequest $request, $id)
    {
        $session = DoctorCallSession::findOrFail($id);

        // Ownership validation for doctor role
        if (auth()->user()->hasRole('doctor') && $session->doctor_id !== auth()->id()) {
            abort(403);
        }

        $data = $request->validated();

        // For doctor role, prevent changing doctor_id
        if (auth()->user()->hasRole('doctor')) {
            unset($data['doctor_id']);
        }

        $session->update($data);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => true,
                'message' => __('messages.update_form', ['form' => __('sidebar.call_sessions')]),
                'data' => $session,
            ]);
        }

        return redirect()->route('backend.doctor-call-session.index')
            ->with('success', __('messages.update_form', ['form' => __('sidebar.call_sessions')]));
    }

    public function destroy($id)
    {
        $session = DoctorCallSession::findOrFail($id);

        // Ownership validation for doctor role
        if (auth()->user()->hasRole('doctor') && $session->doctor_id !== auth()->id()) {
            abort(403);
        }

        $session->delete();

        return response()->json([
            'status' => true,
            'message' => __('messages.delete_form', ['form' => __('sidebar.call_sessions')]),
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
            $ids = DoctorCallSession::whereIn('id', $ids)
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
            DoctorCallSession::whereIn('id', $ids)->delete();
        } elseif ($action_type == 'change-status') {
            DoctorCallSession::whereIn('id', $ids)->update(['status' => $request->status]);
        }

        return response()->json([
            'status' => true,
            'message' => __('messages.action_success'),
        ]);
    }

    public function update_status($id, Request $request)
    {
        $session = DoctorCallSession::findOrFail($id);

        // Ownership validation for doctor role
        if (auth()->user()->hasRole('doctor') && $session->doctor_id !== auth()->id()) {
            abort(403);
        }

        $session->update(['status' => $request->status]);

        return response()->json([
            'status' => true,
            'message' => __('messages.status_updated'),
        ]);
    }
}
