<?php

namespace Modules\Frontend\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Clinic\Models\Doctor;
use Modules\Clinic\Models\DoctorCallService;
use Modules\Clinic\Models\DoctorCallSession;
use Modules\Clinic\Models\DoctorRating;
use Modules\Appointment\Models\Appointment;
use Modules\Appointment\Models\AppointmentTransaction;
use Modules\Appointment\Trait\AppointmentTrait;
use Modules\Clinic\Trait\CallServicePricing;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;

class CallBookingController extends Controller
{
    use AppointmentTrait;
    use CallServicePricing;

    public function index()
    {
        return view('frontend::book_call_listing');
    }

    public function index_data(Request $request)
    {
        $doctor_list = Doctor::query()
            ->where('status', 1)
            ->whereHas('doctorCallServices', function ($q) {
                $q->where('status', 1);
            })
            ->with(['user']);

        $search = $request->input('search');
        if ($search) {
            $doctor_list = $doctor_list->whereHas('user', function ($query) use ($search) {
                $query->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
            });
        }

        $doctor_list = $doctor_list->orderBy('updated_at', 'desc');

        return DataTables::of($doctor_list)
            ->addColumn('card', function ($doctor) {
                $doctor_id = optional($doctor->user)->id;
                $total_appointment = Appointment::where('doctor_id', $doctor_id)->where('status', 'checkout')->count();
                $doctor->total_appointment = $total_appointment;
                $reviews = DoctorRating::where('doctor_id', $doctor_id)->get();
                $doctor->average_rating = $reviews->avg('rating');
                return view('frontend::components.card.book_call_card', compact('doctor'))->render();
            })
            ->rawColumns(['card'])
            ->make(true);
    }

    public function bookingForm($doctor_id)
    {
        $doctor = Doctor::where('id', $doctor_id)
            ->where('status', 1)
            ->with('user')
            ->firstOrFail();

        $services = DoctorCallService::where('doctor_id', $doctor->doctor_id)
            ->where('status', 1)
            ->get();

        $paymentMethods = ['cash'];
        $gateways = [
            'stripe_payment' => 'Stripe',
            'razorpay_payment' => 'Razor Pay',
            'paypal_payment' => 'PayPal',
            'paystack_payment' => 'Paystack',
            'flutterwave_payment' => 'Flutterwave',
        ];
        foreach ($gateways as $settingKey => $label) {
            if (setting($settingKey)) {
                $paymentMethods[] = $label;
            }
        }
        if (setting('wallet_payment')) {
            $paymentMethods[] = 'Wallet';
        }

        return view('frontend::book_call_form', compact('doctor', 'services', 'paymentMethods'));
    }

    public function getCallServices($doctor_id)
    {
        $doctor = Doctor::where('id', $doctor_id)->where('status', 1)->firstOrFail();

        $services = DoctorCallService::where('doctor_id', $doctor->doctor_id)
            ->where('status', 1)
            ->get();

        return response()->json([
            'status' => true,
            'data' => $services,
        ]);
    }

