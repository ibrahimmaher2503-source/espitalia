<?php

namespace Modules\DoctorVisit\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\DoctorVisitStatusChanged;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Modules\DoctorVisit\Http\Requests\DoctorVisitStatusUpdateRequest;
use Modules\DoctorVisit\Models\DoctorVisitRequest;
use Modules\DoctorVisit\Models\DoctorVisitStatusHistory;
use Modules\DoctorVisit\Transformers\DoctorVisitRequestResource;

class AdminDoctorVisitApiController extends Controller
{
    /**
     * Check whether the given user may manage (update/note) a visit request.
     * - Admin or receptionist: always yes.
     * - Doctor role: only if they are the assigned doctor.
     */
    private function canManageRequest(User $user, DoctorVisitRequest $req): bool
    {
        if ($user->hasRole(['admin', 'receptionist'])) {
            return true;
        }

        if ($user->hasRole('doctor') && $req->assigned_doctor_id !== null) {
            $assignedDoctor = $req->assignedDoctor;

            return $assignedDoctor && $assignedDoctor->doctor_id === $user->id;
        }

        return false;
    }

    /**
     * Guard: only admin/receptionist/doctor may access admin endpoints.
     */
    private function requireStaffRole(): ?JsonResponse
    {
        if (! auth()->user()->hasAnyRole(['admin', 'receptionist', 'doctor'])) {
            return response()->json([
                'status'  => false,
                'message' => __('doctor_visit.unauthorized_status_update'),
            ], 403);
        }

        return null;
    }

    /**
     * US3: List all visit requests with optional filters.
     */
    public function index(Request $request): JsonResponse
    {
        if ($guard = $this->requireStaffRole()) {
            return $guard;
        }

        // Validate filter inputs
        $request->validate([
            'status' => ['nullable', 'string', 'in:pending,confirmed,cancelled,completed'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'assigned_doctor_id' => ['nullable', 'integer'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = DoctorVisitRequest::with(['patient', 'preferredDoctor.user', 'assignedDoctor.user'])
            ->orderBy('created_at', 'desc');

        // C1 fix: Doctors can only see their own assigned requests
        if (auth()->user()->hasRole('doctor')) {
            $query->whereHas('assignedDoctor', function ($q) {
                $q->where('doctor_id', auth()->id());
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('preferred_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('preferred_date', '<=', $request->date_to);
        }

        if ($request->filled('assigned_doctor_id')) {
            $query->where('assigned_doctor_id', $request->assigned_doctor_id);
        }

        $perPage = min($request->integer('per_page', 15), 100);
        $requests = $query->paginate($perPage);

        return response()->json([
            'status' => true,
            'data'   => DoctorVisitRequestResource::collection($requests),
            'meta'   => [
                'current_page' => $requests->currentPage(),
                'per_page'     => $requests->perPage(),
                'total'        => $requests->total(),
                'last_page'    => $requests->lastPage(),
            ],
        ]);
    }

    /**
     * US3: Update the status of a visit request.
     */
    public function updateStatus(DoctorVisitStatusUpdateRequest $request, string $reference): JsonResponse
    {
        $visitRequest = DoctorVisitRequest::where('reference_number', $reference)->first();

        if (! $visitRequest) {
            return response()->json([
                'status'  => false,
                'message' => __('doctor_visit.request_not_found'),
            ], 404);
        }

        if (! $this->canManageRequest(auth()->user(), $visitRequest)) {
            return response()->json([
                'status'  => false,
                'message' => __('doctor_visit.unauthorized_status_update'),
            ], 403);
        }

        $newStatus = $request->status;
        $currentStatus = $visitRequest->status;
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

        DoctorVisitStatusHistory::create([
            'visit_request_id' => $visitRequest->id,
            'old_status'       => $currentStatus,
            'new_status'       => $newStatus,
            'changed_by'       => auth()->id(),
            'note'             => $request->note,
        ]);

        // Email patient on confirmed / cancelled / completed
        if (in_array($newStatus, [
            DoctorVisitRequest::STATUS_CONFIRMED,
            DoctorVisitRequest::STATUS_CANCELLED,
            DoctorVisitRequest::STATUS_COMPLETED,
        ])) {
            $visitRequest->load('patient');
            if ($visitRequest->patient && $visitRequest->patient->email) {
                Mail::to($visitRequest->patient->email)->send(new DoctorVisitStatusChanged($visitRequest));
            }
        }

        $visitRequest->load(['patient', 'preferredDoctor.user', 'assignedDoctor.user', 'statusHistories.changedBy']);

        return response()->json([
            'status'  => true,
            'data'    => new DoctorVisitRequestResource($visitRequest),
            'message' => __('doctor_visit.status_updated_success'),
        ]);
    }

    /**
     * US3: Assign or reassign a doctor to a visit request.
     */
    public function assignDoctor(Request $request, string $reference): JsonResponse
    {
        if (! auth()->user()->hasRole(['admin', 'receptionist'])) {
            return response()->json([
                'status'  => false,
                'message' => __('doctor_visit.unauthorized_status_update'),
            ], 403);
        }

        $request->validate([
            'doctor_id' => ['required', 'integer', 'exists:doctors,id'],
        ]);

        $visitRequest = DoctorVisitRequest::where('reference_number', $reference)->first();

        if (! $visitRequest) {
            return response()->json([
                'status'  => false,
                'message' => __('doctor_visit.request_not_found'),
            ], 404);
        }
        $visitRequest->update(['assigned_doctor_id' => $request->doctor_id]);

        $visitRequest->load(['patient', 'preferredDoctor.user', 'assignedDoctor.user']);

        return response()->json([
            'status'  => true,
            'data'    => new DoctorVisitRequestResource($visitRequest),
            'message' => __('doctor_visit.doctor_assigned_success'),
        ]);
    }

    /**
     * US3: Add or update internal admin notes.
     */
    public function addNote(Request $request, string $reference): JsonResponse
    {
        $visitRequest = DoctorVisitRequest::where('reference_number', $reference)->first();

        if (! $visitRequest) {
            return response()->json([
                'status'  => false,
                'message' => __('doctor_visit.request_not_found'),
            ], 404);
        }

        if (! $this->canManageRequest(auth()->user(), $visitRequest)) {
            return response()->json([
                'status'  => false,
                'message' => __('doctor_visit.unauthorized_status_update'),
            ], 403);
        }

        $request->validate([
            'admin_notes' => ['required', 'string', 'max:2000'],
        ]);

        $visitRequest->update(['admin_notes' => $request->admin_notes]);
        $visitRequest->load(['patient', 'preferredDoctor.user', 'assignedDoctor.user']);

        return response()->json([
            'status'  => true,
            'data'    => new DoctorVisitRequestResource($visitRequest),
            'message' => __('doctor_visit.notes_updated_success'),
        ]);
    }
}
