<?php

namespace Modules\Pharmacy\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PharmacyCouponUsage extends Model
{
    public $timestamps = false;

    protected $table = 'pharmacy_coupon_usages';

    protected $fillable = ['coupon_id', 'user_id', 'order_id', 'discount_amount', 'created_at'];

    protected $casts = [
        'discount_amount' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(PharmacyCoupon::class, 'coupon_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(PharmacyOrder::class, 'order_id');
    }
}
