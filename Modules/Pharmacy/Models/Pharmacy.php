<?php

namespace Modules\Pharmacy\Models;

use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pharmacy extends BaseModel
{
    protected $table = 'pharmacies';

    protected $fillable = [
        'name', 'name_ar', 'name_en', 'description_ar', 'description_en',
        'phone', 'email', 'phone_secondary', 'address', 'latitude', 'longitude',
        'delivery_fee', 'minimum_order_amount', 'max_delivery_radius_km',
        'is_active', 'is_delivery_enabled', 'is_pickup_enabled', 'avg_preparation_time_min',
        'rating', 'total_orders', 'tax_number',
        'commission_id', 'vendor_id', 'branch_id', 'governorate_id', 'city_id',
        'license_number', 'operating_hours',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_delivery_enabled' => 'boolean',
        'is_pickup_enabled' => 'boolean',
        'delivery_fee' => 'decimal:2',
        'minimum_order_amount' => 'decimal:2',
        'max_delivery_radius_km' => 'decimal:1',
        'rating' => 'decimal:1',
        'total_orders' => 'integer',
        'avg_preparation_time_min' => 'integer',
        'latitude' => 'double',
        'longitude' => 'double',
        'vendor_id' => 'integer',
        'branch_id' => 'integer',
        'governorate_id' => 'integer',
        'city_id' => 'integer',
        'operating_hours' => 'array',
    ];

    // Bilingual Accessors
    public function getNameAttribute($value)
    {
        if ($value) {
            return $value;
        }
        $locale = app()->getLocale();
        $localized = $locale === 'ar' ? $this->name_ar : $this->name_en;

        return $localized ?: ($locale === 'ar' ? $this->name_en : $this->name_ar) ?: '';
    }

    public function getDescriptionAttribute()
    {
        $locale = app()->getLocale();
        $localized = $locale === 'ar' ? $this->description_ar : $this->description_en;

        return $localized ?: ($locale === 'ar' ? $this->description_en : $this->description_ar) ?: '';
    }

    // Relationships
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function governorate(): BelongsTo
    {
        return $this->belongsTo(\Modules\Location\Models\Governorate::class);
    }

    public function governorateCity(): BelongsTo
    {
        return $this->belongsTo(\Modules\Location\Models\GovernorateCity::class, 'city_id');
    }

    public function inventory(): HasMany
    {
        return $this->hasMany(PharmacyInventory::class, 'pharmacy_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(PharmacyOrder::class, 'pharmacy_id');
    }

    /**
     * Users assigned to this pharmacy via users.pharmacy_id (for middleware/auth).
     * For actual staff management, use pharmacyStaff().
     */
    public function assignedUsers(): HasMany
    {
        return $this->hasMany(User::class, 'pharmacy_id');
    }

    public function commission(): BelongsTo
    {
        return $this->belongsTo(PharmacyCommission::class, 'commission_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(\Modules\Branch\Models\Branch::class, 'branch_id');
    }

    public function pharmacyStaff(): HasMany
    {
        return $this->hasMany(PharmacyStaff::class, 'pharmacy_id');
    }

    public function deliveryZones(): HasMany
    {
        return $this->hasMany(PharmacyDeliveryZone::class, 'pharmacy_id');
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(PharmacyPrescription::class, 'pharmacy_id');
    }

    public function coupons(): HasMany
    {
        return $this->hasMany(PharmacyCoupon::class, 'pharmacy_id');
    }
}
