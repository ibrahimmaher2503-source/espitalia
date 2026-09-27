<?php

namespace Modules\DoctorVisit\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\DoctorVisit\Http\Requests\DoctorVisitStoreRequest;
use Modules\DoctorVisit\Models\DoctorVisitRequest;
use Modules\DoctorVisit\Models\DoctorVisitStatusHistory;
use Modules\DoctorVisit\Transformers\DoctorVisitRequestResource;

class DoctorVisitApiController extends Controller
{
    /**
     * US1: Submit a new doctor visit request.
     */
    public function store(DoctorVisitStoreRequest $request): JsonResponse
    {
        $visitRequest = DB::transaction(function () use ($request) {
            $visitRequest = DoctorVisitRequest::create([
                'patient_id'          => auth()->id(),
                'visit_type'          => DoctorVisitRequest::VISIT_TYPE_HOME,
                'visit_reason'        => $request->visit_reason,
                'preferred_date'      => $request->preferred_date,
                'contact_phone'       => $request->contact_phone,
                'preferred_doctor_id' => $request->preferred_doctor_id,
                'additional_notes'    => $request->additional_notes,
                'status'              => DoctorVisitRequest::STATUS_PENDING,
            ]);

            DoctorVisitStatusHistory::create([
                'visit_request_id' => $visitRequest->id,
                'old_status'       => null,
                'new_status'       => DoctorVisitRequest::STATUS_PENDING,
                'changed_by'       => auth()->id(),
                'note'             => 'Request submitted by patient.',
            ]);

            return $visitRequest;
        });

        return response()->json([
            'status'  => true,
            'data'    => new DoctorVisitRequestResource($visitRequest),
            'message' => __('doctor_visit.request_submitted_success'),
        ], 201);
    }

    /**
     * US2: List the authenticated patient's own visit requests.
     */
    public function index(): JsonResponse
    {
        $requests = DoctorVisitRequest::where('patient_id', auth()->id())
            ->with(['preferredDoctor.user', 'assignedDoctor.user'])
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15);

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
     * US2: Get full details of a specific visit request by reference number.
     */
    public function show(string $reference): JsonResponse
    {
        $visitRequest = DoctorVisitRequest::where('reference_number', $reference)->first();

        if (! $visitRequest) {
            return response()->json([
                'status'  => false,
                'message' => __('doctor_visit.request_not_found'),
            ], 404);
        }

        if ($visitRequest->patient_id !== auth()->id()) {
            return response()->json([
                'status'  => false,
                'message' => __('doctor_visit.unauthorized_status_update'),
            ], 403);
        }

        $visitRequest->load(['preferredDoctor.user', 'assignedDoctor.user', 'statusHistories.changedBy']);

        return response()->json([
            'status' => true,
            'data'   => new DoctorVisitRequestResource($visitRequest),
        ]);
    }

    /**
     * Patients may cancel only an unassigned pending request. There is no
     * fee/refund policy in the current product rules for an assigned visit,
     * so confirmed requests are deliberately left to support/admin workflow.
     */
    public function cancel(Request $request, string $reference): JsonResponse
    {
        $request->validate([
            'cancellation_reason' => ['required', 'string', 'max:500'],
            'expected_updated_at' => ['nullable', 'date'],
        ]);

        try {
            $visitRequest = DB::transaction(function () use ($request, $reference) {
                $visitRequest = DoctorVisitRequest::query()
                    ->where('reference_number', $reference)
                    ->lockForUpdate()
                    ->first();

                if (! $visitRequest) {
                    throw new \DomainException('not_found');
                }

                if ((int) $visitRequest->patient_id !== (int) auth()->id()) {
                    throw new \DomainException('forbidden');
                }

                if ($request->filled('expected_updated_at')
                    && ! $visitRequest->updated_at->equalTo(\Carbon\Carbon::parse($request->input('expected_updated_at')))) {
                    throw new \DomainException('stale');
                }

                if ($visitRequest->status !== DoctorVisitRequest::STATUS_PENDING) {
                    throw new \DomainException($visitRequest->status === DoctorVisitRequest::STATUS_CANCELLED
                        ? 'already_cancelled'
                        : 'state');
                }

                $visitRequest->update([
                    'status' => DoctorVisitRequest::STATUS_CANCELLED,
                    'cancellation_reason' => trim($request->input('cancellation_reason')),
                ]);

                DoctorVisitStatusHistory::create([
                    'visit_request_id' => $visitRequest->id,
                    'old_status' => DoctorVisitRequest::STATUS_PENDING,
                    'new_status' => DoctorVisitRequest::STATUS_CANCELLED,
                    'changed_by' => auth()->id(),
                    'note' => trim($request->input('cancellation_reason')),
                ]);

                return $visitRequest->fresh()->load([
                    'preferredDoctor.user',
                    'assignedDoctor.user',
                    'statusHistories.changedBy',
                ]);
            });
        } catch (\DomainException $e) {
            $status = match ($e->getMessage()) {
                'not_found' => 404,
                'forbidden' => 403,
                'stale' => 409,
                default => 422,
            };

            $message = match ($e->getMessage()) {
                'not_found' => __('doctor_visit.request_not_found'),
                'forbidden' => __('doctor_visit.unauthorized_status_update'),
                'stale' => __('messages.resource_changed'),
                'already_cancelled' => __('doctor_visit.request_already_cancelled'),
                default => __('doctor_visit.patient_cancel_not_allowed'),
            };

            return response()->json([
                'status' => false,
                'message' => $message,
            ], $status);
        }

        try {
            if (function_exists('sendNotificationOnBookingUpdate')) {
                sendNotificationOnBookingUpdate('doctor_visit_cancelled', [
                    'id' => $visitRequest->id,
                    'reference_number' => $visitRequest->reference_number,
                    'patient_id' => $visitRequest->patient_id,
                    'cancellation_reason' => $visitRequest->cancellation_reason,
                ]);
            }
        } catch (\Throwable $e) {
            \Log::warning('Doctor visit cancellation notification failed: '.$e->getMessage());
        }

        return response()->json([
            'status' => true,
            'message' => __('doctor_visit.request_cancelled_success'),
            'data' => new DoctorVisitRequestResource($visitRequest),
        ]);
    }
}
