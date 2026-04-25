<?php

namespace Modules\DoctorVisit\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
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
}