    public function slotTimeList(Request $request)
    {
        $availableSlot = [];

        if ($request->filled(['appointment_date', 'doctor_id', 'call_service_id'])) {
            $doctor = Doctor::where('id', $request->doctor_id)
                ->where('status', 1)
                ->first();

            if (!$doctor) {
                return response()->json(['status' => false, 'data' => []]);
            }

            $doctor_user_id = $doctor->doctor_id;
            $timezone = new \DateTimeZone(setting('default_time_zone') ?? 'UTC');
            $carbonDate = Carbon::parse($request->appointment_date);
            $dayOfWeek = strtolower($carbonDate->locale('en')->dayName);

            // Get time slot duration from call service
            $service = DoctorCallService::find($request->call_service_id);
            $time_slot_duration = $service ? (int) $service->time_slot : 10;

            // Get doctor call session for this day
            $doctorSession = DoctorCallSession::where('doctor_id', $doctor_user_id)
                ->where('day', $dayOfWeek)
                ->where('status', 1)
                ->first();

            if ($doctorSession && !$doctorSession->is_holiday) {
                $startTime = Carbon::parse($doctorSession->start_time, $timezone);
                $endTime = Carbon::parse($doctorSession->end_time, $timezone);
                $breaks = $doctorSession->breaks ?? [];
                if (is_string($breaks)) {
                    $breaks = json_decode($breaks, true) ?? [];
                }

                // Generate time slots
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

                // Filter out booked slots — query ALL appointments (clinic + independent + call)
                $appointmentData = Appointment::where('appointment_date', $request->appointment_date)
                    ->where('doctor_id', $doctor_user_id)
                    ->where('status', '!=', 'cancelled')
                    ->get();

                $bookedSlots = [];
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

    public function saveAppointment(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'call_service_id' => 'required|exists:doctor_call_services,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|date_format:H:i',
        ]);

        $doctor = Doctor::where('id', $request->doctor_id)
            ->where('status', 1)
            ->firstOrFail();

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

        $service = DoctorCallService::find($request->call_service_id);
        $callType = $service ? $service->call_type : null;

        // Generate meeting link for video calls
        $meetingLink = null;
        if ($callType === 'video') {
            $meetingLink = 'https://meet.jit.si/espitalia-' . Str::uuid();
        }

        $data = $request->only(['doctor_id', 'call_service_id', 'appointment_date', 'appointment_time', 'transaction_type']);
        $data['user_id'] = auth()->user()->id;
        $data['doctor_id'] = $doctor->doctor_id;
        $data['clinic_id'] = null;
        $data['service_id'] = null;
        $data['call_service_id'] = $request->call_service_id;
        $data['booking_type'] = 'call';
        $data['call_type'] = $callType;
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

        // Create transaction record
        AppointmentTransaction::updateOrCreate(
            ['appointment_id' => $appointment->id],
            [
                'appointment_id' => $appointment->id,
                'transaction_type' => $data['transaction_type'] ?? 'cash',
                'total_amount' => $serviceData['total_amount'],
                'payment_status' => ($data['transaction_type'] ?? 'cash') === 'cash' ? 0 : 0,
                'discount_value' => $serviceData['discount_amount'],
                'discount_type' => $serviceData['discount_type'],
            ]
        );

        // Send notification
        $notification_data = [
            'id' => $appointment->id,
            'user_id' => $appointment->user_id,
            'doctor_id' => $appointment->doctor_id,
            'appointment_date' => Carbon::parse($appointment->appointment_date)->format('Y-m-d'),
            'appointment_time' => $appointment->appointment_time,
            'service_name' => $serviceData['service_name'],
            'booking_type' => 'call',
        ];

        try {
            $this->sendNotificationOnBookingUpdate('new_appointment', $notification_data);
        } catch (\Exception $e) {
            \Log::error('Call booking notification failed: ' . $e->getMessage());
        }

        // Handle payment routing
        $paymentMethod = $data['transaction_type'] ?? 'cash';
        $paymentHandlers = [
            'cash' => 'CashPayment',
            'Wallet' => 'WalletPayment',
            'Stripe' => 'StripePayment',
            'Razor Pay' => 'RazorpayPayment',
            'Paystack' => 'PaystackPayment',
            'PayPal' => 'PayPalPayment',
            'Flutterwave' => 'FlutterwavePayment',
        ];

        if (isset($paymentHandlers[$paymentMethod]) && method_exists($this, $paymentHandlers[$paymentMethod])) {
            $price = $serviceData['total_amount'];
            return $this->{$paymentHandlers[$paymentMethod]}($request, $appointment, $price);
        }

        return response()->json([
            'message' => __('messages.create_form', ['form' => __('frontend.call_booking')]),
            'data' => $appointment,
            'meeting_link' => $meetingLink,
            'status' => true,
        ], 200);
    }
}
