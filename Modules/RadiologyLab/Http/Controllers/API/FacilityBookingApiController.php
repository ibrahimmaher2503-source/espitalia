<?php

namespace Modules\RadiologyLab\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\RadiologyLab\Http\Requests\FacilityBookingRequest;
use Modules\RadiologyLab\Http\Requests\FacilitySlotRequest;
use Modules\RadiologyLab\Models\FacilityBooking;
use Modules\RadiologyLab\Models\Lab;
use Modules\RadiologyLab\Models\RadiologyCenter;
use Modules\RadiologyLab\Services\FacilitySlotService;
use Modules\RadiologyLab\Transformers\FacilityBookingResource;

class FacilityBookingApiController extends Controller
{
    public function __construct(private FacilitySlotService $slotService) {}

    /**
     * Get available time slots for a lab on a given date.
     */
    public function labSlots(FacilitySlotRequest $request, int $labId)
    {
        $lab = Lab::where('id', $labId)->where('status', true)->first();

        if (! $lab) {
            return response()->json([
                'status' => false,
                'message' => __('radiology_lab.facility_inactive'),
            ], 422);
        }

        $date = $request->input('date');
        $slots = $this->slotService->getAvailableSlots('lab', $labId, $date);

        return response()->json([
            'status' => true,
            'data' => [
                'facility' => ['id' => $lab->id, 'name' => $lab->name, 'type' => 'lab'],
                'date' => $date,
                'day' => strtolower(Carbon::parse($date)->locale('en')->dayName),
                'slots' => $slots,
            ],
        ]);
    }

    /**
     * Get available time slots for a radiology center on a given date.
     */
    public function radiologyCenterSlots(FacilitySlotRequest $request, int $centerId)
    {
        $center = RadiologyCenter::where('id', $centerId)->where('status', true)->first();

        if (! $center) {
            return response()->json([
                'status' => false,
                'message' => __('radiology_lab.facility_inactive'),
            ], 422);
        }

        $date = $request->input('date');
        $slots = $this->slotService->getAvailableSlots('radiology', $centerId, $date);

        return response()->json([
            'status' => true,
            'data' => [
                'facility' => ['id' => $center->id, 'name' => $center->name, 'type' => 'radiology'],
                'date' => $date,
                'day' => strtolower(Carbon::parse($date)->locale('en')->dayName),
                'slots' => $slots,
            ],
        ]);
    }

    /**
     * Create a new facility booking.
     */
    public function store(FacilityBookingRequest $request)
    {
        try {
            $booking = DB::transaction(function () use ($request) {
                $type = $request->input('type');
                $facilityId = $type === 'lab' ? $request->input('lab_id') : $request->input('radiology_center_id');
                $date = $request->input('booking_date');
                $time = $request->input('booking_time');

                // Double-booking check inside transaction (pessimistic lock)
                $existing = FacilityBooking::where('type', $type)
                    ->where($type === 'lab' ? 'lab_id' : 'radiology_center_id', $facilityId)
                    ->where('booking_date', $date)
                    ->where('booking_time', $time)
                    ->where('status', '!=', FacilityBooking::STATUS_CANCELLED)
                    ->lockForUpdate()
                    ->first();

                if ($existing) {
                    throw new \RuntimeException('double_booking');
                }

                return FacilityBooking::create([
                    'type' => $type,
                    'lab_id' => $type === 'lab' ? $facilityId : null,
                    'radiology_center_id' => $type === 'radiology' ? $facilityId : null,
                    'patient_id' => auth()->id(),
                    'patient_name' => $request->input('patient_name'),
                    'patient_phone' => $request->input('patient_phone'),
                    'scan_type' => $request->input('scan_type'),
                    'booking_date' => $date,
                    'booking_time' => $time,
                    'status' => FacilityBooking::STATUS_PENDING,
                    'notes' => $request->input('notes'),
                ]);
            });
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'double_booking') {
                return response()->json([
                    'status' => false,
                    'message' => __('radiology_lab.slot_unavailable'),
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Facility booking creation failed: '.$e->getMessage());

            return response()->json([
                'status' => false,
                'message' => __('messages.something_wrong'),
            ], 500);
        }

        try {
            if (function_exists('sendNotificationOnBookingUpdate')) {
                sendNotificationOnBookingUpdate('new_facility_booking', [
                    'id' => $booking->id,
                    'user_id' => $booking->patient_id,
                    'booking_number' => $booking->booking_number,
                    'booking_date' => $booking->booking_date->format('Y-m-d'),
                    'booking_time' => $booking->booking_time,
                    'type' => $booking->type,
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Facility booking notification failed: '.$e->getMessage());
        }

        return (new FacilityBookingResource($booking->load(['lab', 'radiologyCenter'])))
            ->additional([
                'status' => true,
                'message' => __('radiology_lab.booking_created'),
            ])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * List authenticated user's bookings.
     */
    public function index(Request $request)
    {
        $query = FacilityBooking::byPatient(auth()->id())
            ->with(['lab', 'radiologyCenter'])
            ->orderBy('booking_date', 'desc');

        if ($request->filled('type')) {
            $query->byType($request->input('type'));
        }

        if ($request->filled('status')) {
            $query->byStatus($request->input('status'));
        }

        $perPage = min((int) ($request->per_page ?? 15), 50);
        $bookings = $query->paginate($perPage);

        return FacilityBookingResource::collection($bookings)->additional(['status' => true]);
    }

    /**
     * Show a single booking (patient must own it).
     */
    public function show(int $id)
    {
        $booking = FacilityBooking::with(['lab', 'radiologyCenter'])
            ->findOrFail($id);

        if ($booking->patient_id !== auth()->id()) {
            return response()->json(['status' => false, 'message' => __('messages.no_permission')], 403);
        }

        return (new FacilityBookingResource($booking))->additional(['status' => true]);
    }

    /**
     * Cancel a booking (patient cancels their own booking).
     */
    public function cancel(Request $request, int $id)
    {
        $request->validate([
            // VL-09: align with admin API which requires a reason
            'cancellation_reason' => 'required|string|max:500',
        ]);

        $booking = FacilityBooking::findOrFail($id);

        if ($booking->patient_id !== auth()->id()) {
            return response()->json(['status' => false, 'message' => __('messages.no_permission')], 403);
        }

        if (! $booking->canTransitionTo(FacilityBooking::STATUS_CANCELLED)) {
            return response()->json([
                'status' => false,
                'message' => __('radiology_lab.booking_not_cancellable'),
            ], 422);
        }

        // FN-16: store user id (int) consistently across code paths; role is inferable
        $booking->update([
            'status' => FacilityBooking::STATUS_CANCELLED,
            'cancellation_reason' => $request->input('cancellation_reason'),
            'cancelled_by' => auth()->id(),
        ]);

        return response()->json([
            'status' => true,
            'message' => __('radiology_lab.booking_cancelled'),
            'data' => ['id' => $booking->id, 'status' => $booking->status],
        ]);
    }
}
