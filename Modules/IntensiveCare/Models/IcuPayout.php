<?php

namespace Modules\IntensiveCare\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IcuPayout extends BaseModel
{
    protected $table = 'icu_payouts';

    protected $fillable = [
        'hospital_id',
        'total_amount',
        'total_commission',
        'payment_method',
        'payment_date',
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'total_commission' => 'decimal:2',
        'payment_date' => 'date',
    ];

    const PAYMENT_METHOD_CASH = 'cash';

    const PAYMENT_METHOD_BANK_TRANSFER = 'bank_transfer';

    // ── Relationships ────────────────────────────────────────

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class, 'hospital_id');
    }

    public function earnings(): HasMany
    {
        return $this->hasMany(IcuCommissionEarning::class, 'payout_id');
    }
}
