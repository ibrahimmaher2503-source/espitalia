<?php

namespace Modules\RadiologyLab\Models;

use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class TestOrder extends BaseModel
{
    use HasFactory;

    protected $table = 'test_orders';

    protected $fillable = [
        'order_number',
        'patient_id',
        'doctor_id',
        'lab_technician_id',
        'clinical_notes',
        'priority',
        'order_date',
        'status',
        'payment_status',
        'total_amount',
        'discount_amount',
        'final_amount',
        'admin_notes',
        'cancelled_by',
        'cancellation_reason',
        'created_by',
        'updated_by',
        'deleted_by',
        'offer_id',
        'offer_discount',
        'offer_code',
    ];

    protected $casts = [
        'order_date' => 'date',
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'offer_discount' => 'decimal:2',
    ];

    // ── Status Constants ───────────────────────────────────────

    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_SAMPLE_COLLECTED = 'sample_collected';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';

    const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_CONFIRMED,
        self::STATUS_SAMPLE_COLLECTED,
        self::STATUS_PROCESSING,
        self::STATUS_COMPLETED,
        self::STATUS_DELIVERED,
        self::STATUS_CANCELLED,
    ];

    // Priority constants
    const PRIORITY_ROUTINE = 'routine';
    const PRIORITY_URGENT = 'urgent';
    const PRIORITY_STAT = 'stat';

    const PRIORITIES = [
        self::PRIORITY_ROUTINE,
        self::PRIORITY_URGENT,
        self::PRIORITY_STAT,
    ];

    // Status transitions
    const ALLOWED_TRANSITIONS = [
        self::STATUS_PENDING => [self::STATUS_CONFIRMED, self::STATUS_CANCELLED],
        self::STATUS_CONFIRMED => [self::STATUS_SAMPLE_COLLECTED, self::STATUS_CANCELLED],
        self::STATUS_SAMPLE_COLLECTED => [self::STATUS_PROCESSING, self::STATUS_CANCELLED],
        self::STATUS_PROCESSING => [self::STATUS_COMPLETED],
        self::STATUS_COMPLETED => [self::STATUS_DELIVERED],
        self::STATUS_DELIVERED => [],
        self::STATUS_CANCELLED => [],
    ];

    // Auto-generate order number
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $year = now()->format('Y');
                $lastOrder = static::withTrashed()
                    ->where('order_number', 'like', "LAB-{$year}-%")
                    ->orderBy('id', 'desc')
                    ->lockForUpdate()
                    ->first();

                if ($lastOrder) {
                    // Extract the numeric part after the last hyphen
                    $parts = explode('-', $lastOrder->order_number);
                    $lastNumber = (int) end($parts);
                    $nextNumber = $lastNumber + 1;
                } else {
                    $nextNumber = 1;
                }

                // Pad to at least 4 digits, but allow more if needed
                $order->order_number = "LAB-{$year}-" . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    // ── Relationships ──────────────────────────────────────────

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function offer()
    {
        return $this->belongsTo(\Modules\Offer\Models\Offer::class, 'offer_id');
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function labTechnician()
    {
        return $this->belongsTo(User::class, 'lab_technician_id');
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function items()
    {
        return $this->hasMany(TestOrderItem::class, 'test_order_id');
    }

    public function reports()
    {
        return $this->hasMany(TestReport::class, 'test_order_id');
    }

    public function transactions()
    {
        return $this->hasMany(TestOrderTransaction::class, 'test_order_id');
    }

    // ── Scopes ─────────────────────────────────────────────────

    public function scopeByPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    public function scopeByTechnician($query, $technicianId)
    {
        return $query->where('lab_technician_id', $technicianId);
    }

    public function scopeByDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    // ── Accessors ──────────────────────────────────────────────

    public function getStatusBadgeAttribute()
    {
        return match ($this->status) {
            self::STATUS_PENDING => '<span class="badge bg-warning text-dark">' . __('radiology_lab.status_pending') . '</span>',
            self::STATUS_CONFIRMED => '<span class="badge bg-info">' . __('radiology_lab.status_confirmed') . '</span>',
            self::STATUS_SAMPLE_COLLECTED => '<span class="badge bg-primary">' . __('radiology_lab.status_sample_collected') . '</span>',
            self::STATUS_PROCESSING => '<span class="badge bg-primary">' . __('radiology_lab.status_processing') . '</span>',
            self::STATUS_COMPLETED => '<span class="badge bg-success">' . __('radiology_lab.status_completed') . '</span>',
            self::STATUS_DELIVERED => '<span class="badge bg-success">' . __('radiology_lab.status_delivered') . '</span>',
            self::STATUS_CANCELLED => '<span class="badge bg-danger">' . __('radiology_lab.status_cancelled') . '</span>',
            default => '<span class="badge bg-secondary">' . $this->status . '</span>',
        };
    }

    public function getPaymentBadgeAttribute()
    {
        return match ($this->payment_status) {
            'paid' => '<span class="badge bg-success">' . __('radiology_lab.payment_paid') . '</span>',
            'refunded' => '<span class="badge bg-warning text-dark">' . __('radiology_lab.payment_refunded') . '</span>',
            default => '<span class="badge bg-danger">' . __('radiology_lab.payment_unpaid') . '</span>',
        };
    }

    public function getPriorityBadgeAttribute()
    {
        return match ($this->priority) {
            self::PRIORITY_URGENT => '<span class="badge bg-warning text-dark">' . __('radiology_lab.priority_urgent') . '</span>',
            self::PRIORITY_STAT => '<span class="badge bg-danger">' . __('radiology_lab.priority_stat') . '</span>',
            default => '<span class="badge bg-secondary">' . __('radiology_lab.priority_routine') . '</span>',
        };
    }

    public function canTransitionTo($newStatus)
    {
        $allowed = self::ALLOWED_TRANSITIONS[$this->status] ?? [];

        return in_array($newStatus, $allowed);
    }

    protected static function newFactory()
    {
        return \Modules\RadiologyLab\Database\Factories\TestOrderFactory::new();
    }
}
