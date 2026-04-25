<?php

namespace Modules\Pharmacy\Models;

use App\Models\Address;
use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PharmacyOrder extends BaseModel
{
    protected $table = 'pharmacy_orders';

    protected $fillable = [
        'user_id',
        'pharmacy_id',
        'address_id',
        'subtotal',
        'delivery_fee',
        'total',
        'payment_method',
        'status',
        'delivery_type',
        'delivery_notes',
        'estimated_delivery_at',
        'actual_delivery_at',
        'delivery_person_id',
        'pickup_code',
        'pickup_verified_at',
        'coupon_id',
        'coupon_discount',
        'cancelled_by',
        'cancellation_reason',
        'cancelled_at',
        'refund_status',
        'refund_amount',
        'offer_id',
        'offer_discount',
        'offer_code',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'total' => 'decimal:2',
        'coupon_discount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'offer_discount' => 'decimal:2',
        'estimated_delivery_at' => 'datetime',
        'actual_delivery_at' => 'datetime',
        'pickup_verified_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';

    const STATUS_CONFIRMED = 'confirmed';

    const STATUS_PREPARING = 'preparing';

    const STATUS_ON_THE_WAY = 'on_the_way';

    const STATUS_DELIVERED = 'delivered';

    const STATUS_CANCELLED = 'cancelled';

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class, 'pharmacy_id');
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'address_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PharmacyOrderItem::class, 'order_id');
    }

    // Scopes
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(PharmacyCoupon::class, 'coupon_id');
    }

    public function offer()
    {
        return $this->belongsTo(\Modules\Offer\Models\Offer::class, 'offer_id');
    }

    public function deliveryPerson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivery_person_id');
    }

    public function cancelledByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function prescription()
    {
        return $this->hasOne(PharmacyPrescription::class, 'order_id');
    }

    public function refunds()
    {
        return $this->hasMany(PharmacyRefund::class, 'order_id');
    }
}
