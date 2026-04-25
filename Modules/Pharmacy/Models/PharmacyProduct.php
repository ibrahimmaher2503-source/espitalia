<?php

namespace Modules\Pharmacy\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PharmacyProduct extends BaseModel
{
    protected $table = 'pharmacy_products';

    protected $fillable = [
        'name_ar',
        'name_en',
        'category_id',
        'sub_category_id',
        'brand_id',
        'product_type_id',
        'description',
        'description_ar',
        'description_en',
        'requires_prescription',
        'is_active',
        'barcode',
        'sku',
        'default_price',
        'dosage_form',
        'strength',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'requires_prescription' => 'boolean',
        'default_price' => 'decimal:2',
    ];

    // Relationships
    public function category(): BelongsTo
    {
        return $this->belongsTo(PharmacyCategory::class, 'category_id');
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(PharmacyCategory::class, 'sub_category_id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(PharmacyBrand::class, 'brand_id');
    }

    public function productType(): BelongsTo
    {
        return $this->belongsTo(PharmacyProductType::class, 'product_type_id');
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(PharmacyInventory::class, 'product_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(PharmacyOrderItem::class, 'product_id');
    }

    // Accessors
    public function getNameAttribute()
    {
        $locale = app()->getLocale();
        $localized = $locale === 'ar' ? $this->name_ar : $this->name_en;

        return $localized ?: ($locale === 'ar' ? $this->name_en : $this->name_ar) ?: '';
    }

    public function getDescriptionAttribute($value)
    {
        if ($value) {
            return $value;
        }
        $locale = app()->getLocale();
        $localized = $locale === 'ar' ? $this->description_ar : $this->description_en;

        return $localized ?: ($locale === 'ar' ? $this->description_en : $this->description_ar) ?: '';
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock($query)
    {
        return $query->join('pharmacy_inventory', 'pharmacy_products.id', '=', 'pharmacy_inventory.product_id')
            ->where('pharmacy_inventory.stock_qty', '>', 0)
            ->where('pharmacy_inventory.is_available', true)
            ->distinct('pharmacy_products.id');
    }
}
