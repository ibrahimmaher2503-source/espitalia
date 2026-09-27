<?php

namespace Modules\Pharmacy\Models;

use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;

class PharmacyPrescription extends BaseModel implements HasMedia
{
    protected $table = 'pharmacy_prescriptions';

    protected $fillable = [
        'prescription_number', 'order_id', 'user_id', 'pharmacy_id',
        'doctor_name', 'doctor_license_number', 'patient_name',
        'notes', 'status', 'reviewed_by', 'reviewed_at', 'rejection_reason',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_UNDER_REVIEW = 'under_review';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_LINKED = 'linked_to_order';

    const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_UNDER_REVIEW,
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
        self::STATUS_LINKED,
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->prescription_number)) {
                $year = date('Y');
                $last = static::withTrashed()
                    ->where('prescription_number', 'like', "PRX-{$year}-%")
                    ->orderByDesc('id')
                    ->value('prescription_number');

                $sequence = 1;
                if ($last && preg_match('/PRX-\d{4}-(\d+)/', $last, $matches)) {
                    $sequence = (int) $matches[1] + 1;
                }

                $model->prescription_number = sprintf('PRX-%s-%04d', $year, $sequence);
            }
        });
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('prescription_images');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class, 'pharmacy_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(PharmacyOrder::class, 'order_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByPharmacy($query, $pharmacyId)
    {
        return $query->where('pharmacy_id', $pharmacyId);
    }
}
