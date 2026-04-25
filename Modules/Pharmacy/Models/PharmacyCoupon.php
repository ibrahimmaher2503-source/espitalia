<?php

namespace Modules\Pharmacy\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PharmacyCoupon extends BaseModel
{
    protected $table = 'pharmacy_coupons';

    protected $fillable = [
        'pharmacy_id', 'code', 'title_ar', 'title_en',
        'description_ar', 'description_en', 'discount_type', 'discount_value',
        'max_discount_amount', 'min_order_amount', 'usage_limit', 'usage_per_user',
        'used_count', 'starts_at', 'expires_at', 'is_active',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'usage_limit' => 'integer',
        'usage_per_user' => 'integer',
        'used_count' => 'integer',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    const TYPE_PERCENTAGE = 'percentage';
    const TYPE_FIXED = 'fixed';

    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class, 'pharmacy_id');
    }

    public function usages(): HasMany
    {
        return $this->hasMany(PharmacyCouponUsage::class, 'coupon_id');
    }

    public function getTitleAttribute()
    {
        $locale = app()->getLocale();
        $localized = $locale === 'ar' ? $this->title_ar : $this->title_en;

        return $localized ?: ($locale === 'ar' ? $this->title_en : $this->title_ar) ?: '';
    }

    public function getDescriptionAttribute()
    {
        $locale = app()->getLocale();
        $localized = $locale === 'ar' ? $this->description_ar : $this->description_en;

        return $localized ?: ($locale === 'ar' ? $this->description_en : $this->description_ar) ?: '';
    }

    public function isValidForUser(int $userId): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return false;
        }

        // usage_per_user: null = unlimited, 0 = blocked (cast to int makes null → 0)
        $perUserLimit = $this->getAttributes()['usage_per_user'] ?? null; // raw value, bypassing int cast
        if ($perUserLimit !== null && $perUserLimit > 0) {
            $userUsageCount = $this->usages()->where('user_id', $userId)->count();
            if ($userUsageCount >= $perUserLimit) {
                return false;
            }
        }

        return true;
    }

    public function calculateDiscount(float $subtotal): float
    {
        if ($subtotal < $this->min_order_amount) {
            return 0;
        }

        if ($this->discount_type === self::TYPE_PERCENTAGE) {
            $discount = $subtotal * ($this->discount_value / 100);
            if ($this->max_discount_amount) {
                $discount = min($discount, (float) $this->max_discount_amount);
            }
        } else {
            $discount = (float) $this->discount_value;
        }

        return min($discount, $subtotal);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeValid($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->where(function ($q) {
                $q->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit');
            });
    }

    public function scopeForPharmacy($query, $pharmacyId)
    {
        return $query->where('pharmacy_id', $pharmacyId);
    }

    public function scopePlatformWide($query)
    {
        return $query->whereNull('pharmacy_id');
    }
}
