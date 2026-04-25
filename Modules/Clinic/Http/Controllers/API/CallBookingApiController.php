<?php

namespace Modules\Clinic\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Clinic\Models\Doctor;
use Modules\Clinic\Models\DoctorCallService;
use Modules\Clinic\Models\DoctorCallSession;
use Modules\Clinic\Models\DoctorRating;
use Modules\Clinic\Transformers\DoctorResource;
use Modules\Clinic\Transformers\DoctorCallServiceResource;
use Modules\Appointment\Models\Appointment;
use Modules\Appointment\Models\AppointmentTransaction;
use Modules\Appointment\Trait\AppointmentTrait;
use Modules\Clinic\Trait\CallServicePricing;
use Carbon\Carbon;

class CallBookingApiController extends Controller
{
    use AppointmentTrait;
    use CallServicePricing;

    /**
     * List doctors who have call services.
     */
    public function doctors(Request $request)
    {
        $query = Doctor::where('status', 1)
            ->whereHas('doctorCallServices', function ($q) {
                $q->where('status', 1);
            })
            ->with(['user', 'doctorService', 'doctorclinic', 'doctorReviews', 'doctorCallServices' => function ($q) {
                $q->where('status', 1);
            }]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
            });
        }

        $perPage = min((int) ($request->per_page ?? 15), 50);
        $data = $query->orderBy('updated_at', 'desc')->paginate($perPage);

        return DoctorResource::collection($data)->additional([
            'status' => true,
        ]);
    }

    /**
     * Get call services for a specific doctor.
     */
    public function services($doctorId)
    {
        $doctor = Doctor::where('id', $doctorId)->where('status', 1)->firstOrFail();

        $services = DoctorCallService::where('doctor_id', $doctor->doctor_id)
            ->where('status', 1)
            ->get();

        return response()->json([
            'status' => true,
            'data' => DoctorCallServiceResource::collection($services),
        ]);
    }

    /**
     * Get available time slots for a doctor on a given date.
     */
    public function slots(Request $request, $doctorId)
    {
        $request->validate([
            'appointment_date' => 'required|date',
            'call_service_id' => 'required|exists:doctor_call_services,id',
        ]);

        $doctor = Doctor::where('id', $doctorId)->where('status', 1)->firstOrFail();
        $doctor_user_id = $doctor->doctor_id;
        $timezone = new \DateTimeZone(setting('default_time_zone') ?? 'UTC');
        $carbonDate = Carbon::parse($request->appointment_date);
        $dayOfWeek = strtolower($carbonDate->locale('en')->dayName);

        $service = DoctorCallService::find($request->call_service_id);
        $time_slot_duration = $service ? (int) $service->time_slot : 10;

        $doctorSession = DoctorCallSession::where('doctor_id', $doctor_user_id)
            ->where('day', $dayOfWeek)
            ->where('status', 1)
            ->first();

        $availableSlot = [];

        if ($doctorSession && !$doctorSession->is_holiday) {
            $startTime = Carbon::parse($doctorSession->start_time, $timezone);
            $endTime = Carbon::parse($doctorSession->end_time, $timezone);
            $breaks = $doctorSession->breaks ?? [];

            if (is_string($breaks)) {
                $breaks = json_decode($breaks, true) ?? [];
            }

            $timeSlots = [];
            $current = $startTime->copy();
            while ($current < $endTime) {
                $inBreak = false;
                if (is_array($breaks)) {
                    foreach ($breaks as $break) {
                        $breakStart = Carbon::parse($break['start_break'] ?? $break['start'] ?? '00:00', $timezone);
                        $breakEnd = Carbon::parse($break['end_break'] ?? $break['end'] ?? '00:00', $timezone);
                        if ($current >= $breakStart && $current < $breakEnd) {
                            $inBreak = true;
                            break;
                        }
                    }
                }
                if (!$inBreak) {
                    $timeSlots[] = $current->format('H:i');
                }
                $current->addMinutes($time_slot_duration);
            }

            // Check doctor holidays
            if (class_exists(\App\Models\DoctorHoliday::class)) {
                $doctorHoliday = \App\Models\DoctorHoliday::where('doctor_id', $doctor_user_id)
                    ->where('date', $request->appointment_date)
                    ->first();
                if ($doctorHoliday) {
                    return response()->json(['status' => true, 'data' => []]);
                }
            }

            // Filter out booked slots
            $bookedSlots = [];
            $appointmentData = Appointment::where('appointment_date', $request->appointment_date)
                ->where('doctor_id', $doctor_user_id)
                ->where('status', '!=', 'cancelled')
                ->get();

            foreach ($appointmentData as $appointment) {
                $appointmentStart = Carbon::parse($appointment->appointment_time);
                $appointmentDuration = $appointment->duration ?? 0;
                $appointmentEnd = $appointmentStart->copy()->addMinutes($appointmentDuration);

                $slotCheck = $appointmentStart->copy();
                while ($slotCheck < $appointmentEnd) {
                    $bookedSlots[] = $slotCheck->format('H:i');
                    $slotCheck->addMinutes($time_slot_duration);
                }
            }

            $availableSlot = array_values(array_diff($timeSlots, $bookedSlots));

            // Filter past slots if booking for today
            $now = Carbon::now($timezone);
            if ($carbonDate->isToday()) {
                $availableSlot = array_values(array_filter($availableSlot, function ($slot) use ($now, $timezone) {
                    return Carbon::parse($slot, $timezone)->gt($now);
                }));
            }
        }

        return response()->json([
            'status' => true,
            'data' => collect($availableSlot)->map(function ($slot) {
                return [
                    'value' => $slot,
                    'label' => Carbon::parse($slot)->format(setting('time_format') ?? 'h:i A'),
                ];
            })->values(),
        ]);
    }

    /**
     * Create a call booking appointment.
     */
    public function store(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'call_service_id' => 'required|exists:doctor_call_services,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|date_format:H:i',
            'transaction_type' => 'nullable|string|in:cash,stripe,razorpay,paypal,paystack,flutterwave,wallet',
        ]);

        $doctor = Doctor::where('id', $request->doctor_id)
            ->where('status', 1)
            ->firstOrFail();

        $service = DoctorCallService::findOrFail($request->call_service_id);
        $serviceData = $this->getCallServiceAmount($request->call_service_id);

        // Double-booking prevention
        $existingAppointment = Appointment::where('doctor_id', $doctor->doctor_id)
            ->where('appointment_date', $request->appointment_date)
            ->where('appointment_time', $request->appointment_time)
            ->where('status', '!=', 'cancelled')
            ->first();
        if ($existingAppointment) {
            return response()->json(['message' => __('frontend.lbl_no_slots_available'), 'status' => false], 422);
        }

        // Generate meeting link for video calls
        $meetingLink = null;
        if ($service->call_type === 'video') {
            $meetingLink = 'https://meet.jit.si/espitalia-' . Str::uuid();
        }

        $data = $request->only(['doctor_id', 'call_service_id', 'appointment_date', 'appointment_time', 'transaction_type']);
        $data['user_id'] = auth()->user()->id;
        $data['doctor_id'] = $doctor->doctor_id;
        $data['clinic_id'] = null;
        $data['service_id'] = null;
        $data['call_service_id'] = $request->call_service_id;
        $data['booking_type'] = 'call';
        $data['call_type'] = $service->call_type;
        $data['meeting_link'] = $meetingLink;
        $data['service_price'] = $serviceData['service_price'];
        $data['service_amount'] = $serviceData['service_amount'];
        $data['total_amount'] = $serviceData['total_amount'];
        $data['duration'] = $serviceData['duration'];
        $data['status'] = 'confirmed';
        $data['advance_payment_amount'] = 0;
        $data['advance_paid_amount'] = 0;
        $data['start_date_time'] = Carbon::parse($data['appointment_date'] . ' ' . $data['appointment_time'])
            ->format('Y-m-d H:i:s');

        $appointment = Appointment::create($data);

        AppointmentTransaction::updateOrCreate(
            ['appointment_id' => $appointment->id],
            [
                'appointment_id' => $appointment->id,
                'transaction_type' => $data['transaction_type'] ?? 'cash',
                'total_amount' => $serviceData['total_amount'],
                'payment_status' => 0,
                'discount_value' => $serviceData['discount_amount'],
                'discount_type' => $serviceData['discount_type'],
            ]
        );

        // Send notification
        try {
            $this->sendNotificationOnBookingUpdate('new_appointment', [
                'id' => $appointment->id,
                'user_id' => $appointment->user_id,
                'doctor_id' => $appointment->doctor_id,
                'appointment_date' => Carbon::parse($appointment->appointment_date)->format('Y-m-d'),
                'appointment_time' => $appointment->appointment_time,
                'service_name' => $serviceData['service_name'],
                'booking_type' => 'call',
            ]);
        } catch (\Exception $e) {
            \Log::error('Call booking API notification failed: ' . $e->getMessage());
        }

        return response()->json([
            'message' => __('messages.create_form', ['form' => __('frontend.call_booking')]),
            'data' => $appointment->load('user'),
            'meeting_link' => $meetingLink,
            'call_type' => $service->call_type,
            'service_name' => $service->name,
            'duration' => $service->duration_min,
            'total_amount' => $serviceData['total_amount'],
            'status' => true,
        ], 200);
    }
}
