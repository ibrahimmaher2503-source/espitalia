<?php

namespace Modules\Appointment\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use Carbon\Carbon;
use Modules\Clinic\Models\ClinicsService;
use Modules\Appointment\Models\AppointmentTransaction;
use Modules\Clinic\Models\Clinics;
use Modules\Commission\Models\CommissionEarning;
use Modules\Commission\Trait\CommissionTrait;
use Modules\Tip\Trait\TipTrait;
use Modules\Customer\Models\OtherPatient;
use Modules\Clinic\Models\DoctorRating;
use Modules\Clinic\Models\Doctor;
use Modules\Clinic\Models\Receptionist;
use Modules\Clinic\Models\DoctorIndependentService;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Appointment extends BaseModel
{
    use HasFactory;
    use SoftDeletes;
    use CommissionTrait;
    use TipTrait,LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['*']);
    }

    protected $table = 'appointments';

    const CUSTOM_FIELD_MODEL = 'Modules\Appointment\Models\Appointment';

    protected $fillable = ['appointment_number', 'status', 'booking_type', 'start_date_time', 'user_id', 'otherpatient_id', 'clinic_id', 'doctor_id', 'appointment_extra_info', 'appointment_date', 'appointment_time', 'booking_slot_key', 'service_id', 'independent_service_id', 'call_service_id', 'call_type', 'meeting_link', 'total_amount', 'service_amount', 'service_price', 'duration', 'advance_payment_amount', 'advance_paid_amount', 'notes_ar', 'notes_en', 'cancellation_charge_amount', 'offer_id', 'offer_discount', 'offer_code'];

    protected $appends = ['file_url'];

    protected $casts = [
        'user_id' => 'integer',
        'service_id' => 'integer',
        'doctor_id' => 'integer',
        'otherpatient_id' => 'integer',
        'independent_service_id' => 'integer',
        'call_service_id' => 'integer',
        'cancellation_charge_amount' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($appointment) {
            if (empty($appointment->appointment_number)) {
                $year = now()->format('Y');
                $lastNumber = static::whereYear('created_at', $year)->max('id') ?? 0;
                $appointment->appointment_number = "APT-{$year}-" . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
            }
        });

        static::saving(function ($appointment) {
            if (in_array(strtolower((string) $appointment->status), ['cancelled', 'canceled'], true)) {
                $appointment->booking_slot_key = null;
            } elseif ($appointment->booking_slot_key !== null) {
                $appointment->booking_slot_key = static::buildBookingSlotKey(
                    (int) $appointment->doctor_id,
                    $appointment->appointment_date,
                    $appointment->appointment_time,
                );
            }
        });

        static::deleting(function ($appointment) {
            if ($appointment->booking_slot_key !== null) {
                static::withoutEvents(function () use ($appointment) {
                    $appointment->newModelQuery()
                        ->whereKey($appointment->getKey())
                        ->update(['booking_slot_key' => null]);
                });
            }
        });

        static::restoring(function ($appointment) {
            if ($appointment->doctor_id && $appointment->appointment_date && $appointment->appointment_time) {
                $appointment->booking_slot_key = static::buildBookingSlotKey(
                    (int) $appointment->doctor_id,
                    $appointment->appointment_date,
                    $appointment->appointment_time,
                );
            }
        });
    }

    public static function buildBookingSlotKey(int $doctorId, mixed $date, mixed $time): string
    {
        $dateValue = $date instanceof Carbon
            ? $date->format('Y-m-d')
            : Carbon::parse((string) $date)->format('Y-m-d');
        $timeValue = Carbon::parse((string) $time)->format('H:i:s');

        return implode('|', [$doctorId, $dateValue, $timeValue]);
    }

    public function getNotesAttribute()
    {
        $locale = app()->getLocale();
        $localized = $locale === 'ar' ? $this->notes_ar : $this->notes_en;

        return $localized ?: ($locale === 'ar' ? $this->notes_en : $this->notes_ar) ?: '';
    }
    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return \Modules\Appointment\database\factories\AppointmentFactory::new();
    }
    public function getFileUrlAttribute()
    {
        $media = $this->getFirstMediaUrl('file_url');

        return isset($media) && !empty($media) ? $media : Null;
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id')->with('commissionData');
    }

    public function doctorData()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'doctor_id');
    }


    public function otherPatient()
    {
        return $this->belongsTo(OtherPatient::class, 'otherpatient_id', 'id');
    }
    
    public function clinicservice()
    {
        return $this->belongsTo(ClinicsService::class, 'service_id')->with('systemservice', 'serviceRating');
    }

    public function offer()
    {
        return $this->belongsTo(\Modules\Offer\Models\Offer::class, 'offer_id');
    }

    public function independentService()
    {
        return $this->belongsTo(DoctorIndependentService::class, 'independent_service_id');
    }

    public function appointmenttransaction()
    {
        return $this->hasOne(AppointmentTransaction::class, 'appointment_id');
    }

    public function cliniccenter()
    {
        return $this->belongsTo(Clinics::class, 'clinic_id')->with('vendor');
    }

    public function payment()
    {
        return $this->hasOne(AppointmentTransaction::class);
    }

    public function commissionsdata()
    {
        return $this->hasMany(CommissionEarning::class, 'commissionable_id', 'id');
    }

    public function receptionist()
    {
        return $this->belongsTo(Receptionist::class, 'clinic_id', 'clinic_id');
    }

    public function serviceRating()
    {
        return $this->hasOne(DoctorRating::class, 'service_id', 'service_id');
    }

    public function serviceRatingUnique($doctor_id)
    {

        return $this->hasOne(DoctorRating::class, 'service_id', 'service_id')
            ->where('doctor_id', $doctor_id);
    }
    public function patientEncounter()
    {
        return $this->hasOne(PatientEncounter::class, 'appointment_id')->with('billingrecord');
    }

    public function scopeCheckMultivendor($query)
    {
        if (multiVendor() == "0") {
            $userIds = User::whereIn('user_type', ['admin', 'demo_admin'])->pluck('id');

            if ($userIds->isNotEmpty()) {
                $query = $query->where(function ($q) use ($userIds) {
                    $q->whereHas('cliniccenter', function ($sub) use ($userIds) {
                        $sub->whereIn('vendor_id', $userIds);
                    })->orWhereIn('booking_type', ['independent', 'call']);
                });
            }
        }
    }

    public function scopeSetRole($query, $user)
    {
        $user_id = $user->id;

        if (auth()->user()->hasRole(['admin', 'demo_admin'])) {
            if (multiVendor() == "0") {
                $user_ids = User::whereHas('roles', fn ($query) => $query->whereIn('name', ['admin', 'demo_admin']))->pluck('id');
                $query->where(function ($q) use ($user_ids) {
                    $q->whereHas('cliniccenter', function ($sub) use ($user_ids) {
                        $sub->whereIn('vendor_id', $user_ids);
                    })->orWhereIn('booking_type', ['independent', 'call']);
                });
            }
            return $query;
        }

        if ($user->hasRole('vendor')) {
            $query->with('cliniccenter')->whereHas('cliniccenter', function ($query) use ($user_id) {
                $query->where('vendor_id', $user_id);
            });
            return $query;
        }

        if (auth()->user()->hasRole('doctor')) {
            if (multiVendor() == 0) {
                $doctor = Doctor::where('doctor_id', $user_id)->first();
                $vendorId = $doctor->vendor_id;
                $query = $query->where('doctor_id', $user_id)->where(function ($q) use ($vendorId) {
                    $q->whereHas('doctorData', function ($qry) use ($vendorId) {
                        $qry->where('vendor_id', $vendorId);
                    })->orWhereIn('booking_type', ['independent', 'call']);
                });
            } else {
                $query = $query->where('doctor_id', $user_id);
            }
            return $query;
        }

        if (auth()->user()->hasRole('receptionist')) {
            $Receptionist = Receptionist::where('receptionist_id', $user_id)->first();
            $vendorId = $Receptionist->vendor_id;
            $clinic_id = $Receptionist->clinic_id;

            if (multiVendor() == "0") {
                $query = $query->where('clinic_id', $clinic_id)->whereHas('cliniccenter', function ($qry) use ($vendorId) {
                    $qry->where('vendor_id', $vendorId);
                });
            } else {
                $query = $query->where('clinic_id', $clinic_id);
            }
            return $query;
        }

        if (auth()->user()->hasRole('user')) {
            $query->where('user_id', $user_id);
            return $query;
        }

        return $query;
    }

    public function bodyChart()
    {
        return $this->hasMany(AppointmentPatientBodychart::class, 'appointment_id')->with('patient_encounter');
    }

    public function getCancellationCharges(): float
    {
        // Retrieve service configuration settings
        $cancellationChargeHours = setting('cancellation_charge_hours',0);
        $cancellation_charge = setting('is_cancellation_charge',0);
        $cancelltion_Type = setting('cancellation_type','fixed');
        $cancellation_charge_amount = setting('cancellation_charge');
        $cancellation_charge = isset($cancellation_charge) ? $cancellation_charge : 0;
        $cancellationChargeAmount = 0;
        $datetime = setting('default_time_zone');
        if($cancellation_charge == 1 && auth()->check() && auth()->user()->user_type == 'user') {
            $cancellationChargeHours = isset($cancellationChargeHours) ? (double)$cancellationChargeHours : 0;
            $timezone = new \DateTimeZone($datetime ?? 'UTC');
            $date = \Carbon\Carbon::parse($this->appointment_date)->format('Y-m-d');
            $time = $this->appointment_time;
            $bookingDateTimeString = $date . ' ' . $time;
            $bookingTime = new \DateTime($bookingDateTimeString, $timezone);
            $cancellationRequestTime = new \DateTime('now', $timezone); // Current time when cancellation is requested
            if ($bookingTime > $cancellationRequestTime) {
            $timeDifference = $bookingTime->diff($cancellationRequestTime);
            $totalHours = ($timeDifference->days * 24) + $timeDifference->h + ($timeDifference->i / 60);
            if ($totalHours <= $cancellationChargeHours) {
                $cancellationCharge = isset($cancellation_charge_amount) ? (double)$cancellation_charge_amount : 0;
                        if($cancellationCharge > 0){
                            if($cancelltion_Type == 'percentage'){
                    $cancellationChargeAmount = $this->total_amount * $cancellationCharge / 100;
                            }else{
                    $cancellationChargeAmount = $cancellationCharge;
                }
                }
            }
            }
        }
        return $cancellationChargeAmount;
    }

    public function getRefundAmount()
    {
        $advance_paid_amount = $this->advance_paid_amount ?? 0;
        $total_paid = $this->total_amount ?? 0;
        $payment_status = optional($this->appointmenttransaction)->payment_status;
        $cancellation_charge_amount = $this->cancellation_charge_amount ?? 0;

        if($payment_status == 0) { // Unpaid
           
        return $advance_paid_amount - $cancellation_charge_amount; // refund
          
        } else { // Paid
            return $total_paid - $cancellation_charge_amount; // refund
        }
    }


}
