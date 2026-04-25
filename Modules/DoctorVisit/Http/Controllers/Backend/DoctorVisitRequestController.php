<?php

namespace Modules\DoctorVisit\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Clinic\Models\Doctor;
use Modules\DoctorVisit\Models\DoctorVisitRequest;
use Yajra\DataTables\DataTables;

class DoctorVisitRequestController extends Controller
{
    public function __construct()
    {
        $this->module_title = 'doctor_visit.plural_title';
        $this->module_name = 'doctor-visit';
        $this->module_icon = 'ph ph-house-line';

        // Permission middleware
        $this->middleware('permission:view_doctor_visit')->only(['index', 'index_data', 'show']);
        $this->middleware('permission:update_status_doctor_visit')->only(['update_status']);
        $this->middleware('permission:view_doctor_visit')->only(['bulk_action']);

        view()->share([
            'module_title' => $this->module_title,
            'module_icon'  => $this->module_icon,
            'module_name'  => $this->module_name,
        ]);
    }

    public function index(Request $request)
    {
        $pageTitle = trans('doctor_visit.plural_title');
        $module_name = $this->module_name;
        $assets = ['datatable'];

        $stats = [
            'total'     => DoctorVisitRequest::count(),
            'pending'   => DoctorVisitRequest::where('status', DoctorVisitRequest::STATUS_PENDING)->count(),
            'confirmed' => DoctorVisitRequest::where('status', DoctorVisitRequest::STATUS_CONFIRMED)->count(),
            'completed' => DoctorVisitRequest::where('status', DoctorVisitRequest::STATUS_COMPLETED)->count(),
        ];

        return view('doctorvisit::backend.doctor_visit_request.index', compact(
            'pageTitle', 'module_name', 'assets', 'stats'
        ));
    }

    public function index_data(DataTables $datatable, Request $request)
    {
        $query = DoctorVisitRequest::with(['patient', 'assignedDoctor.user']);

        // Role-based filtering: doctor sees only their assigned requests
        if (auth()->user()->hasRole('doctor')) {
            $query->whereHas('assignedDoctor', function ($q) {
                $q->where('doctor_id', auth()->id());
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $datatable->eloquent($query)
            ->addColumn('check', fn ($row) => '<input type="checkbox" class="form-check-input select-table-row" name="datatable_ids[]" value="'.$row->id.'">')
            ->addColumn('patient_name', fn ($row) => optional($row->patient)->full_name ?? '-')
            ->addColumn('assigned_doctor_name', fn ($row) => optional(optional($row->assignedDoctor)->user)->full_name ?? '-')
            ->addColumn('status_badge', fn ($row) => view('doctorvisit::backend.doctor_visit_request.datatable.action_column', ['row' => $row])->render())
            ->addColumn('action', fn ($row) => '<a href="'.route('backend.doctor_visit.show', $row->id).'" class="btn btn-sm btn-primary"><i class="ph ph-eye"></i></a>')
            ->rawColumns(['check', 'status_badge', 'action'])
            ->make(true);
    }

    public function show(int $id)
    {
        $query = DoctorVisitRequest::with([
            'patient',
            'preferredDoctor.user',
            'assignedDoctor.user',
            'statusHistories.changedBy',
        ]);

        // Doctors can only view their own assigned requests
        if (auth()->user()->hasRole('doctor')) {
            $query->whereHas('assignedDoctor', fn ($q) => $q->where('doctor_id', auth()->id()));
        }

        $visitRequest = $query->findOrFail($id);

        $pageTitle = trans('doctor_visit.singular_title').' — '.$visitRequest->reference_number;
        $module_name = $this->module_name;

        $validTransitions = DoctorVisitRequest::VALID_TRANSITIONS[$visitRequest->status] ?? [];
        $doctors = Doctor::with('user')->whereHas('user', fn ($q) => $q->where('status', 1))->get();

        return view('doctorvisit::backend.doctor_visit_request.show', compact(
            'visitRequest', 'pageTitle', 'module_name', 'validTransitions', 'doctors'
        ));
    }

    public function update_status(Request $request, int $id)
    {
        $request->validate([
            'status' => ['required', 'string', 'in:confirmed,cancelled,completed'],
        ]);

        $visitRequest = DoctorVisitRequest::findOrFail($id);
        $currentStatus = $visitRequest->status;
        $newStatus = $request->status;
        $validNext = DoctorVisitRequest::VALID_TRANSITIONS[$currentStatus] ?? [];

        if (! in_array($newStatus, $validNext)) {
            return response()->json([
                'status'  => false,
                'message' => __('doctor_visit.invalid_status_transition'),
            ], 422);
        }

        $updates = ['status' => $newStatus];
        if ($newStatus === DoctorVisitRequest::STATUS_COMPLETED) {
            $updates['completed_at'] = now();
        }
        if ($newStatus === DoctorVisitRequest::STATUS_CANCELLED && $request->filled('cancellation_reason')) {
            $updates['cancellation_reason'] = $request->cancellation_reason;
        }

        $visitRequest->update($updates);

        // Record status history (parity with API controller)
        \Modules\DoctorVisit\Models\DoctorVisitStatusHistory::create([
            'visit_request_id' => $visitRequest->id,
            'old_status'       => $currentStatus,
            'new_status'       => $newStatus,
            'changed_by'       => auth()->id(),
            'note'             => $request->input('note'),
        ]);

        // Email patient on key status changes
        if (in_array($newStatus, [
            DoctorVisitRequest::STATUS_CONFIRMED,
            DoctorVisitRequest::STATUS_CANCELLED,
            DoctorVisitRequest::STATUS_COMPLETED,
        ])) {
            $visitRequest->load('patient');
            if ($visitRequest->patient && $visitRequest->patient->email) {
                \Illuminate\Support\Facades\Mail::to($visitRequest->patient->email)
                    ->send(new \App\Mail\DoctorVisitStatusChanged($visitRequest));
            }
        }

        return response()->json([
            'status'  => true,
            'message' => __('doctor_visit.status_updated_success'),
        ]);
    }

    public function bulk_action(Request $request)
    {
        $request->validate([
            'ids'         => ['required', 'array'],
            'action_type' => ['required', 'string', 'in:delete'],
        ]);

        if ($request->action_type === 'delete') {
            DoctorVisitRequest::whereIn('id', $request->ids)->delete();
        }

        return response()->json([
            'status'  => true,
            'message' => __('messages.bulk_action_success'),
        ]);
    }
}
