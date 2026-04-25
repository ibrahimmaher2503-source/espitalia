<?php

namespace Modules\Pharmacy\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PharmacyPayout extends BaseModel
{
    protected $table = 'pharmacy_payouts';

    protected $fillable = [
        'pharmacy_id',
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

    // Payment method constants
    const PAYMENT_METHOD_CASH = 'cash';

    const PAYMENT_METHOD_BANK_TRANSFER = 'bank_transfer';

    // Relationships
    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class, 'pharmacy_id');
    }

    public function earnings(): HasMany
    {
        return $this->hasMany(PharmacyCommissionEarning::class, 'payout_id');
    }
}
