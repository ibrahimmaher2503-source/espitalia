<?php

namespace Modules\DoctorVisit\Models;

use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Clinic\Models\Doctor;

class DoctorVisitRequest extends BaseModel
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'doctor_visit_requests';

    // ── Status constants ──────────────────────────────────────────

    const STATUS_PENDING = 'pending';

    const STATUS_CONFIRMED = 'confirmed';

    const STATUS_CANCELLED = 'cancelled';

    const STATUS_COMPLETED = 'completed';

    const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_CONFIRMED,
        self::STATUS_CANCELLED,
        self::STATUS_COMPLETED,
    ];

    /** Valid status transitions: current status => allowed next statuses */
    const VALID_TRANSITIONS = [
        self::STATUS_PENDING => [self::STATUS_CONFIRMED, self::STATUS_CANCELLED],
        self::STATUS_CONFIRMED => [self::STATUS_COMPLETED, self::STATUS_CANCELLED],
        self::STATUS_COMPLETED => [],
        self::STATUS_CANCELLED => [],
    ];

    const VISIT_TYPE_HOME = 'home_visit';

    protected $fillable = [
        'patient_id', 'preferred_doctor_id', 'assigned_doctor_id',
        'visit_type', 'visit_reason', 'preferred_date',
        'contact_phone', 'additional_notes', 'status',
        'admin_notes', 'cancellation_reason', 'completed_at',
        'offer_id', 'offer_discount', 'offer_code',
    ];

    protected $casts = [
        'preferred_date' => 'date',
        'completed_at' => 'datetime',
        'offer_discount' => 'decimal:2',
    ];

    // ── Boot: auto-generate reference number ──────────────────────

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->reference_number)) {
                $year = now()->format('Y');
                $last = static::withTrashed()
                    ->where('reference_number', 'like', "VR-{$year}-%")
                    ->orderBy('id', 'desc')
                    ->lockForUpdate()
                    ->first();

                if ($last) {
                    $parts = explode('-', $last->reference_number);
                    $lastNumber = (int) end($parts);
                    $nextNumber = $lastNumber + 1;
                } else {
                    $nextNumber = 1;
                }

                $model->reference_number = 'VR-'.$year.'-'.str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    // ── Relationships ─────────────────────────────────────────────

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function offer()
    {
        return $this->belongsTo(\Modules\Offer\Models\Offer::class, 'offer_id');
    }

    public function preferredDoctor()
    {
        return $this->belongsTo(Doctor::class, 'preferred_doctor_id');
    }

    public function assignedDoctor()
    {
        return $this->belongsTo(Doctor::class, 'assigned_doctor_id');
    }

    public function statusHistories()
    {
        return $this->hasMany(DoctorVisitStatusHistory::class, 'visit_request_id');
    }
}
