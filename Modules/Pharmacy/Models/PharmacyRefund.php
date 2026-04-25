<?php

namespace Modules\Pharmacy\Models;

use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PharmacyRefund extends BaseModel
{
    protected $table = 'pharmacy_refunds';

    protected $fillable = [
        'refund_number', 'order_id', 'user_id', 'reason',
        'refund_type', 'refund_amount', 'refund_method',
        'status', 'admin_notes', 'processed_by', 'processed_at',
    ];

    protected $casts = [
        'refund_amount' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_PROCESSED = 'processed';
    const STATUS_DECLINED = 'declined';

    const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_APPROVED,
        self::STATUS_PROCESSED,
        self::STATUS_DECLINED,
    ];

    const TYPE_FULL = 'full';
    const TYPE_PARTIAL = 'partial';

    const METHOD_ORIGINAL = 'original_method';
    const METHOD_WALLET = 'wallet';
    const METHOD_MANUAL = 'manual';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->refund_number)) {
                $year = date('Y');
                $last = static::withTrashed()
                    ->where('refund_number', 'like', "RFD-{$year}-%")
                    ->orderByDesc('id')
                    ->lockForUpdate()
                    ->value('refund_number');

                $sequence = 1;
                if ($last && preg_match('/RFD-\d{4}-(\d+)/', $last, $matches)) {
                    $sequence = (int) $matches[1] + 1;
                }

                $model->refund_number = sprintf('RFD-%s-%04d', $year, $sequence);
            }
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(PharmacyOrder::class, 'order_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeByOrder($query, $orderId)
    {
        return $query->where('order_id', $orderId);
    }
}
