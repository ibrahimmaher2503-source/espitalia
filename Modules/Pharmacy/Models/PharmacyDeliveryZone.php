<?php

namespace Modules\Pharmacy\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PharmacyDeliveryZone extends BaseModel
{
    protected $table = 'pharmacy_delivery_zones';

    protected $fillable = [
        'pharmacy_id', 'name_ar', 'name_en', 'governorate_id', 'city_id',
        'delivery_fee', 'min_order_amount', 'estimated_delivery_minutes',
        'is_active', 'sort_order',
    ];

    protected $casts = [
        'delivery_fee' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'estimated_delivery_minutes' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'governorate_id' => 'integer',
        'city_id' => 'integer',
    ];

    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class, 'pharmacy_id');
    }

    public function governorate(): BelongsTo
    {
        return $this->belongsTo(\Modules\Location\Models\Governorate::class);
    }

    public function governorateCity(): BelongsTo
    {
        return $this->belongsTo(\Modules\Location\Models\GovernorateCity::class, 'city_id');
    }

    public function getNameAttribute()
    {
        $locale = app()->getLocale();
        $localized = $locale === 'ar' ? $this->name_ar : $this->name_en;

        return $localized ?: ($locale === 'ar' ? $this->name_en : $this->name_ar) ?: '';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function scopeByPharmacy($query, $pharmacyId)
    {
        return $query->where('pharmacy_id', $pharmacyId);
    }

    public function scopeForGovernorate($query, $governorateId)
    {
        return $query->where('governorate_id', $governorateId);
    }
}
