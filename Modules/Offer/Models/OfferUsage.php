<?php

namespace Modules\Offer\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfferUsage extends Model
{
    protected $table = 'offer_usages';

    protected $fillable = [
        'offer_id', 'user_id', 'booking_type', 'booking_id',
        'service_id', 'branch_id', 'original_amount', 'discount_amount',
        'final_amount', 'coupon_code', 'status', 'reversed_at', 'reversal_reason',
    ];

    protected $casts = [
        'original_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'reversed_at' => 'datetime',
    ];

    // Status constants
    const STATUS_APPLIED = 'applied';
    const STATUS_COMPLETED = 'completed';
    const STATUS_REVERSED = 'reversed';
    const STATUS_CANCELLED = 'cancelled';

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(\Modules\Clinic\Models\ClinicsService::class, 'service_id');
    }

    // --- Scopes ---

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForOffer($query, int $offerId)
    {
        return $query->where('offer_id', $offerId);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_APPLIED, self::STATUS_COMPLETED]);
    }
}
