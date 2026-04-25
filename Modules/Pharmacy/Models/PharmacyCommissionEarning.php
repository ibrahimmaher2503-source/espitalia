<?php

namespace Modules\Pharmacy\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PharmacyCommissionEarning extends BaseModel
{
    protected $table = 'pharmacy_commission_earnings';

    protected $fillable = [
        'order_id',
        'pharmacy_id',
        'commission_id',
        'order_subtotal',
        'commission_type',
        'commission_value',
        'commission_amount',
        'pharmacy_receivable',
        'status',
        'payout_id',
    ];

    protected $casts = [
        'order_subtotal' => 'decimal:2',
        'commission_value' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'pharmacy_receivable' => 'decimal:2',
    ];

    // Status constants
    const STATUS_UNPAID = 'unpaid';

    const STATUS_PAID = 'paid';

    const STATUS_CANCELLED = 'cancelled';

    // Relationships
    public function order(): BelongsTo
    {
        return $this->belongsTo(PharmacyOrder::class, 'order_id');
    }

    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class, 'pharmacy_id');
    }

    public function commission(): BelongsTo
    {
        return $this->belongsTo(PharmacyCommission::class, 'commission_id');
    }

    public function payout(): BelongsTo
    {
        return $this->belongsTo(PharmacyPayout::class, 'payout_id');
    }

    // Scopes
    public function scopeUnpaid($query)
    {
        return $query->where('status', self::STATUS_UNPAID);
    }

    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    public function scopeForPharmacy($query, $pharmacyId)
    {
        return $query->where('pharmacy_id', $pharmacyId);
    }
}
