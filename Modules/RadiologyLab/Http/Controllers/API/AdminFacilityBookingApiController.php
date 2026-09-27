<?php

namespace Modules\RadiologyLab\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\RadiologyLab\Models\FacilityBooking;
use Modules\RadiologyLab\Transformers\FacilityBookingResource;

class AdminFacilityBookingApiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:view_facility_bookings')->only('index');
        $this->middleware('permission:manage_facility_bookings')->only(['confirm', 'cancel', 'updateStatus']);
    }

    /**
     * List all bookings with optional filters.
     */
    public function index(Request $request)
    {
        $query = FacilityBooking::with(['patient', 'lab', 'radiologyCenter'])
            ->orderBy('booking_date', 'desc');

        if ($request->filled('type')) {
            $query->byType($request->input('type'));
        }

        if ($request->filled('status')) {
            $query->byStatus($request->input('status'));
        }

        if ($request->filled('date_from')) {
            $query->where('booking_date', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('booking_date', '<=', $request->input('date_to'));
        }

        if ($request->filled('facility_id')) {
            $facilityId = $request->input('facility_id');
            $type = $request->input('type');
            if ($type === 'lab') {
                $query->where('lab_id', $facilityId);
            } elseif ($type === 'radiology') {
                $query->where('radiology_center_id', $facilityId);
            } else {
                $query->where(function ($q) use ($facilityId) {
                    $q->where('lab_id', $facilityId)
                        ->orWhere('radiology_center_id', $facilityId);
                });
            }
        }

        $perPage = min((int) ($request->per_page ?? 15), 50);
        $bookings = $query->paginate($perPage);

        return FacilityBookingResource::collection($bookings)->additional(['status' => true]);
    }

    /**
     * Confirm a pending booking.
     */
    public function confirm(Request $request, int $id)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $booking = FacilityBooking::findOrFail($id);

        if (! $booking->canTransitionTo(FacilityBooking::STATUS_CONFIRMED)) {
            return response()->json([
                'status' => false,
                'message' => __('radiology_lab.invalid_transition'),
            ], 422);
        }

        $booking->update([
            'status' => FacilityBooking::STATUS_CONFIRMED,
            'admin_notes' => $request->input('admin_notes'),
        ]);

        try {
            if (function_exists('sendNotificationOnBookingUpdate')) {
                sendNotificationOnBookingUpdate('facility_booking_confirmed', [
                    'id' => $booking->id,
                    'user_id' => $booking->patient_id,
                    'booking_number' => $booking->booking_number,
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Facility booking confirm notification failed: ' . $e->getMessage());
        }

        return response()->json([
            'status' => true,
            'message' => __('radiology_lab.booking_confirmed'),
            'data' => ['id' => $booking->id, 'status' => $booking->status],
        ]);
    }

    /**
     * Admin cancels a booking (requires reason).
     */
    public function cancel(Request $request, int $id)
    {
        $request->validate([
            'cancellation_reason' => 'required|string|max:500',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        try {
            $booking = DB::transaction(function () use ($request, $id) {
                $booking = FacilityBooking::query()->lockForUpdate()->findOrFail($id);

                if (! $booking->canTransitionTo(FacilityBooking::STATUS_CANCELLED)) {
                    throw new \DomainException('not_cancellable');
                }

                // FN-16: store user id (int) consistently across code paths.
                $booking->update([
                    'status' => FacilityBooking::STATUS_CANCELLED,
                    'cancellation_reason' => trim($request->input('cancellation_reason')),
                    'cancelled_by' => auth()->id(),
                    'admin_notes' => $request->input('admin_notes'),
                ]);

                return $booking->fresh();
            });
        } catch (\DomainException $e) {
            return response()->json([
                'status' => false,
                'message' => __('radiology_lab.booking_not_cancellable'),
            ], 422);
        }

        try {
            if (function_exists('sendNotificationOnBookingUpdate')) {
                sendNotificationOnBookingUpdate('facility_booking_cancelled', [
                    'id' => $booking->id,
                    'user_id' => $booking->patient_id,
                    'booking_number' => $booking->booking_number,
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Facility booking cancel notification failed: ' . $e->getMessage());
        }

        return response()->json([
            'status' => true,
            'message' => __('radiology_lab.booking_cancelled'),
            'data' => ['id' => $booking->id, 'status' => $booking->status],
        ]);
    }

    /**
     * Update booking status to completed or no_show.
     */
    public function updateStatus(Request $request, int $id)
    {
        $request->validate([
            'status' => 'required|in:completed,no_show',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $booking = FacilityBooking::findOrFail($id);
        $newStatus = $request->input('status');

        if (! $booking->canTransitionTo($newStatus)) {
            return response()->json([
                'status' => false,
                'message' => __('radiology_lab.invalid_transition'),
            ], 422);
        }

        $booking->update([
            'status' => $newStatus,
            'admin_notes' => $request->input('admin_notes'),
        ]);

        $messageKey = $newStatus === FacilityBooking::STATUS_COMPLETED
            ? 'booking_completed'
            : 'booking_no_show';

        return response()->json([
            'status' => true,
            'message' => __('radiology_lab.' . $messageKey),
            'data' => ['id' => $booking->id, 'status' => $booking->status],
        ]);
    }
}
