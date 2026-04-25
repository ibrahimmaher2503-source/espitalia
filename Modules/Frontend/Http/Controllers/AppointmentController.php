<?php

namespace Modules\Frontend\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Tax\Models\Tax;
use Modules\Clinic\Models\Doctor;
use Modules\Clinic\Models\DoctorSession;
use Carbon\Carbon;
use Modules\Appointment\Models\Appointment;
use App\Models\Holiday;
use App\Models\User;
use App\Models\DoctorHoliday;
use App\Models\Setting;
use Modules\Clinic\Models\ClinicsService;
use Modules\Clinic\Models\Clinics;
use Modules\Clinic\Models\ClinicsCategory;
use Yajra\DataTables\DataTables;
use PDF;
use Modules\Appointment\Models\AppointmentTransaction;
use Modules\Appointment\Trait\AppointmentTrait;
use Modules\Wallet\Models\Wallet;
use Modules\Wallet\Models\WalletHistory;
use Modules\Appointment\Models\EncouterMedicalHistroy;
use Modules\Appointment\Models\EncounterMedicalReport;
use Modules\Appointment\Models\AppointmentPatientBodychart;
use Modules\Appointment\Models\AppointmentPatientRecord;
use Modules\Currency\Models\Currency;
use Modules\Appointment\Models\EncounterPrescription;
use Modules\Appointment\Models\PatientEncounter;
use Illuminate\Support\Facades\DB;
use Stripe\StripeClient;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use Illuminate\Support\Str;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Modules\Customer\Models\OtherPatient;

