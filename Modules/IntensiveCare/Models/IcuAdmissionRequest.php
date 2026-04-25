<?php

namespace Modules\IntensiveCare\Models;

use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IcuAdmissionRequest extends BaseModel
{
    use HasFactory;

    protected $table = 'icu_admission_requests';

    protected $fillable = [
        'request_number',
        'patient_id',
        'hospital_id',
        'icu_department_id',
        'patient_name',
        'patient_age',
        'patient_gender',
        'national_id',
        'insurance_number',
        'medical_condition_ar',
        'medical_condition_en',
        'diagnosis_ar',
        'diagnosis_en',
        'case_type',
        'urgency',
        'needs_ventilator',
        'needs_oxygen',
        'current_location',
        'needs_ambulance',
        'contact_name',
        'contact_phone',
        'relationship_to_patient',
        'payment_method',
        'insurance_provider',
        'status',
        'admin_notes_ar',
        'admin_notes_en',
        'rejection_reason_ar',
        'rejection_reason_en',
        'response_date',
        'responded_by',
        'cancelled_by',
        'cancellation_reason',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'needs_ventilator' => 'boolean',
        'needs_oxygen' => 'boolean',
        'needs_ambulance' => 'boolean',
        'response_date' => 'datetime',
        'patient_age' => 'integer',
    ];

    // ── Status Constants ─────────────────────────────────────

    const STATUS_PENDING = 'pending';

    const STATUS_ACCEPTED = 'accepted';

    const STATUS_REJECTED = 'rejected';

    const STATUS_INFO_REQUESTED = 'info_requested';

    const STATUS_CANCELLED = 'cancelled';

    const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_ACCEPTED,
        self::STATUS_REJECTED,
        self::STATUS_INFO_REQUESTED,
        self::STATUS_CANCELLED,
    ];

    // Status transitions
    const ALLOWED_TRANSITIONS = [
        self::STATUS_PENDING => [self::STATUS_ACCEPTED, self::STATUS_REJECTED, self::STATUS_INFO_REQUESTED, self::STATUS_CANCELLED],
        self::STATUS_INFO_REQUESTED => [self::STATUS_PENDING, self::STATUS_CANCELLED],
        self::STATUS_ACCEPTED => [self::STATUS_CANCELLED],
        self::STATUS_REJECTED => [],
        self::STATUS_CANCELLED => [],
    ];

    // Other constants
    const CASE_TYPES = ['stroke', 'cardiac', 'post_operative', 'ventilator', 'neonatal', 'pediatric', 'burns', 'general'];

    const URGENCY_LEVELS = ['critical', 'urgent', 'standard'];

    const PAYMENT_METHODS = ['insurance', 'cash'];

    const GENDER_OPTIONS = ['male', 'female'];

    // ── Auto-generate request number ─────────────────────────

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($request) {
            if (empty($request->request_number)) {
                $year = now()->format('Y');
                $lastRequest = static::withTrashed()
                    ->where('request_number', 'like', "ICU-{$year}-%")
                    ->orderBy('id', 'desc')
                    ->lockForUpdate()
                    ->first();

                if ($lastRequest) {
                    // Extract the numeric part after the last hyphen
                    $parts = explode('-', $lastRequest->request_number);
                    $lastNumber = (int) end($parts);
                    $nextNumber = $lastNumber + 1;
                } else {
                    $nextNumber = 1;
                }

                // Pad to at least 4 digits, but allow more if needed
                $request->request_number = "ICU-{$year}-".str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    // ── Relationships ────────────────────────────────────────

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function hospital()
    {
        return $this->belongsTo(Hospital::class, 'hospital_id');
    }

    public function icuDepartment()
    {
        return $this->belongsTo(IcuDepartment::class, 'icu_department_id');
    }

    public function respondedBy()
    {
        return $this->belongsTo(User::class, 'responded_by');
    }

    // ── Methods ──────────────────────────────────────────────

    public function canTransitionTo($newStatus)
    {
        $allowed = self::ALLOWED_TRANSITIONS[$this->status] ?? [];

        return in_array($newStatus, $allowed);
    }

    // ── Scopes ───────────────────────────────────────────────

    public function scopeByPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    public function scopeByHospital($query, $hospitalId)
    {
        return $query->where('hospital_id', $hospitalId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeUrgent($query)
    {
        return $query->whereIn('urgency', ['critical', 'urgent']);
    }

    // ── Bilingual Accessors ───────────────────────────────────

    public function getMedicalConditionAttribute()
    {
        $locale = app()->getLocale();
        $localized = $locale === 'ar' ? $this->medical_condition_ar : $this->medical_condition_en;

        return $localized ?: ($locale === 'ar' ? $this->medical_condition_en : $this->medical_condition_ar) ?: '';
    }

    public function getDiagnosisAttribute()
    {
        $locale = app()->getLocale();
        $localized = $locale === 'ar' ? $this->diagnosis_ar : $this->diagnosis_en;

        return $localized ?: ($locale === 'ar' ? $this->diagnosis_en : $this->diagnosis_ar) ?: '';
    }

    public function getAdminNotesAttribute()
    {
        $locale = app()->getLocale();
        $localized = $locale === 'ar' ? $this->admin_notes_ar : $this->admin_notes_en;

        return $localized ?: ($locale === 'ar' ? $this->admin_notes_en : $this->admin_notes_ar) ?: '';
    }

    public function getRejectionReasonAttribute()
    {
        $locale = app()->getLocale();
        $localized = $locale === 'ar' ? $this->rejection_reason_ar : $this->rejection_reason_en;

        return $localized ?: ($locale === 'ar' ? $this->rejection_reason_en : $this->rejection_reason_ar) ?: '';
    }

    // ── Accessors ────────────────────────────────────────────

    public function getStatusBadgeAttribute()
    {
        return match ($this->status) {
            self::STATUS_PENDING => '<span class="badge bg-warning text-dark">'.__('intensive_care.status_pending').'</span>',
            self::STATUS_ACCEPTED => '<span class="badge bg-success">'.__('intensive_care.status_accepted').'</span>',
            self::STATUS_REJECTED => '<span class="badge bg-danger">'.__('intensive_care.status_rejected').'</span>',
            self::STATUS_INFO_REQUESTED => '<span class="badge bg-info">'.__('intensive_care.status_info_requested').'</span>',
            self::STATUS_CANCELLED => '<span class="badge bg-secondary">'.__('intensive_care.status_cancelled').'</span>',
            default => '<span class="badge bg-secondary">'.$this->status.'</span>',
        };
    }

    public function getUrgencyBadgeAttribute()
    {
        return match ($this->urgency) {
            'critical' => '<span class="badge bg-danger">'.__('intensive_care.urgency_critical').'</span>',
            'urgent' => '<span class="badge bg-warning text-dark">'.__('intensive_care.urgency_urgent').'</span>',
            'standard' => '<span class="badge bg-secondary">'.__('intensive_care.urgency_standard').'</span>',
            default => '<span class="badge bg-secondary">'.$this->urgency.'</span>',
        };
    }

    public function getPaymentBadgeAttribute()
    {
        return match ($this->payment_method) {
            'insurance' => '<span class="badge bg-info">'.__('intensive_care.payment_insurance').'</span>',
            'cash' => '<span class="badge bg-secondary">'.__('intensive_care.payment_cash').'</span>',
            default => '<span class="badge bg-secondary">'.$this->payment_method.'</span>',
        };
    }

    public function getCaseTypeLabelAttribute()
    {
        return __('intensive_care.case_'.$this->case_type);
    }

    // ── Media ────────────────────────────────────────────────

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('medical_reports');
    }

    protected static function newFactory()
    {
        return \Modules\IntensiveCare\Database\Factories\IcuAdmissionRequestFactory::new();
    }
}
