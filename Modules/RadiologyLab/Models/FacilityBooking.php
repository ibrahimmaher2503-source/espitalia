<?php

namespace Modules\RadiologyLab\Models;

use App\Models\BaseModel;
use App\Models\User;
use Carbon\Carbon;

class FacilityBooking extends BaseModel
{
    protected $table = 'facility_bookings';

    const STATUS_PENDING = 'pending';

    const STATUS_CONFIRMED = 'confirmed';

    const STATUS_CANCELLED = 'cancelled';

    const STATUS_COMPLETED = 'completed';

    const STATUS_NO_SHOW = 'no_show';

    const ALLOWED_TRANSITIONS = [
        self::STATUS_PENDING => [self::STATUS_CONFIRMED, self::STATUS_CANCELLED],
        self::STATUS_CONFIRMED => [self::STATUS_COMPLETED, self::STATUS_CANCELLED, self::STATUS_NO_SHOW],
        self::STATUS_CANCELLED => [],
        self::STATUS_COMPLETED => [],
        self::STATUS_NO_SHOW => [],
    ];

    protected $fillable = [
        'booking_number',
        'type',
        'lab_id',
        'radiology_center_id',
        'patient_id',
        'patient_name',
        'patient_phone',
        'scan_type',
        'booking_date',
        'booking_time',
        'status',
        'notes',
        'admin_notes',
        'cancellation_reason',
        'cancelled_by',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'booking_time' => 'string',
        'status' => 'string',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (empty($booking->booking_number)) {
                $booking->booking_number = static::generateBookingNumber();
            }
        });
    }

    public static function generateBookingNumber(): string
    {
        $year = Carbon::now()->year;
        $prefix = "BK-{$year}-";

        $lastBooking = static::withTrashed()
            ->where('booking_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->lockForUpdate()
            ->first();

        if ($lastBooking) {
            $parts = explode('-', $lastBooking->booking_number);
            $lastSequence = (int) end($parts);
            $nextSequence = $lastSequence + 1;
        } else {
            $nextSequence = 1;
        }

        return $prefix . str_pad($nextSequence, 4, '0', STR_PAD_LEFT);
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function lab()
    {
        return $this->belongsTo(Lab::class, 'lab_id');
    }

    public function radiologyCenter()
    {
        return $this->belongsTo(RadiologyCenter::class, 'radiology_center_id');
    }

    public function getFacilityAttribute()
    {
        return $this->type === 'lab' ? $this->lab : $this->radiologyCenter;
    }

    public function scopeByPatient($query, int $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('booking_date', $date);
    }

    public function scopeActive($query)
    {
        return $query->where('status', '!=', self::STATUS_CANCELLED);
    }

    public function canTransitionTo(string $newStatus): bool
    {
        return in_array($newStatus, self::ALLOWED_TRANSITIONS[$this->status] ?? []);
    }
}
