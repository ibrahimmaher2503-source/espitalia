<?php

namespace Modules\Appointment\Http\Controllers\Backend;

use App\Authorizable;
use App\Models\User;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Modules\Appointment\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\CustomField\Models\CustomField;
use Modules\CustomField\Models\CustomFieldGroup;
use Yajra\DataTables\DataTables;
use Modules\Appointment\Trait\AppointmentTrait;
use Modules\Appointment\Models\AppointmentTransaction;
use Modules\Tax\Models\Tax;
use Google\Client;
use App\Models\Setting;
use App\Mail\AppointmentConfirmation;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\UniqueConstraintViolationException;
use Google\Service\Calendar;
use Google\Client as GoogleClient;
use Modules\Customer\Models\OtherPatient;;

use Modules\Clinic\Models\ClinicServiceMapping;
use Modules\Clinic\Models\Clinics;
use Modules\Clinic\Models\ClinicsService;
use Modules\Clinic\Models\Doctor;
use Modules\Clinic\Models\DoctorServiceMapping;
use Modules\Commission\Models\CommissionEarning;
use Modules\Constant\Models\Constant;
use Modules\Appointment\Trait\EncounterTrait;
use Modules\Clinic\Models\SystemService;
use Modules\Appointment\Models\BillingRecord;
use Modules\Appointment\Models\PatientEncounter;
use Modules\Appointment\Trait\BillingRecordTrait;
use Modules\Wallet\Models\Wallet;
use Modules\Wallet\Models\WalletHistory;

class AppointmentsController extends Controller
{
    use AppointmentTrait;
    use EncounterTrait;
    use BillingRecordTrait;

    protected string $exportClass = '\App\Exports\CustomerExport';

