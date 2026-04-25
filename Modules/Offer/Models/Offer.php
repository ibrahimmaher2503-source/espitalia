<?php

namespace Modules\Offer\Models;

use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Offer extends BaseModel
{
    use HasFactory;

    protected $table = 'offers';

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_ACTIVE = 'active';
    const STATUS_PAUSED = 'paused';
    const STATUS_EXPIRED = 'expired';
    const STATUS_EXHAUSTED = 'exhausted';

    const STATUSES = [
        self::STATUS_DRAFT, self::STATUS_SCHEDULED, self::STATUS_ACTIVE,
        self::STATUS_PAUSED, self::STATUS_EXPIRED, self::STATUS_EXHAUSTED,
    ];

    // Discount type constants
    const TYPE_PERCENTAGE = 'percentage';
    const TYPE_FIXED_AMOUNT = 'fixed_amount';
    const TYPE_OVERRIDE_PRICE = 'override_price';
    const TYPE_BUNDLE_PRICE = 'bundle_price';
    const TYPE_BUY_X_GET_Y = 'buy_x_get_y';
    const TYPE_FREE_ADDON = 'free_addon';

    const DISCOUNT_TYPES = [
        self::TYPE_PERCENTAGE, self::TYPE_FIXED_AMOUNT, self::TYPE_OVERRIDE_PRICE,
        self::TYPE_BUNDLE_PRICE, self::TYPE_BUY_X_GET_Y, self::TYPE_FREE_ADDON,
    ];

    // Customer scope constants
    const SCOPE_ALL = 'all';
    const SCOPE_FIRST_TIME = 'first_time';
    const SCOPE_RETURNING = 'returning';
    const SCOPE_SEGMENT = 'segment';

    protected $fillable = [
        // Identity
        'title_ar', 'title_en', 'description_ar', 'description_en',
        'short_label_ar', 'short_label_en', 'terms_ar', 'terms_en',
        'banner_text_ar', 'banner_text_en', 'slug', 'internal_name',
        // Discount config
        'discount_type', 'discount_value', 'max_discount_amount', 'minimum_order_amount',
        'override_price', 'buy_quantity', 'get_quantity', 'get_service_id', 'get_discount_percentage',
        // Coupon
        'code', 'requires_coupon', 'auto_apply',
        // Targeting
        'target_scope', 'applies_to_all_services', 'applies_to_all_branches',
        'applies_to_all_categories', 'vendor_id',
        // Customer eligibility
        'customer_scope', 'first_booking_only', 'min_completed_bookings', 'max_completed_bookings',
        // Scheduling
        'starts_at', 'ends_at', 'is_flash',
        // Usage limits
        'usage_limit_total', 'usage_limit_per_user', 'used_count',
        // Priority & stacking
        'priority', 'stackable', 'exclusive_group',
        // Display
        'is_active', 'is_featured', 'status',
        // Channel
        'allowed_channels', 'allowed_payment_methods',
        // Commission
        'commission_on_original',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'minimum_order_amount' => 'decimal:2',
        'override_price' => 'decimal:2',
        'get_discount_percentage' => 'decimal:2',
        'buy_quantity' => 'integer',
        'get_quantity' => 'integer',
        'requires_coupon' => 'boolean',
        'auto_apply' => 'boolean',
        'applies_to_all_services' => 'boolean',
        'applies_to_all_branches' => 'boolean',
        'applies_to_all_categories' => 'boolean',
        'first_booking_only' => 'boolean',
        'min_completed_bookings' => 'integer',
        'max_completed_bookings' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_flash' => 'boolean',
        'usage_limit_total' => 'integer',
        'usage_limit_per_user' => 'integer',
        'used_count' => 'integer',
        'priority' => 'integer',
        'stackable' => 'boolean',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'allowed_channels' => 'array',
        'allowed_payment_methods' => 'array',
        'commission_on_original' => 'boolean',
    ];

    // --- Relationships ---

    public function services(): HasMany
    {
        return $this->hasMany(OfferService::class);
    }

    public function branches(): HasMany
    {
        return $this->hasMany(OfferBranch::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(OfferCategory::class);
    }

    public function customerSegments(): HasMany
    {
        return $this->hasMany(OfferCustomerSegment::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(OfferSchedule::class);
    }

    public function usages(): HasMany
    {
        return $this->hasMany(OfferUsage::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(OfferAuditLog::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    // --- Bilingual Accessors ---

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

    public function getShortLabelAttribute()
    {
        $locale = app()->getLocale();
        $localized = $locale === 'ar' ? $this->short_label_ar : $this->short_label_en;

        return $localized ?: ($locale === 'ar' ? $this->short_label_en : $this->short_label_ar) ?: '';
    }

    public function getTermsAttribute()
    {
        $locale = app()->getLocale();
        $localized = $locale === 'ar' ? $this->terms_ar : $this->terms_en;

        return $localized ?: ($locale === 'ar' ? $this->terms_en : $this->terms_ar) ?: '';
    }

    public function getBannerTextAttribute()
    {
        $locale = app()->getLocale();
        $localized = $locale === 'ar' ? $this->banner_text_ar : $this->banner_text_en;

        return $localized ?: ($locale === 'ar' ? $this->banner_text_en : $this->banner_text_ar) ?: '';
    }

    // --- Scopes ---

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('status', self::STATUS_ACTIVE);
    }

    public function scopeAutoApply($query)
    {
        return $query->where('auto_apply', true)->where('requires_coupon', false);
    }

    public function scopeForVendor($query, $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    public function scopeValidNow($query)
    {
        $now = now();

        return $query->where(function ($q) use ($now) {
            $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
        })->where(function ($q) use ($now) {
            $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
        });
    }

    public function scopePlatformWide($query)
    {
        return $query->whereNull('vendor_id');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Get active, currently valid offers for a specific service.
     * Returns a lightweight collection for display in service/doctor listings.
     */
    public static function getActiveOffersForService(int $serviceId): array
    {
        $offers = static::active()
            ->validNow()
            ->where(function ($q) use ($serviceId) {
                $q->where('applies_to_all_services', true)
                    ->orWhereHas('services', function ($sq) use ($serviceId) {
                        $sq->where('service_id', $serviceId);
                    });
            })
            ->select('id', 'title_ar', 'title_en', 'discount_type', 'discount_value', 'code', 'requires_coupon', 'auto_apply', 'short_label_ar', 'short_label_en', 'banner_text_ar', 'banner_text_en', 'ends_at')
            ->orderBy('priority', 'desc')
            ->limit(3)
            ->get();

        if ($offers->isEmpty()) {
            return [];
        }

        $locale = app()->getLocale();

        return $offers->map(function ($offer) use ($locale) {
            $title = $locale === 'ar'
                ? ($offer->title_ar ?: $offer->title_en)
                : ($offer->title_en ?: $offer->title_ar);

            $label = $locale === 'ar'
                ? ($offer->short_label_ar ?: $offer->short_label_en)
                : ($offer->short_label_en ?: $offer->short_label_ar);

            return [
                'id' => $offer->id,
                'title' => $title ?: '',
                'label' => $label ?: '',
                'discount_type' => $offer->discount_type,
                'discount_value' => $offer->discount_value,
                'code' => $offer->requires_coupon ? $offer->code : null,
                'auto_apply' => (bool) $offer->auto_apply,
                'ends_at' => $offer->ends_at?->toIso8601String(),
            ];
        })->all();
    }
}
