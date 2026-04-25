<?php

namespace Modules\IntensiveCare\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IcuCommissionEarning extends BaseModel
{
    use HasFactory;

    protected $table = 'icu_commission_earnings';

    protected $fillable = [
        'admission_request_id',
        'hospital_id',
        'commission_id',
        'order_subtotal',
        'commission_type',
        'commission_value',
        'commission_amount',
        'hospital_receivable',
        'status',
        'payout_id',
    ];

    protected $casts = [
        'order_subtotal' => 'decimal:2',
        'commission_value' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'hospital_receivable' => 'decimal:2',
    ];

    const STATUS_UNPAID = 'unpaid';

    const STATUS_PAID = 'paid';

    const STATUS_CANCELLED = 'cancelled';

    // ── Relationships ────────────────────────────────────────

    public function admissionRequest(): BelongsTo
    {
        return $this->belongsTo(IcuAdmissionRequest::class, 'admission_request_id');
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class, 'hospital_id');
    }

    public function commission(): BelongsTo
    {
        return $this->belongsTo(IcuCommission::class, 'commission_id');
    }

    public function payout(): BelongsTo
    {
        return $this->belongsTo(IcuPayout::class, 'payout_id');
    }

    // ── Scopes ───────────────────────────────────────────────

    public function scopeUnpaid($query)
    {
        return $query->where('status', self::STATUS_UNPAID);
    }

    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    public function scopeForHospital($query, $hospitalId)
    {
        return $query->where('hospital_id', $hospitalId);
    }

    protected static function newFactory()
    {
        return \Modules\IntensiveCare\Database\Factories\IcuCommissionEarningFactory::new();
    }
}