    public function __construct()
    {
        // Page Title
        $this->module_title = 'appointment.title';
        // module name
        $this->module_name = 'appointment';

        // module icon
        $this->module_icon = 'fa-solid fa-clipboard-list';

        view()->share([
            'module_title' => $this->module_title,
            'module_icon' => $this->module_icon,
            'module_name' => $this->module_name,
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    public function index(Request $request)
    {
        $filter = [
            'status' => $request->status,
        ];

        $module_action = 'List';
        $columns = CustomFieldGroup::columnJsonValues(new Appointment());
        $customefield = CustomField::exportCustomFields(new Appointment());

        $export_import = true;
        $export_columns = [
            [
                'value' => 'name',
                'text' => ' Name',
            ]
        ];
        $export_url = route('backend.appointment.export');

        return view('appointment::backend.appointment.index_datatable', compact('module_action', 'filter', 'columns', 'customefield', 'export_import', 'export_columns', 'export_url'));
    }

    /**
     * Select Options for Select 2 Request/ Response.
     *
     * @return Response
     */
    public function index_list(Request $request)
    {
        $term = trim($request->q);

        if (empty($term)) {
            return response()->json([]);
        }

        $query_data = Appointment::where('name', 'LIKE', "%$term%")->orWhere('slug', 'LIKE', "%$term%")->limit(7)->get();

        $data = [];

        foreach ($query_data as $row) {
            $data[] = [
                'id' => $row->id,
                'text' => $row->name . ' (Slug: ' . $row->slug . ')',
            ];
        }
        return response()->json($data);
    }

    public function index_data()
    {
        $query = Appointment::query();
        if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('demo_admin')) {
            $query;
        } else {
            $query->where('doctor_id', auth()->id());
        }
        $appointment_status = Constant::getAllConstant()->where('type', 'BOOKING_STATUS');
        $appointment_colors = Constant::getAllConstant()->where('type', 'BOOKING_STATUS_COLOR');
        $payment_status = config('appointment.PAYMENT_STATUS');

        $query->orderBy('created_at', 'desc');

        $doctor = User::where('user_type', 'doctor')->get();

        return Datatables::of($query)
            ->addColumn('check', function ($data) {
                return '<input type="checkbox" class="form-check-input select-table-row"  id="datatable-row-' . $data->id . '"  name="datatable_ids[]" value="' . $data->id . '" onclick="dataTableRowCheck(' . $data->id . ')">';
            })
            ->addColumn('action', function ($data) {
                return view('appointment::backend.appointment.datatable.action_column', compact('data'));
            })
            ->editColumn('status', function ($data) use ($appointment_status, $appointment_colors) {
                return view('booking::backend.appointment.datatable.select_column', compact('data', 'appointment_status', 'appointment_colors'));
            })
            ->editColumn('updated_at', function ($data) {
                $module_name = $this->module_name;

                $diff = Carbon::now()->diffInHours($data->updated_at);

                if ($diff < 25) {
                    return $data->updated_at->diffForHumans();
                } else {
                    return $data->updated_at->isoFormat('llll');
                }
            })
            ->rawColumns(['action', 'status', 'check'])
            ->orderColumns(['id'], '-:column $1')
            ->make(true);
        // Custom Fields For export
        $customFieldColumns = CustomField::customFieldData($datatable, User::CUSTOM_FIELD_MODEL, null);

        return $datatable->rawColumns(array_merge(['action', 'status', 'is_banned', 'email_verified_at', 'check', 'image'], $customFieldColumns))
            ->toJson();
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */

    public function otherpatient(Request $request)
    {

        $data = $request->except('profile_image');

        $otherPatient = OtherPatient::create($data);


        if ($request->hasFile('profile_image')) {
            storeMediaFile($otherPatient, $request->file('profile_image'), 'profile_image');
        }
        return response()->json([
            'status' => true,
            'message' =>  __('messages.member_added'),
            'data' => [
                'id' => $otherPatient->id,
                'profile_image' => $otherPatient->profile_image,
            ],
        ], 201);
    }

    public function store(Request $request)
    {
        $request->validate([
            'clinic_id' => ['required', 'integer', 'exists:clinic,id'],
            'service_id' => ['required', 'integer', 'exists:clinics_services,id'],
            'doctor_id' => ['required', 'integer'],
            'appointment_date' => ['required', 'date_format:Y-m-d'],
            'appointment_time' => ['required', 'date_format:H:i,H:i:s'],
            'otherpatient_id' => ['nullable', 'integer'],
            'description' => ['nullable', 'string', 'max:2000'],
            'coupon_code' => ['nullable', 'string', 'max:100'],
        ]);

        $userId = (int) auth()->id();
        $data = $request->only([
            'clinic_id',
            'service_id',
            'doctor_id',
            'appointment_date',
            'appointment_time',
            'description',
            'otherpatient_id',
        ]);
        $data['user_id'] = $userId;
        $data['otherpatient_id'] = ! empty($data['otherpatient_id']) ? (int) $data['otherpatient_id'] : null;

        if ($data['otherpatient_id'] !== null
            && ! OtherPatient::where('id', $data['otherpatient_id'])->where('user_id', $userId)->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'otherpatient_id' => __('messages.permission_denied'),
            ]);
        }

        $service = ClinicsService::query()->where('id', $data['service_id'])->where('status', 1)->first();
        $hasClinicService = ClinicServiceMapping::where('clinic_id', $data['clinic_id'])
            ->where('service_id', $data['service_id'])
            ->exists();
        $hasDoctorService = DoctorServiceMapping::where('doctor_id', $data['doctor_id'])
            ->where('service_id', $data['service_id'])
            ->exists();

        if (! $service || ! $hasClinicService || ! $hasDoctorService) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'service_id' => __('messages.invalid_request'),
            ]);
        }

        $appointmentTime = strlen($data['appointment_time']) === 8
            ? substr($data['appointment_time'], 0, 5)
            : $data['appointment_time'];
        $data['appointment_time'] = $appointmentTime;
        $data['start_date_time'] = Carbon::createFromFormat('Y-m-d H:i', $data['appointment_date'] . ' ' . $appointmentTime, setting('default_time_zone'));
        $data['booking_slot_key'] = Appointment::buildBookingSlotKey(
            (int) $data['doctor_id'],
            $data['appointment_date'],
            $data['appointment_time'],
        );

        $existingAppointment = Appointment::query()
            ->where('doctor_id', $data['doctor_id'])
            ->whereDate('appointment_date', $data['appointment_date'])
            ->whereTime('appointment_time', $data['appointment_time'])
            ->whereNotIn('status', ['cancelled', 'canceled'])
            ->lockForUpdate()
            ->first();

        if ($existingAppointment) {
            return response()->json([
                'message' => __('frontend.lbl_no_slots_available'),
                'status' => false,
            ], 422);
        }

        $serviceData = $this->getServiceAmount($data['service_id'], $data['doctor_id'], $data['clinic_id']);

        $taxes = Tax::active()
            ->whereNull('module_type')
            ->orWhere('module_type', 'services')
            ->where('tax_type', 'exclusive')
            ->where('status', 1)
            ->get();

        $data['service_price'] = $serviceData['service_price'];
        $data['service_amount'] = $serviceData['service_amount'];
        $data['total_amount'] = $serviceData['total_amount'];
        $data['duration'] = $serviceData['duration'];
        // Appointment status is a server decision; the patient cannot create
        // an already-completed/cancelled booking by posting a status field.
        $data['status'] = 'confirmed';

        if ($service->is_enable_advance_payment == 1) {
            $data['advance_payment_amount'] = $service->advance_payment_amount;
        }
        $data['appointment_extra_info'] = $data['description'] ?? null;

        // Apply offer/coupon discount if Offer module is active
        $offerResult = $this->applyOfferDiscount(
            (float) $data['service_amount'],
            $userId,
            (int) $data['service_id'],
            null,
            (int) $data['clinic_id'],
            $request->input('coupon_code'),
            $request->is('api/*') ? 'app' : 'web'
        );

        if ($offerResult['offer_discount'] > 0) {
            $data['offer_id'] = $offerResult['offer_id'];
            $data['offer_discount'] = $offerResult['offer_discount'];
            $data['offer_code'] = $offerResult['offer_code'];
            $data['service_amount'] = $offerResult['final_amount'];
            $data['total_amount'] = $offerResult['final_amount'] + ($data['total_amount'] - $serviceData['service_amount']);
        }

        try {
            $data = Appointment::create($data);
        } catch (UniqueConstraintViolationException $e) {
            if (str_contains($e->getMessage(), 'booking_slot_key')) {
                return response()->json([
                    'message' => __('frontend.lbl_no_slots_available'),
                    'status' => false,
                ], 422);
            }

            throw $e;
        }

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

        if ($request->hasFile('file_url')) {
            storeMediaFile($data, $request->file('file_url'));
        }

        if ($request->is('api/*')) {

            $service_data = ClinicsService::where('id', $data['service_id'])->with('systemservice')->first();

            $clinic_data = Clinics::where('id', $data['clinic_id'])->first();

            $data['service_name'] = $service_data->name ?? '--';
            $data['clinic_name'] = $clinic_data->name ?? '--';
            $notification_data = [
                'id' => $data->id,
                'description' => $data->description,
                'appointment_duration' => $data->duration,
                'user_id' => $data->user_id,
                'user_name' => optional($data->user)->first_name ?? default_user_name(),
                'doctor_id' => $data->doctor_id,
                'doctor_name' => optional($data->doctor)->first_name,
                'appointment_date' => $data->start_date_time->format('d/m/Y'),
                'appointment_time' => $data->start_date_time->format('h:i A'),
                'appointment_services_names' => optional($data->clinicservice)->name ?? '--',
                'appointment_services_image' => optional($data->clinicservice)->file_url,
                'appointment_date_and_time' => $data->start_date_time->format('Y-m-d H:i'),
                'latitude' => null,
                'longitude' => null,
                'clinic_name' => $data->clinic_name,
                'clinic_id' => optional($clinic_data)->id

            ];
            $startTime = Carbon::parse($data->appointment_time);
            $endTime = $startTime->copy()->addMinutes($data->duration);
            $data['end_time'] = $endTime->format('H:i:s') ?? '-';
            $appointmentTime = new \DateTime($data->appointment_time);
            $data['appointment_time'] = $appointmentTime->format('H:i:s') ?? '-';
            $this->sendNotificationOnBookingUpdate('new_appointment', $notification_data);
            $message = 'Your Appointment has been booked successfully.';
            $service = ClinicsService::where('id', $data['service_id'])->first();
            if ($service->is_enable_advance_payment == 1) {
                $total_amount = $serviceData['total_amount'];
                $percentage = (float)  $service->advance_payment_amount;
                $data['advance_paid_amount'] = ($total_amount * $percentage) / 100;
            }
            $data['advance_paid_amount'] = $data['advance_paid_amount'] ?? 0;
            return response()->json(['message' => $message, 'data' => $data, 'status' => true], 200);
        } else {
            $clinic_data = Clinics::where('id', $data['clinic_id'])->first();
            $data['clinic_name'] = $clinic_data->name ?? '--';

            $notification_data = [
                'id' => $data->id,
                'description' => $data->description,
                'appointment_duration' => $data->duration,
                'user_id' => $data->user_id,
                'user_name' => optional($data->user)->first_name ?? default_user_name(),
                'doctor_id' => $data->doctor_id,
                'doctor_name' => optional($data->doctor)->first_name,
                'appointment_date' => $data->start_date_time->format('d/m/Y'),
                'appointment_time' => $data->start_date_time->format('h:i A'),
                'appointment_services_names' => optional($data->clinicservice)->name ?? '--',
                'appointment_services_image' => optional($data->clinicservice)->file_url,
                'appointment_date_and_time' => $data->start_date_time->format('Y-m-d H:i'),
                'latitude' => null,
                'longitude' => null,
                'clinic_name' => $data->clinic_name,
                'clinic_id' => optional($clinic_data)->id,
                'clinic_address' => optional($clinic_data)->address ?? '',

            ];
            $this->sendNotificationOnBookingUpdate('new_appointment', $notification_data);

            $message = __('messages.create_form', ['form' => __('appointment.singular_title')]);
            return response()->json(['message' => $message, 'data' => $data, 'status' => true], 200);
        }
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $data = Appointment::findOrFail($id);
        $data['file_url'] = $data->file_url;
        return response()->json(['data' => $data, 'status' => true]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $data = Appointment::findOrFail($id);

        $data->update($request->all());
        if ($request->file_url == null) {
            $data->clearMediaCollection('file_url');
        }

        if ($request->hasFile('file_url')) {
            storeMediaFile($data, $request->file('file_url'), 'file_url');
        }
        if ($request->is('api/*')) {
            $message = __('messages.update_form', ['form' => __('appointment.singular_title')]);;
            return response()->json(['message' => $message, 'data' => $data, 'status' => true], 200);
        } else {
            $message = __('messages.update_form', ['form' => __('appointment.singular_title')]);
            return response()->json(['message' => $message, 'status' => true], 200);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $data = Appointment::findOrFail($id);

        $data->delete();

        $message = __('messages.delete_form', ['form' => __('appointment.singular_title')]);

        return response()->json(['message' => $message, 'status' => true], 200);
    }
    public function updatePaymentStatus($id, Request $request)
    {

        if (isset($request->action_type) && $request->action_type == 'update-payment-status') {
            $status = $request->value;
        }

        $appointment_status = Appointment::where('id', $id)->first();

        AppointmentTransaction::where('appointment_id', $id)->update(['payment_status' => $request->value]);

        if ($status == 1) {

            CommissionEarning::where('commissionable_id', $id)->update(['commission_status' => 'unpaid']);
        }
        $message = __('appointment.payment_status_update');

        return response()->json(['message' => $message, 'status' => true]);
    }
    public function updateStatus($id, Request $request)
    {
        $appointment = Appointment::where('id', $id)->with('user', 'doctor')->first();

        $startDate = Carbon::parse($appointment['start_date_time']);
        $status = $request->status;

        $wallet_amount = 0;
        $user_wallet = Wallet::where('user_id', $appointment->user_id)->first();
        if ($user_wallet) {
            $wallet_amount = $user_wallet->amount;
        }

        if (isset($request->action_type) && $request->action_type == 'update-status') {

            $status = $request->value;
        }
        $appointment->status = $status;

        if ($status == 'check_in') {

            $encouter_details = $this->generateEncounter($appointment);
            $encounter_details = PatientEncounter::where('id', $encouter_details->id)->with('appointment')->first();
            if ($encounter_details) {
                $billing_record = $this->generateBillingRecord($encounter_details);
                $billing_record = BillingRecord::where('id', $billing_record->id)->with('clinicservice', 'patientencounter')->first();
                if ($billing_record) {
                    $billing_item = $this->generateBillingItem($billing_record);
                }
            }
        }

        if ($status == 'checkout') {

            if ($appointment) {
                $encounter_details = PatientEncounter::where('appointment_id', $appointment->id)
                    ->with('appointment')
                    ->first();

                if ($encounter_details) {
                    $billing_details = BillingRecord::where('encounter_id', $encounter_details->id)
                        ->where('payment_status', 1)
                        ->first();

                    if ($billing_details) {
                        $encounter_details->update(['status' => 0]);
                    } else {
                        return response()->json(['message' => 'Payment not completed. Please complete the payment before checkout.', 'status' => false]);
                    }
                }
            }
        }
        $appointment->update();

        if ($status == 'cancelled') {

            $cancellation_charge_amount = $request->cancellation_charge_amount ?? 0;
            $cancellation_reason = $request->reason ?? null;
            $appointment->cancellation_charge = $request->cancellation_charge ?? null;
            $appointment->cancellation_type = $request->cancellation_type ?? null;

            $advance_paid_amount = $appointment->advance_paid_amount;
            $total_paid = $appointment->total_amount;

            $payment_status = optional($appointment->appointmenttransaction)->payment_status;
            $refund_amount = 0;

            if ($payment_status == 0) { // Unpaid
                if ($advance_paid_amount >= $cancellation_charge_amount) {
                    $refund_amount = $advance_paid_amount - $cancellation_charge_amount;
                } else {
                    // Deduct remaining from wallet
                    $wallet_deduct = $cancellation_charge_amount - $advance_paid_amount;
                    if ($wallet_deduct > 0) {
                        $user_wallet->amount -= $wallet_deduct;
                        $user_wallet->update();

                        // Wallet history: deduction
                        $debitHistory = new WalletHistory;
                        $debitHistory->user_id = $user_wallet->user_id;
                        $debitHistory->datetime = Carbon::now();
                        $debitHistory->activity_type = 'wallet_deduction';
                        $debitHistory->activity_message = trans('messages.wallet_deduction', ['value' => $appointment->id]);

                        $debit_data = [
                            'title' => $user_wallet->title,
                            'user_id' => $user_wallet->user_id,
                            'amount' => $user_wallet->amount,
                            'credit_debit_amount' => $wallet_deduct,
                            'transaction_type' => __('messages.debit'),
                            'appointment_id' => $appointment->id,
                            'cancellation_charge_amount' => $cancellation_charge_amount,
                            'cancellation_reason' => $cancellation_reason,
                        ];

                        $debitHistory->activity_data = json_encode($debit_data);
                        $debitHistory->save();
                    }

                    $refund_amount = 0; // No refund in this case
                }
            } else { // Paid

                $refund_amount = $total_paid - $cancellation_charge_amount;
            }

            if ($refund_amount > 0) {
                $user_wallet->amount += $refund_amount;
                $user_wallet->update();

                // Wallet history: refund
                $creditHistory = new WalletHistory;
                $creditHistory->user_id = $user_wallet->user_id;
                $creditHistory->datetime = Carbon::now();
                $creditHistory->activity_type = 'wallet_refund';
                $creditHistory->activity_message = trans('messages.wallet_refund', ['value' => $appointment->id]);

                $credit_data = [
                    'title' => $user_wallet->title,
                    'user_id' => $user_wallet->user_id,
                    'amount' => $user_wallet->amount,
                    'credit_debit_amount' => $refund_amount,
                    'transaction_type' => __('messages.credit'),
                    'appointment_id' => $appointment->id,
                    'cancellation_charge_amount' => $cancellation_charge_amount,
                    'cancellation_reason' => $cancellation_reason,
                ];

                $creditHistory->activity_data = json_encode($credit_data);
                $creditHistory->save();
            }

            // Notification only for advance-paid unpaid cases
            if ($advance_paid_amount && $payment_status == 0) {
                $notification_data = [
                    'activity_type' => 'wallet_refund',
                    'payment_status' => 'Advance Payment',
                    'wallet' => $user_wallet,
                    'appointment_id' => $appointment->id,
                    'refund_amount' => $refund_amount,
                    'cancellation_charge' => $cancellation_charge_amount,
                    'reason' => $cancellation_reason,
                ];

                $this->sendNotificationOnBookingUpdate('wallet_refund', $notification_data);
            }

            $appointment->reason = $cancellation_reason;
            $appointment->cancellation_charge_amount = $cancellation_charge_amount;
            $appointment->update();

            // Reverse offer usage on cancellation
            if ($appointment->offer_id && class_exists(\Modules\Offer\Services\OfferEligibilityService::class)) {
                try {
                    app(\Modules\Offer\Services\OfferEligibilityService::class)->reverseUsage(
                        $appointment->offer_id,
                        $appointment->id,
                        'appointment'
                    );
                } catch (\Throwable $e) {
                    \Log::warning('Offer usage reversal failed: ' . $e->getMessage());
                }
            }
        }

        $notify_type = null;

        switch ($status) {
            case 'confirmed':
                $notify_type = 'accept_appointment';
                break;
            case 'rejected':
                $notify_type = 'reject_appointment';
                break;
            case 'checkout':
                $notify_type = 'checkout_appointment';
                break;
            case 'cancelled':
                $notify_type = 'cancel_appointment';
                break;
        }
        $clinic_data = Clinics::where('id', $appointment->clinic_id)->first();


        $notification_data = [
            'id' => $appointment->id,
            'description' => $appointment->description,
            'appointment_duration' => $appointment->duration,
            'user_id' => $appointment->user_id,
            'user_name' => optional($appointment->user)->first_name ?? default_user_name(),
            'doctor_id' => $appointment->doctor_id,
            'doctor_name' => optional($appointment->doctor)->first_name,
            'appointment_date' => $startDate->format('d/m/Y'),
            'appointment_time' => $startDate->format('h:i A'),
            'appointment_services_names' => optional(optional(ClinicsService::with('systemservice')->find($appointment->service_id))->systemservice)->name ?? '--',
            'appointment_services_image' => optional($appointment->clinicservice)->file_url,
            'appointment_date_and_time' => $startDate->format('Y-m-d H:i'),
            'latitude' => null,
            'longitude' => null,
            'clinic_id' => $appointment->clinic_id,
            'clinic_name' => $clinic_data->name ?? '--',
            'updated_by_role' => auth()->user()->user_type ?? '',
        ];
        $this->sendNotificationOnBookingUpdate($notify_type, $notification_data);
        $message = __('appointment.status_update');

        return response()->json(['message' => $message, 'status' => true]);
    }

    public function view()
    {
        return view('appointment::backend.appointment.view');
    }


    public function savePayment(Request $request)
    {
        $validated = $request->validate([
            'id' => ['required', 'integer', 'exists:appointments,id'],
            'transaction_type' => ['required', 'string', 'in:cash,wallet'],
        ]);

        $appointment = Appointment::whereKey($validated['id'])->firstOrFail();
        if (auth()->user()?->user_type === 'user' && (int) $appointment->user_id !== (int) auth()->id()) {
            abort(403, __('messages.access_denied_role'));
        }

        $service = ClinicsService::findOrFail($appointment->service_id);
        $serviceData = $this->getServiceAmount(
            $appointment->service_id,
            $appointment->doctor_id,
            $appointment->clinic_id
        );
        $totalAmount = round(max((float) ($serviceData['total_amount'] ?? 0), 0), 2);
        $taxes = Tax::active()
            ->where(function ($query) {
                $query->whereNull('module_type')->orWhere('module_type', 'services');
            })
            ->where('tax_type', 'exclusive')
            ->where('status', 1)
            ->get();
        $transactionType = strtolower($validated['transaction_type']);

        $payment = DB::transaction(function () use ($appointment, $service, $serviceData, $taxes, $transactionType, $totalAmount) {
            $lockedAppointment = Appointment::whereKey($appointment->id)->lockForUpdate()->firstOrFail();
            if (auth()->user()?->user_type === 'user' && (int) $lockedAppointment->user_id !== (int) auth()->id()) {
                abort(403, __('messages.access_denied_role'));
            }

            $existing = AppointmentTransaction::where('appointment_id', $lockedAppointment->id)
                ->lockForUpdate()
                ->first();

            // A second key cannot charge an appointment that is already paid.
            if ($existing && (int) $existing->payment_status === 1) {
                return $existing;
            }

            // Cash is a pending settlement. Do not create another pending row
            // for a retry or silently convert an advance into cash.
            if ($existing && $transactionType === 'cash') {
                return $existing;
            }

            $advancePayment = false;
            $paymentStatus = $transactionType === 'wallet' ? 1 : 0;
            $amountToCharge = $totalAmount;

            if ($transactionType === 'wallet') {
                $isRemainingPayment = $existing
                    && (int) $existing->advance_payment_status === 1
                    && (int) $existing->payment_status === 0;

                if ($isRemainingPayment) {
                    $amountToCharge = round(max($totalAmount - (float) $existing->total_amount, 0), 2);
                } elseif ((int) $service->is_enable_advance_payment === 1) {
                    $percentage = min(max((float) $service->advance_payment_amount, 0), 100);
                    $amountToCharge = round(($totalAmount * $percentage) / 100, 2);
                    $advancePayment = true;
                    $paymentStatus = 0;
                }

                if ($amountToCharge <= 0) {
                    return $existing;
                }

                $wallet = Wallet::where('user_id', $lockedAppointment->user_id)
                    ->lockForUpdate()
                    ->first();
                if ($wallet === null || (float) $wallet->amount < $amountToCharge) {
                    return response()->json([
                        'status' => false,
                        'message' => __('messages.wallet_balance_error'),
                    ], 400);
                }

                $wallet->decrement('amount', $amountToCharge);
                $wallet->refresh();
                WalletHistory::create([
                    'user_id' => $wallet->user_id,
                    'datetime' => now(),
                    'activity_type' => 'paid_for_appointment',
                    'activity_message' => trans('messages.paid_for_appointment', ['value' => $lockedAppointment->id]),
                    'activity_data' => json_encode([
                        'title' => $wallet->title,
                        'user_id' => $wallet->user_id,
                        'amount' => $wallet->amount,
                        'credit_debit_amount' => $amountToCharge,
                        'transaction_type' => __('messages.debit'),
                        'appointment_id' => $lockedAppointment->id,
                    ]),
                ]);
            }

            $payment = AppointmentTransaction::updateOrCreate(
                ['appointment_id' => $lockedAppointment->id],
                [
                    'transaction_type' => $transactionType,
                    'total_amount' => $amountToCharge,
                    'payment_status' => $paymentStatus,
                    'discount_value' => $serviceData['discount_value'] ?? 0,
                    'discount_type' => $serviceData['discount_type'] ?? null,
                    'discount_amount' => $serviceData['discount_amount'] ?? 0,
                    'external_transaction_id' => null,
                    'tax_percentage' => $taxes->values()->toArray(),
                    'inclusive_tax' => $serviceData['service_inclusive_tax'] ?? 0,
                    'inclusive_tax_price' => $serviceData['total_inclusive_tax'] ?? 0,
                    'paid_at' => $transactionType === 'wallet' ? now() : null,
                ]
            );

            $payment->advance_payment_status = $advancePayment ? 1 : 0;
            $payment->save();
            if ($advancePayment) {
                $lockedAppointment->advance_paid_amount = $amountToCharge;
                $lockedAppointment->save();
            }

            return $payment;
        });

        if ($payment instanceof \Illuminate\Http\JsonResponse) {
            return $payment;
        }

        return response()->json([
            'message' => __('appointment.save_appointment'),
            'data' => $payment,
            'status' => true,
        ], 200);
    }
    public function joinGoogleMeet(Request $request)
    {
        $id = $request->id;
        $authUrl = Appointment::where('id', $id)->value('meet_link');
        return redirect($authUrl);
    }

    public function joinZoomMeet(Request $request)
    {
        $id = $request->id;
        $authUrl = Appointment::where('id', $id)->value('start_video_link');
        return redirect($authUrl);
    }


    public function generateMeetLink(Request $request, $startDateTime, $duration, $data)
    {
        $employee = User::find($data['doctor_id']);
        if ($employee) {
            $storedToken = $employee->google_access_token;
            try {
                $storedToken = decrypt($storedToken);
            } catch (\Throwable) {
                // Support legacy plaintext JSON while it is rotated by the
                // authenticated Google authorization flow.
            }
            $googleAccessToken = is_string($storedToken) ? json_decode($storedToken, true) : [];
            $accessToken = $googleAccessToken['access_token'] ?? null;
            $refreshToken = $googleAccessToken['refresh_token'] ?? null;
            $googleMeetSettings = Setting::whereIn('name', ['google_meet_method', 'google_clientid', 'google_secret_key'])
                ->pluck('val', 'name');
            $settings = $googleMeetSettings->toArray();
            $client = new GoogleClient([
                'client_id' => $settings['google_clientid'],
                'client_secret' => $settings['google_secret_key'],
                'redirect_uri' => 'postmessage',
                'access_type' => 'offline', // Use 'offline' for refresh token flow
                'prompt' => 'consent', // Use 'consent' to force user consent for refresh token
                'scopes' => ['https://www.googleapis.com/auth/calendar.events'],
            ]);

            $client->setAccessToken($accessToken);

            if ($client->isAccessTokenExpired()) {
                if ($refreshToken) {
                    $client->refreshToken($refreshToken);
                    $newAccessToken = $client->getAccessToken();
                    User::where('id', $data['doctor_id'])->update([
                        'google_access_token' => encrypt(json_encode($newAccessToken, JSON_THROW_ON_ERROR)),
                    ]);

                    $request->session()->put('google_access_token', json_encode($newAccessToken));
                } else {
                    $authUrl = $client->createAuthUrl();
                    return redirect()->away($authUrl);
                }
            }
        }
        $service = new Calendar($client);
        $user = User::find($data->user_id);
        $center = ClinicServiceMapping::with('service', 'center')
            ->where('service_id', $data->service_id)
            ->where('clinic_id', $data->center_id)
            ->first();
        if ($employee && $center) {
            $clinicService = $center->service;
            $clinic = $center->center;

            $emailData = [
                'service_name' => $clinicService->name,
                'user_name' => "{$user->first_name} {$user->last_name}",
                'clinic_name' => $clinic->name,
                'doctor_name' => "{$employee->first_name} {$employee->last_name}",
                'appointment_date' => $data->appointment_date,
                'appointment_time' => $data->appointment_time,
            ];
        }
        $contentSettings = Setting::whereIn('name', ['content', 'google_event'])->pluck('val', 'name');

        $content = $contentSettings['content'] ?? '';
        $googleEvent = $contentSettings['google_event'] ?? '';

        $placeholders = [
            '{{appointment_date}}' => $emailData['appointment_date'],
            '{{appointment_time}}' => $emailData['appointment_time'],
            '{{patient_name}}' => $emailData['user_name'],
            '{{clinic_name}}' => $emailData['clinic_name'],
            '{{appointment_desc}}' => $emailData['clinic_name'],
            '{{service_name}}' => $emailData['clinic_name'],
        ];

        foreach ($placeholders as $placeholder => $value) {
            $content = str_replace($placeholder, $value, $content);
            $googleEvent = str_replace($placeholder, $value, $googleEvent);
        }
        $startDateTime = Carbon::parse($data->appointment_date . ' ' . $data->appointment_time)->format('Y-m-d\TH:i:s');
        $endDateTime = Carbon::parse($startDateTime)->addHour()->format('Y-m-d\TH:i:s');

        $event = new \Google\Service\Calendar\Event([
            'summary' => $googleEvent,
            'description' => $content,
            'start' => [
                'dateTime' => $startDateTime,
                'timeZone' => 'UTC',
            ],
            'end' => [
                'dateTime' => $endDateTime,
                'timeZone' => 'UTC',
            ],
            'conferenceData' => [
                'createRequest' => [
                    'requestId' => uniqid(),
                    'conferenceSolutionKey' => [
                        'type' => 'hangoutsMeet',
                    ],
                ],
            ],
        ]);
        $calendarId = 'primary';
        $event = $service->events->insert($calendarId, $event, ['conferenceDataVersion' => 1]);
        $meetingDetails = [
            'title' => $event->getSummary(),
            'description' => $event->getDescription(),
            'start' => $event->getStart()->getDateTime(),
            'end' => $event->getEnd()->getDateTime(),
            'location' => $event->getLocation(),
            'attendees' => $event->getAttendees(),
            'link' => $event->getHangoutLink(),
        ];
        $hangoutLink = $meetingDetails['link'];

        if (!empty($hangoutLink)) {
            $data->meet_link = $hangoutLink;
        }
        $data->save();

        $emails = User::whereIn('id', [$data['employee_id'], $data['user_id']])
            ->pluck('email')
            ->toArray();
        Mail::to($emails)->send(new AppointmentConfirmation($meetingDetails));
        return $meetingDetails;
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
}
