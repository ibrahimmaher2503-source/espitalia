<?php

namespace Modules\RequestNurse\Models;

use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class NurseRequest extends BaseModel
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'nurse_requests';

    protected $fillable = [
        'patient_id',
        'nurse_id',
        'service_description_ar',
        'service_description_en',
        'request_date',
        'preferred_date',
        'preferred_time',
        'duration_hours',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'country',
        'postal_code',
        'latitude',
        'longitude',
        'contact_number',
        'status',
        'payment_status',
        'total_amount',
        'patient_notes',
        'admin_notes',
        'cancelled_by',
        'cancellation_reason',
        'governorate_id',
        'city_id',
        'hourly_rate',
        'offer_id',
        'offer_discount',
        'offer_code',
    ];

    protected $casts = [
        'preferred_date' => 'date',
        'payment_status' => 'boolean',
        'duration_hours' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'governorate_id' => 'integer',
        'city_id' => 'integer',
    ];

    const CUSTOM_FIELD_MODEL = 'Modules\RequestNurse\Models\NurseRequest';

    protected static function newFactory()
    {
        return \Modules\RequestNurse\Database\Factories\NurseRequestFactory::new();
    }

    // ── Status Constants ───────────────────────────────────────

    const STATUS_PENDING = 'pending';

    const STATUS_CONFIRMED = 'confirmed';

    const STATUS_IN_PROGRESS = 'in_progress';

    const STATUS_COMPLETED = 'completed';

    const STATUS_CANCELLED = 'cancelled';

    const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_CONFIRMED,
        self::STATUS_IN_PROGRESS,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    // ── Relationships ──────────────────────────────────────────

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id', 'id');
    }

    public function offer()
    {
        return $this->belongsTo(\Modules\Offer\Models\Offer::class, 'offer_id');
    }

    public function nurse()
    {
        return $this->belongsTo(User::class, 'nurse_id', 'id');
    }

    public function nurseProfile()
    {
        return $this->belongsTo(Nurse::class, 'nurse_id', 'nurse_id');
    }

    public function governorate()
    {
        return $this->belongsTo(\Modules\Location\Models\Governorate::class);
    }

    public function governorateCity()
    {
        return $this->belongsTo(\Modules\Location\Models\GovernorateCity::class, 'city_id');
    }

    // ── Accessors ──────────────────────────────────────────────

    public function getServiceDescriptionAttribute()
    {
        $locale = app()->getLocale();
        $localized = $locale === 'ar' ? $this->service_description_ar : $this->service_description_en;

        return $localized ?: ($locale === 'ar' ? $this->service_description_en : $this->service_description_ar) ?: '';
    }

    public function getFullAddressAttribute()
    {
        $parts = array_filter([
            $this->address_line_1,
            $this->address_line_2,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country,
        ]);

        return implode(', ', $parts);
    }

    public function getStatusBadgeAttribute()
    {
        return match ($this->status) {
            self::STATUS_PENDING => '<span class="badge bg-warning text-dark">'.__('request_nurse.status_pending').'</span>',
            self::STATUS_CONFIRMED => '<span class="badge bg-info">'.__('request_nurse.status_confirmed').'</span>',
            self::STATUS_IN_PROGRESS => '<span class="badge bg-primary">'.__('request_nurse.status_in_progress').'</span>',
            self::STATUS_COMPLETED => '<span class="badge bg-success">'.__('request_nurse.status_completed').'</span>',
            self::STATUS_CANCELLED => '<span class="badge bg-danger">'.__('request_nurse.status_cancelled').'</span>',
            default => '<span class="badge bg-secondary">'.$this->status.'</span>',
        };
    }

    // ── Scopes ─────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    public function scopeByNurse($query, $nurseId)
    {
        return $query->where('nurse_id', $nurseId);
    }

    public function scopeByPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