class AppointmentController extends Controller
{
    use AppointmentTrait;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('frontend::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('frontend::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        //
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('frontend::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('frontend::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }

    public function appointmentList()
    {
        $appointments = Appointment::CheckMultivendor()
            ->with(['appointmenttransaction', 'cliniccenter', 'clinicservice', 'user', 'doctor', 'patientEncounter'])
            ->get()
            ->map(function ($appointment) {
                return [
                    'id' => $appointment->id,
                    'data' => $appointment
                ];
            });

        $doctors = Doctor::CheckMultivendor()->with('user')->get();

        return view('frontend::appointments', compact('appointments', 'doctors'));
    }

    public function index_data(Request $request)
    {
        $appointment_list = Appointment::CheckMultivendor()->with('appointmenttransaction', 'cliniccenter', 'clinicservice', 'independentService', 'user', 'doctor');

        if (auth()->user()) {
            $appointment_list = $appointment_list->where('user_id', auth()->id());
        }
        $filter = $request->filter;
        // Get the activeTab value from the frontend
        $activeTab = $request->input('activeTab');
        if (isset($filter['activeTab'])) {
            $activeTab = $filter['activeTab'];
            if ($activeTab == "upcoming-appointments") {
                $appointment_list = $appointment_list->whereNotIn('status', ['checkout', 'cancelled']);
            } else if ($activeTab == "completed-appointments") {
                $appointment_list->where('status', 'checkout');
            }
        }

        if (isset($filter['doctor_id'])) {
            $doctorId = $filter['doctor_id'];
            $appointment_list = $appointment_list->where('doctor_id', $doctorId);
        }

        $appointments = $appointment_list->orderBy('updated_at', 'desc');

        return DataTables::of($appointments)
            ->addColumn('card', function ($appointment) {
                $medical_history = EncouterMedicalHistroy::where('encounter_id', optional($appointment->patientEncounter)->id)->get()->groupBy('type');
                $medical_report = EncounterMedicalReport::where('encounter_id', optional($appointment->patientEncounter)->id)->first();
                $prescriptions = EncounterPrescription::where('encounter_id', optional($appointment->patientEncounter)->id)->get();
                $bodychart = AppointmentPatientBodychart::where('appointment_id', $appointment->id)->get();
                $soap = AppointmentPatientRecord::where('encounter_id', optional($appointment->patientEncounter)->id)->first();

                return view('frontend::components.card.appointment_card', compact('appointment', 'medical_history', 'medical_report', 'prescriptions', 'bodychart', 'soap'))->render();
            })
            ->rawColumns(['card'])
            ->make(true);
    }

    public function appointmentDetails($id)
    {
        $appointment = Appointment::setRole(auth()->user())->with('appointmenttransaction', 'clinicservice', 'independentService', 'serviceRating', 'patientEncounter', 'cliniccenter', 'bodyChart')->where('id', $id)->first();

        if (!$appointment) {
            return redirect()->route('appointment-list')->with('error', 'Appointment not found!');
        }

        $medical_history = EncouterMedicalHistroy::where('encounter_id', optional($appointment->patientEncounter)->id)->get()->groupBy('type');
        $medical_report = EncounterMedicalReport::where('encounter_id', optional($appointment->patientEncounter)->id)->first();
        $prescriptions = EncounterPrescription::where('encounter_id', optional($appointment->patientEncounter)->id)->get();

        $bodychart = AppointmentPatientBodychart::where('encounter_id', optional($appointment->patientEncounter)->id)->get();

        $soap = AppointmentPatientRecord::where('encounter_id', optional($appointment->patientEncounter)->id)->first();


        $review = $appointment->serviceRating?->where('user_id', auth()->user()->id)->first();

        $currency = Currency::where('is_primary', 1)->first();
        $currencySymbol = $currency ? $currency->currency_symbol : '$';

        // Get tax data either from stored percentage or calculate new
        $tax_percentage = null;
        if ($appointment->appointmenttransaction) {
            $tax_percentage = json_decode($appointment->appointmenttransaction->tax_percentage, true);
        } else {
            $tax_percentage = $appointment->appointmenttransaction
                ? $this->calculateTaxAmounts(null, $appointment->appointmenttransaction->total_amount)
                : [];
        }

        $service_tax = null;
        $gst = null;
        $tax = $tax_percentage;
        // Extract the service tax and gst values from the tax data
        foreach ($tax_percentage as $tax) {
            if ($tax['title'] == 'Service Tax') {
                $service_tax = $tax['value'];
            }
            if ($tax['title'] == 'GST') {
                $gst = $tax['value'];
            }
        }

        $total_tax = $service_tax + $gst;
        $advancePaid = $appointment->advance_paid_amount > 0;

        $paymentMethodsList = [
            'cash' => 'cash_payment_method',
            'Wallet' => 'wallet_payment_method',
            'Stripe' => 'str_payment_method',
            'Paystack' => 'paystack_payment_method',
            'PayPal' => 'paypal_payment_method',
            'Flutterwave' => 'flutterwave_payment_method',
            'Airtel' => 'airtel_payment_method',
            'PhonePay' => 'phonepay_payment_method',
            'Midtrans' => 'midtrans_payment_method',
            'Cinet' => 'cinet_payment_method',
            'Sadad' => 'sadad_payment_method',
            'Razor' => 'razor_payment_method',
        ];

        $paymentMethods = ['Wallet'];
        foreach ($paymentMethodsList as $displayName => $settingKey) {
            if (setting($settingKey, 0) == 1) {
                $paymentMethods[] = $displayName;
            }
        }
        $appointment->currency_symbol = $currencySymbol;

        $medical_reports = EncounterMedicalReport::where('encounter_id', optional($appointment->patientEncounter)->id)->get();

        return view('frontend::appointment_detail', compact('appointment', 'medical_history', 'medical_report', 'prescriptions', 'tax_percentage', 'review', 'advancePaid', 'paymentMethods', 'bodychart', 'soap','medical_reports'));
    }

    public function encounterList()
    {
        return view('frontend::encounters');
    }



    public function encounter_index_data()
    {
        $encounter_list = PatientEncounter::SetRole(auth()->user())
            ->with(['appointment', 'user', 'clinic', 'doctor'])
            ->get();

        return DataTables::of($encounter_list)
            ->addColumn('card', function ($encounter) {
                $medical_history = EncouterMedicalHistroy::where('encounter_id', $encounter->id)->get()->groupBy('type');
                $medical_report = EncounterMedicalReport::where('encounter_id', $encounter->id)->first();
                $prescriptions = EncounterPrescription::where('encounter_id', $encounter->id)->get();
                $bodychart = AppointmentPatientBodychart::where('appointment_id', optional($encounter->appointment)->id)->get();
                $soap = AppointmentPatientRecord::where('encounter_id', $encounter->id)->first();
                return view('frontend::components.card.encounter_card', compact('encounter', 'bodychart', 'soap', 'medical_history', 'medical_report', 'prescriptions'))->render();
            })
            ->rawColumns(['card'])
            ->make(true);

        return view('frontend::encounters');
    }

    public function getPaymentData(Request $request)
    {
        // Initialize default values
        $service_charge = 0;
        $discount_amount = 0;
        $doctorService = null;

        // Check if both selectedService and selectedDoctor are provided
        if ($request->has('selectedService') && $request->has('selectedDoctor')) {
            $serviceId = $request->input('selectedService');
            $doctorId = $request->input('selectedDoctor');
            $doctor = Doctor::CheckMultivendor()->where('id', $doctorId)->where('status', 1)->first();
            $doctor_id = $doctor->doctor_id;
            $inclusive_tax = null;
            $data = ClinicsService::where('id', $serviceId)
                ->with([
                    'doctor_service' => function ($query) use ($doctor_id) {
                        $query->where('doctor_id', $doctor_id);
                    }
                ])
                ->first();
            if ($data && $data->doctor_service->isNotEmpty()) {
                $doctorService = $data->doctor_service->first();
                $service_charge = $doctorService->charges;
                if ($data->discount == 1) {
                    $discount_amount = ($data->discount_type == 'percentage')
                        ? $service_charge * $data->discount_value / 100
                        : $data->discount_value;
                    $service_charge = $service_charge - $discount_amount;
                }
                if ($data->is_inclusive_tax == 1) {
                    $service_inclusive_tax = $data->inclusive_tax ?? null;
                    $inclusive_tax = $this->calculate_inclusive_tax_frontend($service_charge, $service_inclusive_tax);
                    $inclusive_tax_amount = $inclusive_tax['total_inclusive_tax'];
                    $service_charge = $service_charge + $inclusive_tax_amount;
                    $total_inclusivetax = collect($inclusive_tax['taxes'])->sum('amount');
                }
            }
        }

        $taxData = $this->calculateTaxAmounts(null, $service_charge);

        $couponPercentage = 0;
        $couponAmount = 0;
        $subtotal = $service_charge;
        $totalTax = collect($taxData)->sum('amount');
        $tax = $totalTax; // Example tax value
        $total = $subtotal + $totalTax;  // Total price includes tax

        $advancePayableAmount = ($total * $data->advance_payment_amount) / 100;
        $currency = Currency::where('is_primary', 1)->first();
        $currencySymbol = $currency ? $currency->currency_symbol : '$';
        // Return the response as JSON
        return response()->json([
            'price' => $doctorService ? $doctorService->charges : 0,
            'discountPercentage' => $data->discount_type,
            'discountvalue' => $data->discount_value,
            'discountAmount' => $discount_amount,
            'couponPercentage' => $couponPercentage,
            'couponAmount' => $couponAmount,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
            'taxData' => $taxData,
            'advancePayableAmount' => $advancePayableAmount ?? 0,
            'advancePayableAmountPercentage' => $data ? $data->advance_payment_amount : 0,
            'is_enable_advance_payment' => $data->is_enable_advance_payment,
            'currency' => $currencySymbol,
            'total_inclusive_tax' => $total_inclusivetax ?? 0,
            'service_inclusive_tax' => $service_inclusive_tax ?? 0,
            'inclusive_tax_data' => $inclusive_tax ?? 0,
            'is_inclusive_tax' =>  $data->is_inclusive_tax ?? 0,
        ]);
    }


    public function calculateTaxAmounts($taxData, $totalAmount)
    {
        $result = [];
        if ($taxData != null) {
            $taxes = json_decode($taxData);
        } else {
            $taxes = Tax::active()->whereNull('module_type')
                ->orWhere('module_type', 'services')
                ->where('tax_type', 'exclusive')
                ->where('status', 1)
                ->get();
        }
        foreach ($taxes as $tax) {
            $amount = 0;
            if ($tax->type == 'percent') {
                $amount = ($tax->value / 100) * $totalAmount;
            } else {
                $amount = $tax->value ?? 0;
            }
            $result[] = [
                'title' => $tax->title ?? 'Unknown Tax',
                'type' => $tax->type,
                'value' => $tax->value,
                'amount' => (float) number_format($amount, 2, '.', ''),
                'tax_scope' => $tax->tax_scope ?? '',
            ];
        }
        return $result;
    }

    public function calculate_inclusive_tax_frontend($service_amount, $inclusive_tax)
    {

        $inclusive_tax_amount = 0;
        $result = [];
        if ($inclusive_tax) {
            $taxData = json_decode($inclusive_tax, true);

            foreach ($taxData as $tax) {


                if ($tax['type'] == 'percent') {
                    $tax_amount = $service_amount * $tax['value'] / 100;
                } elseif ($tax['type'] == 'fixed') {
                    $tax_amount = $tax['value'];
                }

                $inclusive_tax_amount += $tax_amount;
                $result[] = [
                    'title' => $tax['title'] ?? 'Unknown Tax',
                    'type' => $tax['type'],
                    'value' => $tax['value'],
                    'amount' => (float) number_format($tax_amount, 2, '.', ''),
                    'tax_scope' => $tax['tax_scope'] ?? '',
                ];
            }
        }

        return [
            'taxes' => $result,
            'total_inclusive_tax' => (float) number_format($inclusive_tax_amount, 2, '.', ''),
        ];
    }


    public function slot_time_list(Request $request)
    {

        $availableSlot = [];

        if ($request->filled(['appointment_date', 'clinic_id', 'doctor_id', 'service_id'])) {
            $doctor = Doctor::CheckMultivendor()->where('id', $request->doctor_id)->where('status', 1)->first();
            $doctor_id = $doctor->doctor_id;
            $timezone = new \DateTimeZone(setting('default_time_zone') ?? 'UTC');

            $time_slot_duration = 10;
            $timeslot = ClinicsService::where('id', $request->service_id)->value('time_slot');

            if ($timeslot) {
                $time_slot_duration = ($timeslot === 'clinic_slot') ?
                    (int) Clinics::where('id', $request->clinic_id)->value('time_slot') :
                    (int) $timeslot;
            }

            $currentDate = Carbon::today($timezone);
            $carbonDate = Carbon::parse($request->appointment_date, $timezone);

            $dayOfWeek = $carbonDate->locale('en')->dayName;
            $availableSlot = [];

            $doctorSession = DoctorSession::where('clinic_id', $request->clinic_id)->where('doctor_id', $doctor_id)->where('day', $dayOfWeek)->first();

            if ($doctorSession && !$doctorSession->is_holiday) {

                $startTime = Carbon::parse($doctorSession->start_time, $timezone);
                $endTime = Carbon::parse($doctorSession->end_time, $timezone);

                $breaks = $doctorSession->breaks;

                $timeSlots = [];

                $current = $startTime->copy();
                while ($current < $endTime) {

                    $inBreak = false;
                    foreach ($breaks as $break) {
                        $breakStartTime = Carbon::parse($break['start_break'], $timezone);
                        $breakEndTime = Carbon::parse($break['end_break'], $timezone);
                        if ($current >= $breakStartTime && $current < $breakEndTime) {
                            $inBreak = true;
                            break;
                        }
                    }

                    if (!$inBreak) {
                        $timeSlots[] = $current->format('H:i');
                    }

                    $current->addMinutes($time_slot_duration);
                }

                $availableSlot = $timeSlots;

                if ($carbonDate == $currentDate) {
                    $todaytimeSlots = [];
                    $currentDateTime = Carbon::now($timezone);
                    foreach ($timeSlots as $slot) {
                        $slotTime = Carbon::parse($slot, $timezone);

                        if ($slotTime->greaterThan(Carbon::parse($currentDateTime, $timezone))) {

                            $todaytimeSlots[] = $slotTime->format('H:i');
                        }
                    }
                    $availableSlot = $todaytimeSlots;
                }

                $clinic_holiday = Holiday::where('clinic_id', $request->clinic_id)
                    ->where('date', $request->appointment_date)
                    ->first();

                if ($clinic_holiday) {
                    if ($clinic_holiday) {
                        $holidayStartTime = Carbon::parse($clinic_holiday->start_time, $timezone);
                        $holidayEndTime = Carbon::parse($clinic_holiday->end_time, $timezone);

                        $availableSlot = array_filter($availableSlot, function ($slot) use ($holidayStartTime, $holidayEndTime, $timezone) {
                            $slotTime = Carbon::parse($slot, $timezone);
                            return !($slotTime->between($holidayStartTime, $holidayEndTime));
                        });

                        $availableSlot = array_values($availableSlot);
                    }
                }
                $doctor_holiday = DoctorHoliday::where('doctor_id', $doctor_id)
                    ->where('date', $request->appointment_date)
                    ->first();

                if ($doctor_holiday) {
                    $holidayStartTime = Carbon::parse($doctor_holiday->start_time, $timezone);
                    $holidayEndTime = Carbon::parse($doctor_holiday->end_time, $timezone);

                    $availableSlot = array_filter($availableSlot, function ($slot) use ($holidayStartTime, $holidayEndTime, $timezone) {
                        $slotTime = Carbon::parse($slot, $timezone);
                        return !($slotTime->between($holidayStartTime, $holidayEndTime));
                    });

                    $availableSlot = array_values($availableSlot);
                }


                $appointmentData = Appointment::where('appointment_date', $request->appointment_date)->where('doctor_id', $doctor_id)->where('status', '!=', 'cancelled')->get();


                $bookedSlots = [];

                foreach ($appointmentData as $appointment) {

                    $startTime = Carbon::parse($appointment->start_date_time);
                    $startTime = strtotime($startTime);
                    $duration = $appointment->duration;

                    $endTime = $startTime + ($duration * 60);

                    $startTime = $startTime - ($duration * 60);

                    while ($startTime < $endTime) {
                        $bookedSlots[] = date('H:i', $startTime);
                        $startTime += 300;
                    }
                }
                $availableSlotTime = array_diff($availableSlot, $bookedSlots);
                $availableSlot = array_values($availableSlotTime);
            }
        }

        $message = 'messages.avaibleslot';

        // $data = [
        //     'availableSlot' => $availableSlot
        // ];

        // return response()->json([
        //     'status' => true,
        //     'data' => $availableSlot // Array of available slots
        // ]);


        $data = [
            'availableSlot' => collect($availableSlot)->map(function ($slot) {
                return Carbon::parse($slot)->format(setting('time_format') ?? 'h:i A');
            })
        ];

        return response()->json([
            'status' => true,
            'data' => $data['availableSlot']
        ]);
    }

    public function saveAppointment(Request $request)
    {


        $doctor = Doctor::CheckMultivendor()->where('id', $request->selectedDoctor)->where('status', 1)->first();
        $request['doctor_id'] = $doctor->doctor_id;
        $data = $request->all();
        $currency = Currency::where('is_primary', 1)->first();
        $currencySymbol = $currency ? $currency->currency_symbol : '$';

        $data['currency_symbol'] = $currencySymbol;
        if ($request->has('otherpatient_id') && $request->otherpatient_id != 'null') {

            $data['otherpatient_id'] = (int) $request['otherpatient_id'];
        } else {
            $data['otherpatient_id'] = null;
        }
        $serviceData = $this->getServiceAmount($data['service_id'], $data['doctor_id'], $data['clinic_id']);
        $request['selectedServiceName'] = $request['selectedServiceName'] ?? $serviceData['service_name'];
        $data['doctor_name'] = optional($doctor->user)->full_name;
        $data['user_id'] = auth()->user()->id;
        $data['service_name'] = $request['selectedServiceName'];
        $startDatetime = $data['appointment_date'] . ' ' . $data['appointment_time'];
        $data['appointment_time'] = Carbon::parse($data['appointment_time'])->format('H:i:s');
        $data['start_date_time'] = Carbon::parse($startDatetime)->format('Y-m-d H:i:s');

        $data['service_price'] = $serviceData['service_price'];
        $data['service_amount'] = $serviceData['service_amount'];
        $data['total_amount'] = $serviceData['total_amount'];
        $data['duration'] = $serviceData['duration'];
        $data['status'] = $data['status'] ? $data['status'] : 'confirmed';
        $data['advance_payment_status'] = $request->input('advance_payment_status');
        $service = ClinicsService::where('id', $data['service_id'])->first();
        $data['clinic_name'] = $service->ClinicServiceMapping->first()->center->name;
        $data['is_enable_advance_payment'] = $service->is_enable_advance_payment;
        $data['formate_appointment_date'] = DateFormate($data['appointment_date']);


        if ($service->is_enable_advance_payment == 1) {
            $advance_payable_amount = round(($data['total_amount'] * $service->advance_payment_amount) / 100, 2);
            // Calculate advance payment and round to 2 decimal places
            $data['advance_payment_amount'] = $service->advance_payment_amount;
            $data['advance_paid_amount'] = $advance_payable_amount;
            $data['remaining_payment_amount'] = $data['total_amount'] - $advance_payable_amount;
            $data['payble_amount'] = $advance_payable_amount;
            $data['advance_payment_status'] = 1;
            $data['payment_status'] = 0;
        } else {
            $data['advance_payment_amount'] = 0;
            $data['advance_paid_amount'] = 0;
            $data['remaining_payment_amount'] = 0;
            $data['advance_payment_status'] = 0;
            $data['payment_status'] = 1;
            $data['payble_amount'] = $data['total_amount'];
        }
        // Apply offer/coupon discount if Offer module is active
        $offerResult = $this->applyOfferDiscount(
            (float) $data['service_amount'],
            (int) $data['user_id'],
            (int) $data['service_id'],
            null,
            (int) $data['clinic_id'],
            $request->input('coupon_code'),
            'web'
        );

        if ($offerResult['offer_discount'] > 0) {
            $data['offer_id'] = $offerResult['offer_id'];
            $data['offer_discount'] = $offerResult['offer_discount'];
            $data['offer_code'] = $offerResult['offer_code'];
            $data['service_amount'] = $offerResult['final_amount'];
            $data['total_amount'] = $offerResult['final_amount'] + ($data['total_amount'] - $serviceData['service_amount']);
        }

        $paymentData = $data;
        $data = Appointment::create($data);

        // Record offer usage
        if (! empty($offerResult['offer_id']) && class_exists(\Modules\Offer\Services\OfferEligibilityService::class)) {
            try {
                app(\Modules\Offer\Services\OfferEligibilityService::class)->recordUsage(
                    $offerResult['offer_id'],
                    $data->user_id,
                    $data->id,
                    'appointment',
                    $data->service_id,
                    $offerResult['offer_discount']
                );
            } catch (\Throwable $e) {
                \Log::warning('Offer usage recording failed: ' . $e->getMessage());
            }
        }

        $is_telemet = ClinicsService::where('id', $data['service_id'])->pluck('is_video_consultancy')->first();
        if ($is_telemet == 1) {
            $setting = Setting::where('name', 'google_meet_method')->orwhere('name', 'is_zoom')->first();
            if ($data && $setting) {
                if ($setting->name == 'google_meet_method' && $setting->val == 1) {
                    $meetLink = $this->generateMeetLink($request, $data['start_date_time'], $data['duration'], $data);
                } else {
                    $zoom_url = getzoomVideoUrl($data);
                    if (!empty($zoom_url) && isset($zoom_url['start_url']) && isset($zoom_url['join_url'])) {
                        $startUrl = $zoom_url['start_url'];
                        $joinUrl = $zoom_url['join_url'];

                        $data->start_video_link = $startUrl;
                        $data->join_video_link = $joinUrl;
                        $data->save();
                    }
                }
            }
        }

        $tax = $data['tax_percentage'] ?? Tax::active()->whereNull('module_type')->orWhere('module_type', 'services')->where('tax_type', 'exclusive')->where('status', 1)->get();
        $transactionData = [
            'appointment_id' => $data->id,
            'transaction_type' => $data['transaction_type'] ?? 'cash',
            'total_amount' => $serviceData['total_amount'],
            'payment_status' => $data['payment_status'] ?? 0,
            'discount_value' => $serviceData['discount_value'] ?? 0,
            'discount_type' => $serviceData['discount_type'] ?? null,
            'discount_amount' => $serviceData['discount_amount'] ?? 0,
            'external_transaction_id' => $data['external_transaction_id'] ?? null,
            'tax_percentage' => json_encode($tax),
            'inclusive_tax' => $serviceData['service_inclusive_tax'],
            'inclusive_tax_price' => $serviceData['total_inclusive_tax']
        ];

        $payment = AppointmentTransaction::updateOrCreate(
            ['appointment_id' => $data->id],
            $transactionData
        );

        $paymentData['id'] = $data->id;
        // $paymentData = $data;
        $paymentData['tax_percentage'] = $this->calculateTaxAmounts(null, $data['total_amount']);
        $paymentData['included_tax'] = [];
        $paymentData['type'] = 'appointment';
        // if (in_array($data['transaction_type'], ['Wallet', 'Stripe', 'Paystack', 'PayPal', 'Flutterwave'])) {
        //     $paymentData['payment_status'] = 1;
        // } else {
        //     $paymentData['payment_status'] = 0;
        // }
        if ($request->hasFile('file_url')) {
            storeMediaFile($data, $request->file('file_url'));
        }

        // $this->savePayment($paymentData);
        $paymentMethod = $request->input('transaction_type');
        $price = $paymentData['payble_amount'];

        $paymentHandlers = [
            'cash' => 'CashPayment',
            'Wallet' => 'WalletPayment',
            'Stripe' => 'StripePayment',
            'Razor Pay' => 'RazorpayPayment',
            'Paystack' => 'PaystackPayment',
            'PayPal' => 'PayPalPayment',
            'Flutterwave' => 'FlutterwavePayment',
            'cinet' => 'CinetPayment',
            'sadad' => 'SadadPayment',
            'airtel' => 'AirtelPayment',
            'phonepe' => 'PhonePePayment',
            'midtrans' => 'MidtransPayment',
        ];
        if (array_key_exists($paymentMethod, $paymentHandlers)) {

            return $this->{$paymentHandlers[$paymentMethod]}($request, $paymentData, $price);
        }


        $notification_data = [
            'id' => $data->id,
            'description' => $data->description,
            'appointment_duration' => $data->duration,
            'user_id' => $data->user_id,
            'user_name' => optional($data->user)->first_name ?? default_user_name(),
            'doctor_id' => $data->doctor_id,
            'doctor_name' => optional($data->doctor)->first_name,
            'appointment_date' => Carbon::parse($data->appointment_date)->format('Y-m-d'), // Format date
            'appointment_time' => Carbon::parse($data->appointment_time)->format('H:i'), // Format time
            'appointment_services_names' => optional($data->clinicservice)->name ?? '--',
            'appointment_services_image' => optional($data->clinicservice)->file_url,
            'appointment_date_and_time' => Carbon::parse($data->appointment_date . ' ' . $data->appointment_time)->format('Y-m-d H:i'),
            'latitude' => null,
            'longitude' => null,
        ];
        $this->sendNotificationOnBookingUpdate('new_appointment', $notification_data);

        $message = __('messages.create_form', ['form' => __('appointment.singular_title')]);
        return response()->json(['message' => $message, 'data' => $data, 'currency' => $currencySymbol, 'status' => true], 200);
    }

    public function payNow(Request $request)
    {

        $appointment = Appointment::with('clinicservice', 'appointmenttransaction', 'patientEncounter')->findOrFail($request->appointment_id);
        $currency = Currency::where('is_primary', 1)->first();
        $currencySymbol = $currency ? $currency->currency_symbol : '$';

        $totalAmount = $appointment->patientEncounter && $appointment->patientEncounter->billingrecord
            ? $appointment->patientEncounter->billingrecord->final_total_amount
            : $appointment->total_amount;

        $paymentData = [
            'id' => $appointment->id,
            'service_id' => $appointment->service_id,
            'clinic_id' => $appointment->clinic_id,
            'selectedDoctor' => optional($appointment->doctorData)->id,
            'appointment_date' => Carbon::parse($appointment->appointment_date)->format('Y-m-d'),
            'appointment_time' => Carbon::parse($appointment->appointment_time)->format('H:i'),
            'selectedDoctorName' => optional($appointment->doctor)->first_name . ' ' . optional($appointment->doctor)->last_name,
            'selectedServiceName' => optional($appointment->clinicservice)->name,
            'transaction_type' => $request->transaction_type,
            'user_id' => $appointment->user_id,
            'status' => $appointment->status,
            'total_amount' =>  $totalAmount,
            'advance_payment_status' => $appointment->advance_payment_amount !== 0 ? 1 : 0,
            'doctor_id' => $appointment->doctor_id,
            'currency_symbol' => $currencySymbol,
            'doctor_name' => optional($appointment->doctor)->first_name . ' ' . optional($appointment->doctor)->last_name,
            'service_name' => optional($appointment->clinicservice)->name,
            'start_date_time' => $appointment->start_date_time,
            'service_price' => $appointment->service_price,
            'service_amount' => $appointment->service_amount,
            'duration' => $appointment->duration,
            'clinic_name' => optional($appointment->cliniccenter)->name,
            'is_enable_advance_payment' => optional($appointment->clinicservice)->is_enable_advance_payment,
            'type' => 'appointment_detail',

        ];

        $paymentData['is_enable_advance_payment'] = optional($appointment->clinicservice)->is_enable_advance_payment;

        if ($paymentData['is_enable_advance_payment'] == 1) {
            $paymentData['advance_payment_amount'] = $appointment->advance_payment_amount;
            $paymentData['advance_paid_amount'] = $appointment->advance_paid_amount;
            $paymentData['remaining_payment_amount'] = $paymentData['total_amount'] - $paymentData['advance_paid_amount'];

            $paymentData['payble_amount'] = $paymentData['remaining_payment_amount'];
            $paymentData['advance_payment_status'] = 0;
            $paymentData['payment_status'] = 1;
        } else {
            $paymentData['advance_payment_amount'] = 0;
            $paymentData['advance_paid_amount'] = 0;
            $paymentData['remaining_payment_amount'] = 0;
            $paymentData['advance_payment_status'] = 0;
            $paymentData['payment_status'] = 1;
            $paymentData['payble_amount'] = $paymentData['total_amount'];
        }

        $paymentData['payble_amount'] = round($paymentData['payble_amount'], 2);

        $paymentData['tax_percentage'] = $this->calculateTaxAmounts(null, $paymentData['payble_amount']);
        $paymentData['included_tax'] = [];

        $paymentData['description'] = "Payment for {$paymentData['service_name']} with Dr. " . optional($appointment->doctor)->first_name . ' ' . optional($appointment->doctor)->last_name;
        $paymentData['product_name'] = $paymentData['service_name'];

        $paymentHandlers = [
            'cash' => 'CashPayment',
            'Wallet' => 'WalletPayment',
            'Stripe' => 'StripePayment',
            'Razor' => 'RazorpayPayment',
            'Paystack' => 'PaystackPayment',
            'PayPal' => 'PayPalPayment',
            'Flutterwave' => 'FlutterwavePayment',
            'cinet' => 'CinetPayment',
            'sadad' => 'SadadPayment',
            'airtel' => 'AirtelPayment',
            'phonepe' => 'PhonePePayment',
            'midtrans' => 'MidtransPayment',
        ];

        if (!array_key_exists($request->transaction_type, $paymentHandlers)) {
            return response()->json(['message' => 'Invalid payment method', 'status' => false], 400);
        }

        $paymentMethod = $request->transaction_type;
        // return $this->{$handlerMethod}($request, $paymentData, $paymentData['payble_amount']);

        if (array_key_exists($paymentMethod, $paymentHandlers)) {

            return $this->{$paymentHandlers[$paymentMethod]}($request, $paymentData, $paymentData['payble_amount']);
        }

        $message = 'Payment Successfull';
        return response()->json(['message' => $message, 'data' => $paymentData, 'currency' => $currencySymbol, 'status' => true], 200);
    }

    //cash payment
    public function CashPayment(Request $request, $paymentData, $price)
    {
        $paymentData['transaction_type'] = 'cash';
        $paymentData['payment_status'] = 0;
        $paymentData['external_transaction_id'] = null;
        $paymentData['tax_percentage'] = $this->calculateTaxAmounts(null, $price);
        $this->savePayment($paymentData);
        $message = __('messages.save_appointment');
        return response()->json(['message' => $message, 'data' => $paymentData, 'status' => true], 200);
        // return $this->handlePaymentSuccess($paymentData);
    }

    //wallet payment
    public function WalletPayment(Request $request, $paymentData, $price)
    {
        $paymentData['transaction_type'] = 'Wallet';
        $paymentData['external_transaction_id'] = null;
        $paymentData['tax_percentage'] = $this->calculateTaxAmounts(null, $price);
        $this->savePayment($paymentData);
        $message = __('messages.save_appointment');
        return response()->json(['message' => $message, 'data' => $paymentData, 'status' => true], 200);
    }

    //stripe payment

    protected function StripePayment(Request $request, $paymentData, $price)
    {

        $baseURL = url('/');

        $stripe_secret_key = GetpaymentMethod('stripe_secretkey');

        $currency = GetcurrentCurrency();

        // Initialize the Stripe client
        $stripe = new StripeClient($stripe_secret_key);
        // $service_name = $request->input('selectedServiceName');
        $service_name = $paymentData['selectedServiceName'];

        $price = number_format($price, 2, '.', '');

        $priceInCents = $price * 100;
        // Create the checkout session
        $checkout_session = $stripe->checkout->sessions->create([
            'payment_method_types' => ['card'],
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => $currency,
                        'product_data' => [
                            'name' => $service_name, // Replace with dynamic data if needed
                        ],
                        'unit_amount' => $priceInCents,
                    ],
                    'quantity' => 1,
                ],
            ],
            'mode' => 'payment',
            'metadata' => [
                'service_id' => $paymentData['service_id'],
                'clinic_id' => $paymentData['clinic_id'],
                'appointment_id' => $paymentData['id'],
                'selectedDoctor' => $paymentData['selectedDoctor'],
                'doctor_id' => $paymentData['doctor_id'],
                'appointment_date' => $paymentData['appointment_date'],
                'appointment_time' => $paymentData['appointment_time'],
                'selectedDoctorName' => $paymentData['selectedDoctorName'],
                'user_id' => $paymentData['user_id'],
                'advance_payment_amount' => $paymentData['advance_payment_amount'],
                'advance_paid_amount' => $paymentData['advance_paid_amount'],
                'remaining_payment_amount' => $paymentData['remaining_payment_amount'],
                'advance_payment_status' => $paymentData['advance_payment_status'],
                'type' => $paymentData['type']
            ],
            'success_url' => $baseURL . '/payment/success?gateway=stripe&session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $baseURL . '/payment/cancel',
        ]);

        // Return the Stripe session URL for redirection
        return response()->json(['payment_method' => 'stripe', 'redirect' => $checkout_session->url]);
    }
    //paypal payment
    protected function PayPalPayment(Request $request, $paymentData, $price)
    {
        $baseURL = url('/');


        // Validate price
        if (!is_numeric($price) || $price <= 0) {
            return redirect()->back()->withErrors('Invalid price value.');
        }

        // try {
        // Get Access Token
        $accessToken = $this->getAccessToken();

        // Create Payment
        $payment = $this->createPayment($accessToken, $price, $paymentData);

        if (isset($payment['links'])) {
            foreach ($payment['links'] as $link) {
                if ($link['rel'] === 'approval_url') {
                    return response()->json(['success' => true, 'redirect' => $link['href']]);
                }
            }
        }

        // return redirect()->back()->withErrors('Payment creation failed.');
        // } catch (\Exception $ex) {
        //     return redirect()->back()->withErrors('Payment processing failed: ' . $ex->getMessage());
        // }
    }

    private function getAccessToken()
    {
        $clientId =  GetpaymentMethod('paypal_clientid');
        $clientSecret = GetpaymentMethod('paypal_secretkey');

        $client = new Client();
        $response = $client->post('https://api.sandbox.paypal.com/v1/oauth2/token', [
            'auth' => [$clientId, $clientSecret],
            'form_params' => [
                'grant_type' => 'client_credentials',
            ],
        ]);

        $data = json_decode($response->getBody(), true);
        return $data['access_token'];
    }

    private function createPayment($accessToken, $price, $paymentData)
    {
        $baseURL = url('/');

        $currency = GetcurrentCurrency();
        $formattedCurrency = strtoupper(strtolower($currency));

        $client = new Client();

        // Building the payment request
        $response = $client->post('https://api.sandbox.paypal.com/v1/payments/payment', [
            'headers' => [
                'Authorization' => "Bearer $accessToken",
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'intent' => 'sale',
                'payer' => [
                    'payment_method' => 'paypal',
                ],
                'transactions' => [
                    [
                        'amount' => [
                            'total' => $price,
                            'currency' => $formattedCurrency,
                        ],
                        'description' => 'Payment for service ID: ' . $paymentData['service_id'],
                        'item_list' => [
                            'items' => [
                                [
                                    'name' => $paymentData['selectedServiceName'] ?? '',
                                    'sku' => $paymentData['service_id'],
                                    'price' => $price,
                                    'currency' =>  $formattedCurrency,
                                    'quantity' => 1, // Ensure this is a valid number
                                ],
                            ],
                        ],
                        'custom' => json_encode([ // Use the custom field to pass extra metadata
                            'clinic_id' => $paymentData['clinic_id'] ?? null,
                            'doctor_id' => $paymentData['doctor_id'] ?? null,
                            'appointment_date' => $paymentData['appointment_date'] ?? null,
                            'appointment_time' => $paymentData['appointment_time'] ?? null,
                            'appointment_id' => $paymentData['id'] ?? null,
                            'payment_status' => $paymentData['payment_status'],
                            'advance_payment_amount' => $paymentData['advance_payment_amount'],
                            'advance_paid_amount' => $paymentData['advance_paid_amount'],
                            'remaining_payment_amount' => $paymentData['remaining_payment_amount'],
                            'advance_payment_status' => $paymentData['advance_payment_status'],
                        ]),
                    ],
                ],
                'redirect_urls' => [
                    'return_url' => $baseURL . '/payment/success?gateway=paypal',
                    'cancel_url' => $baseURL . '/payment/cancel',
                ],
            ],
        ]);

        return json_decode($response->getBody(), true);
    }


    //paystack payment
    protected function PaystackPayment(Request $request, $paymentData, $price)
    {
        $baseURL = url('/');

        $paystackSecretKey = GetpaymentMethod('paystack_secretkey');

        $currency = GetcurrentCurrency();
        $formattedCurrency = strtoupper(strtolower($currency));

        $priceInKobo = $price * 100; // Paystack uses kobo

        // Additional custom data to be passed in the metadata
        $customMetadata = [
            'tax_percentage' => $request->input('tax_percentage', []),  // If tax percentage exists, add it
            'clinic_id' => $request->input('clinic_id', null),
            'doctor_id' => $request->input('doctor_id', null),
            'appointment_date' => $request->input('appointment_date', null),
            'appointment_time' => $request->input('appointment_time', null),
            'appointment_id' => $paymentData['id'],
            'payment_status' => $paymentData['payment_status'],
            'remaining_payment_amount' => $paymentData['remaining_payment_amount'],
            'advance_payment_status' => $paymentData['advance_payment_status'],
            'advance_payment_amount' => $paymentData['advance_payment_amount'],
            'advance_paid_amount' => $paymentData['advance_paid_amount'],
            'type' => $paymentData['type']

        ];

        // Create a new Paystack payment
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $paystackSecretKey,
        ])->post('https://api.paystack.co/transaction/initialize', [
            'email' => auth()->user()->email, // Get user email from authenticated user
            'amount' => $priceInKobo,
            'currency' => $formattedCurrency,
            'callback_url' => $baseURL . '/payment/success?gateway=paystack',
            'metadata' => array_merge([
                'plan_id' => $request->input('service_id'),  // Add the service ID to the metadata
            ], $customMetadata),  // Merge custom metadata with the original plan_id
        ]);

        $responseBody = $response->json();

        if ($responseBody['status']) {
            return response()->json([
                'success' => true,
                'redirect' => $responseBody['data']['authorization_url'],
            ]);
        } else {
            return response()->json(['error' => 'Something went wrong. Choose a different method.'], 400);
        }
    }


    //flutter wave
    protected function FlutterwavePayment(Request $request, $paymentData, $price)
    {
        $baseURL = url('/');

        $flutterwaveKey = GetpaymentMethod('flutterwave_secretkey');

        $service_id = $request->input('service_id');
        $clinic_id = $request->input('clinic_id');
        $doctor_id = $request->input('doctor_id');
        $appointment_date = $request->input('appointment_date');
        $appointment_time = $request->input('appointment_time');
        $service_id = $request->input('service_id');  // Get service_id from the request
        $appointment_id = $request->input('appointment_id');  // Get appointment_id from the request
        $priceInKobo = $price;

        $currency = GetcurrentCurrency();
        $formattedCurrency = strtoupper(strtolower($currency));


        // Building metadata to include the necessary details
        $metadata = [
            'clinic_id' => $clinic_id,
            'doctor_id' => $doctor_id,
            'appointment_date' => $appointment_date,
            'appointment_time' => $appointment_time,
            'service_id' => $service_id,  // Include service_id
            'appointment_id' => $paymentData['id'],  // Include appointment_id
            'payment_status' => $paymentData['payment_status'],
            'remaining_payment_amount' => $paymentData['remaining_payment_amount'],
            'advance_payment_status' => $paymentData['advance_payment_status'],
            'advance_payment_amount' => $paymentData['advance_payment_amount'],
            'advance_paid_amount' => $paymentData['advance_paid_amount'],
            'type' => $paymentData['type']
        ];

        // Preparing request data

        $data = [
            'tx_ref' => 'txn_' . time(),
            'amount' => $priceInKobo,
            "currency" => $formattedCurrency,
            'customer_email' => 'test@example.com',
            "payment_type" => "mobilemoneyghana",
            'redirect_url' => $baseURL . '/payment/success?gateway=flutterwave',
            'metadata' => $metadata,  // Attach metadata with all relevant information
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $flutterwaveKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.flutterwave.com/v3/payments', $data);

        $responseBody = $response->json();

        // Handle the response from Flutterwave
        if ($response->successful() && isset($responseBody['status'])) {
            if ($responseBody['status'] === 'success') {
                return response()->json([
                    'success' => true,
                    'redirect' => $responseBody['data']['link'],
                ]);
            } else {
                return response()->json([
                    'error' => 'Payment initialization failed: ' . ($responseBody['message'] ?? 'Unknown error')
                ], 400);
            }
        } else {
            return response()->json([
                'error' => 'Failed to communicate with Flutterwave: ' . ($responseBody['message'] ?? 'Unknown error')
            ], 500);
        }
    }

    //rozar pay

    protected function RazorpayPayment(Request $request, $paymentData, $price)
    {
        $baseURL = url('/');

        $razorpayKey = GetpaymentMethod('razorpay_publickey');
        $razorpaySecret = GetpaymentMethod('razorpay_secretkey');

        $currency = GetcurrentCurrency();
        $formattedCurrency = strtoupper(strtolower($currency));

        $service_id = $request->input('service_id');
        $priceInPaise = $price * 100; // Razorpay expects price in paise (1 INR = 100 paise)
        // Additional custom data for metadata
        $customMetadata = [
            'tax_percentage' => $paymentData['tax_percentage'],
            'clinic_id' => $paymentData['clinic_id'],
            'doctor_id' => $paymentData['doctor_id'],
            'appointment_date' => $paymentData['appointment_date'],
            'appointment_time' => $paymentData['appointment_time'],
            'appointment_id' => $paymentData['id'],
            'payment_status' => $paymentData['payment_status'],
            'remaining_payment_amount' => $paymentData['remaining_payment_amount'],
            'advance_payment_status' => $paymentData['advance_payment_status'],
            'advance_payment_amount' => $paymentData['advance_payment_amount'],
            'advance_paid_amount' => $paymentData['advance_paid_amount'],
            'type' => $paymentData['type']
        ];
        try {
            $api = new \Razorpay\Api\Api($razorpayKey, $razorpaySecret);
            $orderData = [
                'receipt' => 'rcptid_' . time(),
                'amount' => $priceInPaise,
                'currency' => $formattedCurrency,
                'payment_capture' => 1,
                'notes' => array_merge([
                    'service_id' => $service_id,
                ], $customMetadata),
            ];

            $razorpayOrder = $api->order->create($orderData);
            // Store payment data in session for the payment page
            session([
                'razorpay_payment_data' => [
                    'order_id' => $razorpayOrder['id'],
                    'amount' => $priceInPaise,
                    'service_id' => $service_id,
                    'key' => $razorpayKey,
                    'currency' => $formattedCurrency,
                    'name' => $paymentData['service_name'] ?? '',
                    'description' => 'Payment for service',
                ]
            ]);
            return response()->json([
                'redirect' => route('razorpay.payment.page', ['order_id' => $razorpayOrder['id']])
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }


    // cinwt payment
    protected function CinetPayment(Request $request, $paymentData, $price)
    {
        $baseURL = url('/');

        $cinetApiKey = GetpaymentMethod('cinet_Secret_key');

        $plan_id = $request->input('plan_id');
        $priceInCents = $price * 100;
        $service_id = $request->input('service_id');

        $currency = GetcurrentCurrency();
        $formattedCurrency = strtoupper(strtolower($currency));

        // Additional metadata
        $customMetadata = [
            'tax_percentage' => $request->input('tax_percentage', []),
            'advance_payment_amount' => $request->input('advance_payment_amount', 0),
            'clinic_id' => $request->input('clinic_id', null),
            'doctor_id' => $request->input('doctor_id', null),
            'appointment_date' => $request->input('appointment_date', null),
            'appointment_time' => $request->input('appointment_time', null),
            'appointment_id' => $request->input('appointment_id', null),
            'type' => $paymentData['type']
        ];

        $data = [
            'amount' => $priceInCents,
            'currency' => $formattedCurrency,
            'service_id' => $service_id,
            'callback_url' => $baseURL . '/payment/success?gateway=cinet',
            'user_email' => auth()->user()->email,
            'metadata' => $customMetadata, // Pass metadata
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $cinetApiKey,
            ])->post('https://api.cinet.com/payment', $data);

            $responseBody = $response->json();

            if ($response->successful() && isset($responseBody['payment_url'])) {
                return redirect($responseBody['payment_url']);
            } else {
                return redirect()->back()->withErrors('Payment initialization failed: ' . ($responseBody['message'] ?? 'Unknown error'));
            }
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Payment initialization failed: ' . $e->getMessage());
        }
    }

    //sadad payment
    protected function SadadPayment(Request $request, $paymentData, $price)
    {
        $baseURL = url('/');

        $price = $request->input('price');
        $service_id = $request->input('service_id');

        // Additional metadata
        $customMetadata = [
            'tax_percentage' => $request->input('tax_percentage', []),
            'advance_payment_amount' => $request->input('advance_payment_amount', 0),
            'clinic_id' => $request->input('clinic_id', null),
            'doctor_id' => $request->input('doctor_id', null),
            'appointment_date' => $request->input('appointment_date', null),
            'appointment_time' => $request->input('appointment_time', null),
            'appointment_id' => $request->input('appointment_id', null),
            'type' => $paymentData['type']
        ];

        try {
            $response = $this->makeSadadPaymentRequest($price, $service_id, $customMetadata);

            if ($response->status === 'success' && isset($response->redirect_url)) {
                return redirect($response->redirect_url);
            } else {
                return redirect()->back()->withErrors('Payment initiation failed: ' . ($response->message ?? 'Unknown error'));
            }
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Payment initiation failed: ' . $e->getMessage());
        }
    }


    protected function makeSadadPaymentRequest($price, $service_id, $customMetadata)
    {

        $sadad_Sadadkey = GetpaymentMethod('sadad_Sadadkey');

        $currency = GetcurrentCurrency();
        $formattedCurrency = strtoupper(strtolower($currency));

        $baseURL = url('/');


        $url = 'https://api.sadad.com/payment';
        $data = [
            'amount' => $price,
            'currency' => $formattedCurrency, // Assuming Sadad uses SAR
            'service_id' => $service_id,
            'callback_url' => $baseURL . '/payment/success?gateway=sadad',
            'metadata' => $customMetadata, // Pass custom metadata
        ];

        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->post($url, [
                'json' => $data,
                'headers' => [
                    'Authorization' => 'Bearer ' .  $sadad_Sadadkey,
                    'Accept' => 'application/json',
                ],
            ]);

            return json_decode($response->getBody());
        } catch (\Exception $e) {
            throw new \Exception('Sadad API request failed: ' . $e->getMessage());
        }
    }

    //airtel payment

    protected function AirtelPayment(Request $request, $paymentData, $price)
    {
        $baseURL = url('/');

        $price = $request->input('price');
        $service_id = $request->input('service_id');

        // Additional metadata
        $customMetadata = [
            'tax_percentage' => $request->input('tax_percentage', []),
            'advance_payment_amount' => $request->input('advance_payment_amount', 0),
            'clinic_id' => $request->input('clinic_id', null),
            'doctor_id' => $request->input('doctor_id', null),
            'appointment_date' => $request->input('appointment_date', null),
            'appointment_time' => $request->input('appointment_time', null),
            'appointment_id' => $request->input('appointment_id', null),
            'type' => $paymentData['type']
        ];

        try {
            $response = $this->makeAirtelPaymentRequest($price, $service_id, $customMetadata);

            if ($response->status === 'success' && isset($response->redirect_url)) {
                return redirect($response->redirect_url);
            } else {
                return redirect()->back()->withErrors('Payment initiation failed: ' . ($response->message ?? 'Unknown error'));
            }
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Payment initiation failed: ' . $e->getMessage());
        }
    }

    //make airtel payment request
    protected function makeAirtelPaymentRequest($price, $service_id, $customMetadata)
    {

        $airtel_money_secretkey = GetpaymentMethod('airtel_money_secretkey');

        $currency = GetcurrentCurrency();
        $formattedCurrency = strtoupper(strtolower($currency));
        $baseURL = url('/');



        $url = 'https://api.airtel.com/payment';
        $data = [
            'amount' => $price,
            'currency' =>  $formattedCurrency, // Assuming Airtel uses USD, change if needed
            'plan_id' => $service_id,
            'callback_url' =>  $baseURL . '/payment/success?gateway=airtel',
            'metadata' => $customMetadata, // Pass custom metadata
        ];

        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->post($url, [
                'json' => $data,
                'headers' => [
                    'Authorization' => 'Bearer ' .  $airtel_money_secretkey,
                    'Accept' => 'application/json',
                ],
            ]);

            return json_decode($response->getBody());
        } catch (\Exception $e) {
            throw new \Exception('Airtel API request failed: ' . $e->getMessage());
        }
    }

    //phonepe payment

    protected function PhonePePayment(Request $request, $paymentData, $price)
    {
        $price = $request->input('price');
        $service_id = $request->input('service_id');

        try {
            $response = $this->makePhonePePaymentRequest($price, $service_id);

            if ($response->status === 'SUCCESS' && isset($response->data->payment_url)) {
                return redirect($response->data->payment_url);
            } else {
                return redirect()->back()->withErrors('Payment initiation failed: ' . ($response->message ?? 'Unknown error'));
            }
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Payment initiation failed: ' . $e->getMessage());
        }
    }
    //make phonepe payment request
    protected function makePhonePePaymentRequest($price, $plan_id)
    {

        $currency = GetcurrentCurrency();

        $formattedCurrency = strtoupper(strtolower($currency));
        $baseURL = url('/');

        $url = 'https://api.phonepe.com/apis/hermes/pg/v1/pay';
        $data = [
            'amount' => $price * 100, // Convert to paisa
            'plan_id' => $plan_id,
            'callbackUrl' => $baseURL . '/payment/success?gateway=phonepe',
            'currency' =>  $formattedCurrency,
        ];

        try {
            $client = new \GuzzleHttp\Client();
            $payload = json_encode($data);

            // Generate X-VERIFY token
            $phonePeSecretKey = env('PHONEPE_SECRET_KEY');
            $verifyToken = hash('sha256', $payload . $phonePeSecretKey);

            $response = $client->post($url, [
                'body' => $payload,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-VERIFY' => $verifyToken,
                ],
            ]);

            return json_decode($response->getBody());
        } catch (\Exception $e) {
            throw new \Exception('PhonePe API request failed: ' . $e->getMessage());
        }
    }


    //midtrans payment
    protected function MidtransPayment(Request $request, $paymentData, $price)
    {
        // Set Midtrans configuration
        \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        \Midtrans\Config::$isProduction = env('MIDTRANS_IS_PRODUCTION');
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;

        $price = $request->input('price');
        $plan_id = $request->input('plan_id');
        $transactionId = uniqid();

        // Transaction details
        $transactionDetails = [
            'order_id' => $transactionId,
            'gross_amount' => $price, // Amount in IDR
        ];

        // Customer details
        $customerDetails = [
            'first_name' => auth()->user()->name,
            'email' => auth()->user()->email,
        ];

        // Item details (optional)
        $itemDetails = [
            [
                'id' => $plan_id,
                'price' => $price,
                'quantity' => 1,
                'name' => 'Subscription Plan', // Change as needed
            ],
        ];

        $transaction = [
            'transaction_details' => $transactionDetails,
            'customer_details' => $customerDetails,
            'item_details' => $itemDetails,
        ];

        try {
            $snapToken = \Midtrans\Snap::getSnapToken($transaction);
            return response()->json(['snapToken' => $snapToken]);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Payment initiation failed: ' . $e->getMessage());
        }
    }

    //midtrans payment notification
    public function midtransNotification(Request $request)
    {
        $payload = json_decode($request->getContent(), true);

        $transactionStatus = $payload['transaction_status'];
        $transactionId = $payload['order_id'];
        $planId = $payload['item_details'][0]['id'];
        $amount = $payload['gross_amount'];

        // Handle the transaction status
        if (in_array($transactionStatus, ['capture', 'settlement'])) {
            return $this->handlePaymentSuccess($planId, $amount, 'midtrans', $transactionId);
        } elseif (in_array($transactionStatus, ['pending'])) {
            // Handle pending payment logic if required
            return response()->json(['status' => 'pending']);
        } elseif (in_array($transactionStatus, ['deny', 'cancel', 'expire'])) {
            // Handle failed or canceled payment logic
            return response()->json(['status' => 'failed', 'message' => 'Transaction failed.']);
        }

        return response()->json(['status' => 'unknown', 'message' => 'Unknown transaction status.']);
    }

    //save payment
    public function savePayment($data)
    {

        $data['tip_amount'] = $data['tip'] ?? 0;
        $appointment = Appointment::findOrFail($data['id']);
        $serviceDetails = ClinicsService::where('id', $appointment->service_id)->with('vendor')->first();
        $vendor = $serviceDetails->vendor ?? null;
        $serviceData = $this->getServiceAmount($appointment->service_id, $appointment->doctor_id, $appointment->clinic_id);
        $tax = $data['tax_percentage'] ?? Tax::active()->whereNull('module_type')->orWhere('module_type', 'services')->where('tax_type', 'exclusive')->where('status', 1)->get();

        $transactionData = [
            'appointment_id' => $appointment->id,
            'transaction_type' => $data['transaction_type'] ?? 'cash',
            'total_amount' => $serviceData['total_amount'],
            'payment_status' => $data['payment_status'] ?? 0,
            'discount_value' => $serviceData['discount_value'] ?? 0,
            'discount_type' => $serviceData['discount_type'] ?? null,
            'discount_amount' => $serviceData['discount_amount'] ?? 0,
            'external_transaction_id' => $data['external_transaction_id'] ?? null,
            'tax_percentage' => json_encode($tax),
        ];


        if ($data['transaction_type'] == 'Wallet') {
            $wallet = Wallet::where('user_id', $appointment->user_id)->first();
            $paid_amount = 0;

            if ($wallet !== null) {
                $wallet_amount = $wallet->amount;
                if ($data['advance_payment_status'] == 1 && $data['payment_status'] == 0) {
                    if ($wallet_amount >= $data['advance_paid_amount']) {
                        if ($data['advance_payment_status'] == 1) {
                            $wallet->amount = $wallet->amount - $data['advance_paid_amount'];
                            $wallet->update();
                            $transactionData['total_amount'] = $data['advance_paid_amount'];
                            $paid_amount = $data['advance_paid_amount'];
                        }
                    } else {
                        $message = __('messages.wallet_balance_error');
                        return response()->json(['message' => $message], 400);
                    }
                } else if ($data['payment_status'] == 1 && $data['remaining_payment_amount'] > 0) {
                    if ($wallet_amount >= $data['remaining_payment_amount']) {
                        $wallet->amount = $wallet->amount - $data['remaining_payment_amount'];
                        $wallet->update();
                        $transactionData['total_amount'] = $data['remaining_payment_amount'];
                        $paid_amount = $data['remaining_payment_amount'];
                    } else {
                        $message = __('messages.wallet_balance_error');
                        return response()->json(['message' => $message], 400);
                    }
                } else if ($data['payment_status'] == 1) {
                    if ($data['payment_status'] == 1 && $wallet_amount >= $serviceData['total_amount']) {
                        $wallet->amount = $wallet->amount - $serviceData['total_amount'];
                        $wallet->update();

                        $transactionData['total_amount'] = $serviceData['total_amount'];
                        $paid_amount = $serviceData['total_amount'];
                    } else {
                        $message = __('messages.wallet_balance_error');
                        return response()->json(['message' => $message], 400);
                    }
                }


                $wallethistory = new WalletHistory;
                $wallethistory->user_id = $wallet->user_id;
                $wallethistory->datetime = Carbon::now();
                $wallethistory->activity_type = 'paid_for_appointment';
                $wallethistory->activity_message = trans('messages.paid_for_appointment', ['value' => $appointment->id]);
                // $wallethistory->activity_message = Str::replace(':value', $appointment->id, 'paid_for_appointment');
                $activity_data = [
                    'title' => $wallet->title,
                    'user_id' => $wallet->user_id,
                    'amount' => $wallet->amount,
                    'credit_debit_amount' => $paid_amount,
                    'transaction_type' => __('messages.debit'),
                    'appointment_id' => $appointment->id,
                ];

                $wallethistory->activity_data = json_encode($activity_data);
                $wallethistory->save();
            }
        }

        $payment = AppointmentTransaction::updateOrCreate(
            ['appointment_id' => $appointment->id],
            $transactionData
        );

        if (!empty($payment) && $data['advance_payment_status'] == 1) {
            $appointment->advance_paid_amount = $data['advance_paid_amount'];
            $appointment->save();

            $payment->advance_payment_status = $data['advance_payment_status'];
            $payment->total_amount = $data['advance_paid_amount'];
            $payment->save();
        }

        // Fetch the full appointment with all relations
        $appointment_notification = Appointment::with(['user', 'doctor', 'clinicservice'])->findOrFail($data['id']);

        // dd($appointment_notification);
        // Now you have all the details you need
        $notification_data = [
            'id' => $appointment_notification->id,
            'description' => $appointment_notification->description,
            'appointment_duration' => $appointment_notification->duration,
            'user_id' => $appointment_notification->user_id,
            'user_name' => optional($appointment_notification->user)->first_name ?? default_user_name(),
            'doctor_id' => $appointment_notification->doctor_id,
            'doctor_name' => optional($appointment_notification->doctor)->first_name,
            'appointment_date' => $appointment_notification->appointment_date,
            'appointment_time' => $appointment_notification->appointment_time,
            'appointment_services_names' => optional($appointment_notification->clinicservice)->name ?? '--',
            'appointment_services_image' => optional($appointment_notification->clinicservice)->file_url,
            'appointment_date_and_time' => $appointment_notification->start_date_time,
            'latitude' => null,
            'longitude' => null,
        ];

        $this->sendNotificationOnBookingUpdate('new_appointment', $notification_data);
        // dd($data,$appointment,$serviceDetails,$vendor,$serviceData,$tax,$transactionData,$payment,$appointment_notification,$notification_data);
        $message = __('appointment.save_appointment');
        return response()->json(['message' => $message, 'data' => $payment, 'status' => true], 200);
    }

    public function cancelAppointment(Request $request, $id)
    {
        $appointment = Appointment::with('appointmenttransaction')->findOrFail($id);

        if ($appointment->status === 'cancelled') {
            return response()->json([
                'status' => false,
                'message' => __('appointment.already_cancelled')
            ]);
        }

        DB::beginTransaction();

        $appointment->status = 'cancelled';
        $appointment->cancellation_charge = $request->cancellation_charge ?? null;
        $appointment->cancellation_type = $request->cancellation_type ?? null;
        $appointment->reason = $request->reason ?? null;
        $appointment->cancellation_charge_amount = $request->cancellation_charge_amount ?? 0;

        $cancellation_charge_amount = $appointment->cancellation_charge_amount;
        $cancellation_reason = $appointment->reason;

        $advance_paid_amount = $appointment->advance_paid_amount;
        $total_paid = $appointment->total_amount;

        $payment_status = optional($appointment->appointmenttransaction)->payment_status;
        $refund_amount = 0;

        // Get or create wallet
        $user_wallet = Wallet::firstOrCreate(
            ['user_id' => $appointment->user_id],
            ['amount' => 0]
        );

        // UNPAID (Advance paid only)
        if ($payment_status == 0) {
            if ($advance_paid_amount >= $cancellation_charge_amount) {
                $refund_amount = $advance_paid_amount - $cancellation_charge_amount;
            } else {
                $wallet_deduct = $cancellation_charge_amount - $advance_paid_amount;

                if ($wallet_deduct > 0 && $user_wallet->amount >= $wallet_deduct) {
                    $user_wallet->amount -= $wallet_deduct;
                    $user_wallet->update();

                    // Wallet History: Deduction
                    WalletHistory::create([
                        'user_id' => $user_wallet->user_id,
                        'datetime' => Carbon::now(),
                        'activity_type' => 'wallet_deduction',
                        'activity_message' => trans('messages.wallet_deduction', ['value' => $appointment->id]),
                        'activity_data' => json_encode([
                            'title' => $user_wallet->title,
                            'user_id' => $user_wallet->user_id,
                            'amount' => $user_wallet->amount,
                            'credit_debit_amount' => $wallet_deduct,
                            'transaction_type' => __('messages.debit'),
                            'appointment_id' => $appointment->id,
                            'cancellation_charge_amount' => $cancellation_charge_amount,
                            'cancellation_reason' => $cancellation_reason,
                        ])
                    ]);
                }
                $refund_amount = 0;
            }
        }
        // PAID
        else {
            $refund_amount = $total_paid - $cancellation_charge_amount;
        }
        // Refund if applicable
        if ($refund_amount > 0) {
            $user_wallet->amount += $refund_amount;
            $user_wallet->update();

            WalletHistory::create([
                'user_id' => $user_wallet->user_id,
                'datetime' => Carbon::now(),
                'activity_type' => 'wallet_refund',
                'activity_message' => trans('messages.wallet_refund', ['value' => $appointment->id]),
                'activity_data' => json_encode([
                    'title' => $user_wallet->title,
                    'user_id' => $user_wallet->user_id,
                    'amount' => $user_wallet->amount,
                    'credit_debit_amount' => $refund_amount,
                    'transaction_type' => __('messages.credit'),
                    'appointment_id' => $appointment->id,
                    'cancellation_charge_amount' => $cancellation_charge_amount,
                    'cancellation_reason' => $cancellation_reason,
                ])
            ]);
        }
        $notificationData = [
            'id' => $appointment->id,
            'activity_type' => 'wallet_refund',
            'payment_status' => 'Refunded',
            'wallet' => $user_wallet,
            'appointment_id' => $appointment->id,
            'refund_amount' => $refund_amount,
            'description' => $appointment->description,
            'user_id' => $appointment->user_id,
            'user_name' => optional($appointment->user)->first_name ?? default_user_name(),
            'doctor_id' => $appointment->doctor_id,
            'doctor_name' => optional($appointment->doctor)->first_name,
            'appointment_date' => Carbon::parse($appointment->appointment_date)->format('Y-m-d'), // Format date
            'appointment_time' => Carbon::parse($appointment->appointment_time)->format('H:i'), // Format time
            'appointment_services_names' => optional($appointment->clinicservice)->name ?? '--',
            'appointment_services_image' => optional($appointment->clinicservice)->file_url,
            'appointment_duration' => $appointment->duration,
            'updated_by_role' => auth()->user()->user_type ?? '',
        ];

        $this->sendNotificationOnBookingUpdate('cancel_appointment', $notificationData);
        $this->sendNotificationOnBookingUpdate('wallet_refund', $notificationData);

        $appointment->save();
        DB::commit();

        return response()->json([
            'status' => true,
            'message' => __('appointment.cancel_success')
        ]);
    }

    //payment success redirect
    public function paymentSuccess(Request $request)
    {
        $gateway = $request->input('gateway');

        switch ($gateway) {
            case 'stripe':
                return $this->handleStripeSuccess($request);
            case 'razorpay':
                return $this->handleRazorpaySuccess($request);
            case 'paystack':
                return $this->handlePaystackSuccess($request);
            case 'paypal':
                return $this->handlePayPalSuccess($request);
            case 'flutterwave':
                return $this->handleFlutterwaveSuccess($request);
            case 'cinet':
                return $this->handleCinetSuccess($request);
            case 'sadad':
                return $this->handleSadadSuccess($request);
            case 'airtel':
                return $this->handleAirtelSuccess($request);
            case 'phonepe':
                return $this->handlePhonePeSuccess($request);
            case 'midtrans':
                return $this->MidtransPayment($request);
            default:
                return redirect('/')->with('error', 'Invalid payment gateway.');
        }
    }

    //stripe payment success
    protected function handleStripeSuccess(Request $request)
    {
        $previousUrl = url()->previous();
        $stripe_secret_key = GetpaymentMethod('stripe_secretkey');
        $sessionId = $request->input('session_id');
        $stripe = new StripeClient($stripe_secret_key);
        $session = $stripe->checkout->sessions->retrieve($sessionId);
        // try {
        $session = $stripe->checkout->sessions->retrieve($sessionId);
        $metadata = $session->metadata;
        $paymentData = [
            'id' => $metadata->appointment_id ?? null,
            'transaction_type' => 'stripe',
            'payment_status' => ($session->payment_status == 'paid') ? 1 : 0,
            'external_transaction_id' => $sessionId,
            'tip' => $metadata->tip_amount ?? 0,
            'advance_payment_status' => $metadata->advance_payment_status ?? 0,
            'advance_payment_amount' => $metadata->advance_payment_amount ?? 0,
            'advance_paid_amount' => $metadata->advance_paid_amount ?? 0,
            'remaining_payment_amount' => $metadata->remaining_payment_amount ?? 0,
            'metadata' => $session->metadata,
            'paymentStatus' => $session->payment_status, // e.g., 'paid', 'unpaid'
            'amountTotal' => $session->amount_total / 100,
            'currency' => $session->currency,
            'clinic_id' => $metadata->clinic_id,
            'doctor_id' => $metadata->doctor_id,
            'appointment_date' => $metadata->appointment_date,
            'appointment_time' => $metadata->appointment_time, // Format: '17:30'
            'appointment_id' => $metadata->appointment_id,
            'service_id' => $metadata->service_id,
            'type' => $metadata->type,
        ];
        $this->savePayment($paymentData);
        return $this->handlePaymentSuccess($paymentData);
        // } catch (\Exception $e) {
        //     return redirect($previousUrl)->with('error', 'Payment failed: ' . $e->getMessage());
        // }
    }
    //paypal payment success
    protected function handlePayPalSuccess(Request $request)
    {
        $paymentId = $request->input('paymentId');
        $payerId = $request->input('PayerID');

        $paypal_secretkey = GetpaymentMethod('paypal_secretkey');
        $paypal_clientid = GetpaymentMethod('paypal_clientid');


        $apiContext = new ApiContext(
            new OAuthTokenCredential(
                $paypal_secretkey,
                $paypal_clientid
            )
        );




        // try {
        // Fetch payment details

        $payment = Payment::get($paymentId, $apiContext);
        $execution = new PaymentExecution();
        $execution->setPayerId($payerId);

        // $result = $payment->execute($execution, $apiContext);

        $transaction = $payment->getTransactions()[0]; // Use the SDK's getter method
        $itemList = $transaction->getItemList(); // Access item list
        $items = $itemList->getItems(); // Get items array
        $metadata = $items[0]; // First item metadata
        $customData = json_decode($transaction->getCustom() ?? '{}', true);

        // Prepare payment data
        $paymentData = [
            'id' => $customData['appointment_id'],
            'transaction_type' => 'paypal',
            'payment_status' => $customData['payment_status'], // Payment is approved
            'external_transaction_id' => $payment->getId(),
            'tip' => 0, // Add method for tip if applicable
            'advance_payment_status' => $customData['advance_payment_status'] ?? 0,
            'advance_payment_amount' => $customData['advance_payment_amount'] ?? 0,
            'advance_paid_amount' => $customData['advance_paid_amount'] ?? 0,
            'remaining_payment_amount' => $customData['remaining_payment_amount'] ?? 0, // Add method for remaining payment if applicable
            'metadata' => $metadata, // Store metadata object
            'paymentStatus' => 'approved', // PayPal-specific status
            'amountTotal' => $transaction->getAmount()->getTotal(),
            'currency' =>  $formattedCurrency,
            'clinic_id' => $customData['clinic_id'] ?? null,
            'doctor_id' => $customData['doctor_id'] ?? null,
            'appointment_date' => $customData['appointment_date'] ?? null,
            'appointment_time' => $customData['appointment_time'] ?? null,
            'appointment_id' => $customData['appointment_id'],
            'service_id' => $metadata->getSku(),
            'type' => $customData['type'],
        ];
        $this->savePayment($paymentData);
        return $this->handlePaymentSuccess($paymentData);
    }

    //paystack payment success
    protected function handlePaystackSuccess(Request $request)
    {
        // Retrieve reference from Paystack callback
        $reference = $request->input('reference');
        $paystackSecretKey = GetpaymentMethod('paystack_secretkey');

        $currency = GetcurrentCurrency();
        $formattedCurrency = strtoupper(strtolower($currency));


        // Verify payment status using the reference
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $paystackSecretKey,
        ])->get("https://api.paystack.co/transaction/verify/{$reference}");

        // Decode the response from Paystack
        $responseBody = $response->json();

        // If Paystack verification is successful, process the payment
        if ($responseBody['status']) {
            // Create paymentData object (you can use an array or a model, for simplicity, we use an array here)
            $paymentData = [
                'payment_status' => $responseBody['data']['metadata']['payment_status'], // Payment status from Paystack
                'amountTotal' => $responseBody['data']['amount'] / 100, // Convert kobo to naira
                'currency' => $formattedCurrency, // Paystack uses Naira (NGN)
                'advance_payment_status' => $responseBody['data']['metadata']['advance_payment_status'] ?? 0,
                'advance_payment_amount' => $responseBody['data']['metadata']['advance_payment_amount'] ?? 0,
                'advance_paid_amount' => $responseBody['data']['metadata']['advance_paid_amount'] ?? 0,
                'clinic_id' => $responseBody['data']['metadata']['clinic_id'],
                'doctor_id' => $responseBody['data']['metadata']['doctor_id'],
                'appointment_date' => $responseBody['data']['metadata']['appointment_date'],
                'appointment_time' => $responseBody['data']['metadata']['appointment_time'],
                'appointment_id' => $responseBody['data']['metadata']['appointment_id'],
                'transaction_type' => 'paystack', // Payment method (paystack in this case)
                'id' => $responseBody['data']['metadata']['appointment_id'], // Transaction ID
                'remaining_payment_amount' => $responseBody['data']['metadata']['remaining_payment_amount'] / 100,
                'type' => $responseBody['data']['metadata']['type'],
            ];

            $this->savePayment($paymentData);
            // Call your handlePaymentSuccess method with the paymentData object
            return $this->handlePaymentSuccess($paymentData);
        } else {
            // In case the payment verification fails, return an error message
            return redirect('/')->with('error', 'Payment verification failed: ' . $responseBody['message']);
        }
    }

    //flutterwave payment success
    protected function handleFlutterwaveSuccess(Request $request)
    {
        $tx_ref = $request->input('tx_ref');
        $flutterwaveKey = GetpaymentMethod('flutterwave_secretkey');

        $currency = GetcurrentCurrency();
        $formattedCurrency = strtoupper(strtolower($currency));


        // Verifying the payment status from Flutterwave using the transaction reference
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $flutterwaveKey,
        ])->get("https://api.flutterwave.com/v3/transactions/{$tx_ref}/verify");

        $responseBody = $response->json();

        if ($responseBody['status'] === 'success') {
            // Retrieve relevant metadata for the payment
            $metadata = $responseBody['data']['metadata'];

            // Building paymentData object using the response metadata and Flutterwave data
            $paymentData = [
                'amountTotal' => $responseBody['data']['amount'] / 100, // Convert amount from kobo/other unit to the main currency
                'currency' => $formattedCurrency,  // Adjust as needed
                'clinic_id' => $metadata['clinic_id'],
                'doctor_id' => $metadata['doctor_id'],
                'appointment_date' => $metadata['appointment_date'],
                'appointment_time' => $metadata['appointment_time'],
                'service_id' => $metadata['service_id'],  // Include service_id
                'appointment_id' => $metadata['appointment_id'],  // Include appointment_id
                'transaction_type' => 'flutterwave',  // Payment method used
                'id' => $responseBody['data']['id'],  // Flutterwave transaction ID
                'advance_payment_status' => $metadata['advance_payment_status'] ?? 0,
                'advance_payment_amount' => $metadata['advance_payment_amount'] ?? 0,
                'advance_paid_amount' => $metadata['advance_paid_amount'] ?? 0,
                'remaining_payment_amount' => $metadata['remaining_payment_amount'] ?? 0,
                'payment_status' => $metadata['payment_status'] ?? 0,
                'type' => $metadata['type'],
            ];

            // Now, pass the paymentData to your handlePaymentSuccess method
            return $this->handlePaymentSuccess($paymentData);
        } else {
            return redirect('/')->with('error', 'Payment verification failed: ' . $responseBody['message']);
        }
    }

    //handle rozar pay success
    protected function handleRazorpaySuccess(Request $request)
    {
        $paymentId = $request->input('razorpay_payment_id');
        $razorpayOrderId = session('razorpay_order_id');

        $currency = GetcurrentCurrency();
        $formattedCurrency = strtoupper(strtolower($currency));

        $razorpayKey = GetpaymentMethod('razorpay_publickey');
        $razorpaySecret = GetpaymentMethod('razorpay_secretkey');

        $api = new \Razorpay\Api\Api($razorpayKey, $razorpaySecret);

        try {
            // Fetch payment details
            $payment = $api->payment->fetch($paymentId);

            // Fetch order details to get notes/metadata
            $order = $api->order->fetch($payment['order_id']);
            $notes = $order['notes'];
            if ($payment['status'] == 'captured') {
                $paymentData = [
                    'amountTotal' => $payment['amount'] / 100, // Convert paise to INR
                    'currency' =>  $formattedCurrency,
                    'transaction_type' => 'razorpay',
                    'plan_id' => $notes['service_id'] ?? null,
                    'payment_id' => $payment['id'],
                    'transaction_id' => $order['id'],
                    'advance_payment_status' => $notes['advance_payment_status'] ?? 0,
                    'advance_payment_amount' => $notes['advance_payment_amount'] ?? 0,
                    'advance_paid_amount' => $notes['advance_paid_amount'] ?? 0,
                    'remaining_payment_amount' => $notes['remaining_payment_amount'] ?? 0,
                    'payment_status' => $notes['payment_status'] ?? 0,
                    'clinic_id' => $notes['clinic_id'] ?? null,
                    'doctor_id' => $notes['doctor_id'] ?? null,
                    'appointment_date' => $notes['appointment_date'] ?? null,
                    'appointment_time' => $notes['appointment_time'] ?? null,
                    'id' => $notes['appointment_id'] ?? null,
                    'type' => $notes['type'] ?? null,
                ];

                $this->savePayment($paymentData);
                return $this->handlePaymentSuccess($paymentData);
            } else {
                return redirect('/')->with('error', 'Payment failed: ' . ($payment['error_description'] ?? 'Unknown error'));
            }
        } catch (\Exception $e) {
            return redirect('/')->with('error', 'Payment verification failed: ' . $e->getMessage());
        }
    }

    //handle cinet payment success
    protected function handleCinetSuccess(Request $request)
    {
        $transactionId = $request->input('transaction_id');
        $paymentStatus = $request->input('status');
        $amount = $request->input('amount') / 100; // Convert cents to dollars
        $service_id = $request->input('service_id');
        $metadata = $request->input('metadata', []);

        if ($paymentStatus !== 'success') {
            return redirect('/')->with('error', 'Payment failed: Invalid payment status.');
        }

        $currency = GetcurrentCurrency();
        $formattedCurrency = strtoupper(strtolower($currency));

        // Payment data to be processed
        $paymentData = [
            'payment_status' => 1,
            'amountTotal' => $amount,
            'currency' =>  $formattedCurrency,
            'transaction_type' => 'cinet',
            'transaction_id' => $transactionId,
            'service_id' => $service_id,
            'advance_payment_status' => $metadata['advance_payment_status'] ?? 0,
            'advance_payment_amount' => $metadata['advance_payment_amount'] ?? 0,
            'advance_paid_amount' => $metadata['advance_paid_amount'] ?? 0,
            'clinic_id' => $metadata['clinic_id'] ?? null,
            'doctor_id' => $metadata['doctor_id'] ?? null,
            'appointment_date' => $metadata['appointment_date'] ?? null,
            'appointment_time' => $metadata['appointment_time'] ?? null,
            'appointment_id' => $metadata['appointment_id'] ?? null,
            'type' => $metadata['type'] ?? null,
        ];

        // Save the payment and process success
        $this->savePayment($paymentData);
        return $this->handlePaymentSuccess($paymentData);
    }


    //handle sadad payment success
    protected function handleSadadSuccess(Request $request)
    {
        $transactionId = $request->input('transaction_id');
        $paymentStatus = $request->input('status');
        $amount = $request->input('amount');
        $plan_id = $request->input('plan_id');
        $metadata = $request->input('metadata', []);

        if ($paymentStatus !== 'success') {
            return redirect('/')->with('error', 'Payment failed: Invalid payment status.');
        }

        $currency = GetcurrentCurrency();
        $formattedCurrency = strtoupper(strtolower($currency));
        // Payment data to be processed
        $paymentData = [
            'payment_status' => 1,
            'amountTotal' => $amount,
            'currency' => $formattedCurrency, // Assuming Sadad uses SAR (Saudi Riyal)
            'transaction_type' => 'sadad',
            'transaction_id' => $transactionId,
            'plan_id' => $plan_id,
            'advance_payment_status' => $metadata['advance_payment_status'] ?? 0,
            'advance_payment_amount' => $metadata['advance_payment_amount'] ?? 0,
            'advance_paid_amount' => $metadata['advance_paid_amount'] ?? 0,
            'clinic_id' => $metadata['clinic_id'] ?? null,
            'doctor_id' => $metadata['doctor_id'] ?? null,
            'appointment_date' => $metadata['appointment_date'] ?? null,
            'appointment_time' => $metadata['appointment_time'] ?? null,
            'appointment_id' => $metadata['appointment_id'] ?? null,
            'type' => $metadata['type'] ?? null,
        ];

        $this->savePayment($paymentData);
        return $this->handlePaymentSuccess($paymentData);
    }

    //handle airtel payment success
    protected function handleAirtelSuccess(Request $request)
    {
        $transactionId = $request->input('transaction_id');
        $paymentStatus = $request->input('status');
        $amount = $request->input('amount');
        $plan_id = $request->input('plan_id');
        $metadata = $request->input('metadata', []);

        if ($paymentStatus !== 'success') {
            return redirect('/')->with('error', 'Payment failed: Invalid payment status.');
        }

        $currency = GetcurrentCurrency();
        $formattedCurrency = strtoupper(strtolower($currency));

        // Payment data to be processed
        $paymentData = [
            'payment_status' => 1,
            'amountTotal' => $amount,
            'currency' =>  $formattedCurrency, // Assuming Airtel uses USD
            'transaction_type' => 'airtel',
            'transaction_id' => $transactionId,
            'plan_id' => $plan_id,
            'advance_payment_status' => $metadata['advance_payment_status'] ?? 0,
            'advance_payment_amount' => $metadata['advance_payment_amount'] ?? 0,
            'advance_paid_amount' => $metadata['advance_paid_amount'] ?? 0,
            'clinic_id' => $metadata['clinic_id'] ?? null,
            'doctor_id' => $metadata['doctor_id'] ?? null,
            'appointment_date' => $metadata['appointment_date'] ?? null,
            'appointment_time' => $metadata['appointment_time'] ?? null,
            'appointment_id' => $metadata['appointment_id'] ?? null,
            'type' => $metadata['type'] ?? null,
        ];

        $this->savePayment($paymentData);
        return $this->handlePaymentSuccess($paymentData);
    }

    //handle phonepe payment success
    protected function handlePhonePeSuccess(Request $request)
    {
        $transactionId = $request->input('transaction_id');
        $paymentStatus = $request->input('status');
        $amount = $request->input('amount') / 100; // Convert from paisa to rupees
        $planId = $request->input('plan_id');
        $metadata = $request->input('metadata', []);

        if ($paymentStatus !== 'SUCCESS') {
            return redirect('/')->with('error', 'Payment failed: Invalid payment status.');
        }

        $currency = GetcurrentCurrency();
        $formattedCurrency = strtoupper(strtolower($currency));
        // Payment data for processing
        $paymentData = [
            'payment_status' => 1,
            'amountTotal' => $amount,
            'currency' => $formattedCurrency,
            'transaction_type' => 'phonepe',
            'transaction_id' => $transactionId,
            'plan_id' => $planId,
            'metadata' => $metadata, // Add custom metadata if needed
        ];

        $this->savePayment($paymentData);
        return $this->handlePaymentSuccess($paymentData);
    }

    //handle payment success
    protected function handlePaymentSuccess($paymentData)
    {
        $previousUrl = url()->previous();
        $paymentStatus = $paymentData['payment_status']; // e.g., 'paid', 'unpaid'
        $amountTotal = $paymentData['amountTotal']; // Amount in cents
        $currency = Currency::where('is_primary', 1)->first();
        $clinicId = $paymentData['clinic_id'];
        $selectedClinic = null;
        $selectedDoctor = null;
        $doctorId = $paymentData['doctor_id'];
        $selectedClinic = Clinics::CheckMultivendor()->findOrFail($clinicId);
        $selectedDoctor = Doctor::CheckMultivendor()->with('user')->where('doctor_id', $doctorId)->first();
        $doctorId = $selectedDoctor->id;
        $currentStep = 2;
        // Default tab order if no match
        $tabs = [
            ['index' => 0, 'label' => __('frontend.choose_clinics'), 'value' => 'Choose Clinics'],
            ['index' => 1, 'label' => __('frontend.choose_doctors'), 'value' => 'Choose Doctors'],
            ['index' => 2, 'label' => __('frontend.choose_date_time_payment'), 'value' => 'Choose Date, Time, Payment'],
        ];
        $paymentDetails = [
            'message' => 'Great, Payment Successful!',
            'doctorName' => optional(optional($selectedDoctor)->user)->full_name,
            'clinicName' => optional($selectedClinic)->name,
            'appointmentDate' => $paymentData['appointment_date'] ?? '',
            'appointmentTime' => $paymentData['appointment_time'] ?? '',
            'formate_appointment_date' => isset($paymentData['appointment_date']) ? DateFormate($paymentData['appointment_date']) : '',
            'formate_appointment_time' => isset($paymentData['appointment_time']) ? Carbon::parse($paymentData['appointment_time'])->format(setting('time_format') ?? 'h:i A') : '',
            'bookingId' => $paymentData['appointment_id'] ?? $paymentData['id'] ??  '',
            'paymentVia' => $paymentData['transaction_type'] ?? '',
            'totalAmount' => isset($amountTotal) ? number_format($amountTotal, 2) : '',
            'currency' => $currency ? $currency->currency_symbol : 'USD',
        ];

        // List of available payment methods
        $paymentMethodsList = [
            'Cash' => 'cash_payment_method',  // Always available
            'Wallet' => 'wallet_payment_method', // Always available
            'Stripe' => 'str_payment_method',
            'Paystack' => 'paystack_payment_method',
            'PayPal' => 'paypal_payment_method',
            'Flutterwave' => 'flutterwave_payment_method',
            'Airtel' => 'airtel_payment_method',
            'PhonePay' => 'phonepay_payment_method',
            'Midtrans' => 'midtrans_payment_method',
            'Cinet' => 'cinet_payment_method',
            'Sadad' => 'sadad_payment_method',
            'Razor' => 'razor_payment_method',
        ];

        $enabledPaymentMethods = ['Cash', 'Wallet']; // Add Cash and Wallet by default

        // Iterate through all payment methods and check if they are enabled
        foreach ($paymentMethodsList as $displayName => $settingKey) {
            if (setting($settingKey, 0) == 1) { // Assuming 1 means enabled
                $enabledPaymentMethods[] = $displayName; // Add enabled methods to the list
            }
        }
        $selectedService = ClinicsService::CheckMultivendor()->findOrFail(1);
        $serviceId = $selectedService->id;

        if ($paymentData['type'] == 'appointment_detail') {
            return redirect()->route('appointment-details', ['id' => $paymentData['id']])->with(['payment_success' => true, 'paymentDetails' => $paymentDetails]);
        } else {
            return view('frontend::booking', compact('tabs', 'currentStep', 'paymentDetails', 'selectedService', 'serviceId', 'selectedClinic', 'clinicId', 'selectedDoctor', 'doctorId', 'previousUrl', 'tabs', 'enabledPaymentMethods'));
        }

        //return redirect($previousUrl)->with('success', 'Payment completed successfully!');
    }



    public function checkWalletBalance(Request $request)
    {
        $user = auth()->user();

        // Ensure the user is authenticated
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not authenticated.'], 401);
        }

        $wallet = Wallet::where('user_id', auth()->id())->first();
        $walletBalance = $wallet->amount; // Assume user has a wallet_balance attribute
        $totalAmount = $request->input('totalAmount');
        if ($walletBalance === null) {
            return response()->json(['success' => false, 'message' => 'Wallet balance not available.']);
        }

        return response()->json([
            'success' => true,
            'balance' => $walletBalance,
            'isSufficient' => $walletBalance >= $totalAmount
        ]);
    }


    public function randomSlot(Request $request)
    {
        // Fetch slots from clinic or service when no doctor is available
        $timezone = new \DateTimeZone(setting('default_time_zone') ?? 'UTC');
        $time_slot_duration = 10;
        $timeslot = ClinicsService::where('id', $request->service_id)->value('time_slot');

        if ($timeslot) {
            $time_slot_duration = ($timeslot === 'clinic_slot') ?
                (int) Clinics::where('id', $request->clinic_id)->value('time_slot') :
                (int) $timeslot;
        }

        // Get available doctor sessions for the clinic on the specified appointment date
        $clinicSessions = DoctorSession::where('clinic_id', $request->clinic_id)
            ->where('day', Carbon::parse($request->appointment_date)->locale('en')->dayName)
            ->get();

        // If no sessions are found, return an error
        if ($clinicSessions->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No available sessions for the selected date and clinic.'
            ]);
        }



        // Pick a random session from available sessions
        $randomSession = $clinicSessions->random(); // Randomly select one session
        $doctor = Doctor::where('doctor_id', $randomSession->doctor_id)->first();
        // Get the available time slots for the selected session
        $startTime = Carbon::parse($randomSession->start_time, $timezone);
        $endTime = Carbon::parse($randomSession->end_time, $timezone);
        $allAvailableSlots = [];

        $current = $startTime->copy();
        while ($current < $endTime) {
            $allAvailableSlots[] = $current->format('H:i');
            $current->addMinutes($time_slot_duration);
        }

        // Shuffle the available slots to randomize the order
        shuffle($allAvailableSlots);

        // Return the random available slots for the selected doctor session
        return response()->json([
            'status' => true,
            'doctor_id' => $doctor->id, // Pass the doctor_id of the selected session
            'session' => $randomSession, // Include the session details in the response if needed
            'available_slots' => $allAvailableSlots // Return the randomized available slots
        ]);
    }


    public function downloadPDF(Request $request)
    {
        $id = $request->id;
        $appointments = Appointment::with('user', 'doctor', 'clinicservice', 'cliniccenter', 'appointmenttransaction', 'patientEncounter.billingrecord')
            ->where('id', $id)
            ->where('status', 'checkout')
            ->whereHas('appointmenttransaction', function ($query) {
                $query->where('payment_status', 1);
            })->get();
        $appointments->each(function ($appointment) {
            $appointment->date_of_birth = optional($appointment->user)->date_of_birth ?? '-';
        });
        $data = $appointments->toArray();
        if ($request->is('api/*')) {
            $pdf = PDF::loadHTML(view("frontend::invoice", ['data' => $data])->render())
                ->setOptions(['defaultFont' => 'sans-serif']);

            $baseDirectory = storage_path('app/public');
            $highestDirectory = collect(File::directories($baseDirectory))->map(function ($directory) {
                return basename($directory);
            })->max() ?? 0;
            $nextDirectory = intval($highestDirectory) + 1;
            while (File::exists($baseDirectory . '/' . $nextDirectory)) {
                $nextDirectory++;
            }
            $newDirectory = $baseDirectory . '/' . $nextDirectory;
            File::makeDirectory($newDirectory, 0777, true);

            $filename = 'invoice_' . $id . '.pdf';
            $filePath = $newDirectory . '/' . $filename;

            $pdf->save($filePath);


            $url = url('storage/' . $nextDirectory . '/' . $filename);
            if (!isset($appointments) || $appointments->isEmpty() || !$appointments->first()->user_id) {
                return response()->json(['error' => 'User ID not found.'], 404);
            }
            $user_id = $appointments->first()->user_id;
            $user = User::findOrFail($user_id);
            $email = $user->email;
            $subject = 'Your Invoice';
            $details = __('appointment.invoice_find') . $url;

            Mail::to($email)->send(new InvoiceEmail($data, $subject, $details, $filePath, $filename));
            if (!empty($url)) {
                return response()->json(['status' => true, 'link' => $url], 200);
            } else {
                return response()->json(['status' => false, 'message' => 'Url Not Found'], 404);
            }
        } else {


            $view = view("frontend::invoice", ['data' => $data])->render();
            $pdf = Pdf::loadHTML($view);
            // $pdf = Pdf::loadView('appointment::backend.invoice', ['data' => $data]);
            return response()->streamDownload(
                function () use ($pdf) {
                    echo $pdf->output();
                },
                'invoice.pdf',
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="invoice.pdf"',
                ]
            );
        }
    }

    public function otherpatient(Request $request)
    {

        $data = $request->except('profile_image');

        $otherPatient = OtherPatient::create($data);


        if ($request->hasFile('profile_image')) {
            storeMediaFile($otherPatient, $request->file('profile_image'), 'profile_image');
        }
        return response()->json([
            'status' => true,
            'message' => 'Other patient stored successfully!',
            'data' => [
                'id' => $otherPatient->id,
                'profile_image' => $otherPatient->profile_image,
            ],
        ], 201);
    }

    public function otherpatientlist(Request $request)
    {
        $patientId = $request->input('patient_id');

        if (!$patientId) {
            return response()->json([]);
        }

        $data = OtherPatient::where('user_id', $patientId)
            ->get()
            ->map(function ($patient) {
                return [
                    'id' => $patient->id,
                    'first_name' => $patient->first_name,
                    'profile_image' => $patient->profile_image,
                ];
            });

        return response()->json($data);
    }

    public function manageProfile(Request $request)
    {
        return view('frontend::manage_profile');
    }

    public function manageProfile_index_data(Request $request)
    {
        // $patients = OtherPatient::where('user_id', auth()->user()->id)
        //     ->get()
        //     ->map(function ($patient) {
        //         return [
        //             'id' => $patient->id,
        //             'first_name' => $patient->first_name,
        //             'last_name' => $patient->last_name,
        //             'profile_image' => $patient->profile_image,
        //             'dob' => $patient->dob,
        //             'gender' => $patient->gender,
        //             'relation' => $patient->relation,
        //             'contact_number' => $patient->contactNumber,
        //         ];
        //     });


        $search = $request->input('search');
        $patients = OtherPatient::where('user_id', auth()->user()->id);

        if ($search) {
            $patients = $patients->where('title', 'like', '%' . $search . '%');
        }

        $patients = $patients->orderBy('updated_at', 'desc');

        return DataTables::of($patients)
            ->addColumn('card', function ($patients) {
                return view('frontend::components.card.other_patient', compact('patients'))->render();
            })
            ->rawColumns(['card'])
            ->make(true);


        return response()->json(['data' => $patients]);
    }
    public function editOtherPatient($id)
    {
        $patient = OtherPatient::find($id);

        if (!$patient) {
            return response()->json(['status' => false, 'message' => 'Patient not found'], 404);
        }

        return response()->json(['status' => true, 'data' => $patient]);
    }

    public function updateOtherPatient(Request $request, $id)
    {
        $patient = OtherPatient::find($id);

        if (!$patient) {
            return response()->json(['status' => false, 'message' => 'Patient not found'], 404);
        }

        $data = $request->except('profile_image');
        $patient->update($data);

        if ($request->hasFile('profile_image')) {
            storeMediaFile($patient, $request->file('profile_image'), 'profile_image');
        }

        return response()->json([
            'status' => true,
            'message' => 'Patient updated successfully!',
            'data' => [
                'id' => $patient->id,
                'profile_image' => $patient->profile_image,
            ],
        ], 200);
    }

    public function destroyOtherPatient($id)
    {
        $patient = OtherPatient::find($id);
        if ($patient) {
            $patient->delete();
            return response()->json(['message' => 'Patient deleted successfully!']);
        } else {
            return response()->json(['message' => 'Patient not found!'], 404);
        }
    }

    public function showRazorpayPaymentPage($order_id)
    {
        $data = session('razorpay_payment_data');
        if (!$data || $data['order_id'] !== $order_id) {
            abort(404, 'Invalid or expired payment session.');
        }
        return view('razorpay.payment', $data);
    }
}
