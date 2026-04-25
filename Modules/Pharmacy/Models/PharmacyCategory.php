<?php

namespace Modules\Pharmacy\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PharmacyCategory extends BaseModel
{
    protected $table = 'pharmacy_categories';

    protected $fillable = ['parent_id', 'name_ar', 'name_en', 'is_active', 'sort_order'];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Relationships
    public function parent(): BelongsTo
    {
        return $this->belongsTo(PharmacyCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(PharmacyCategory::class, 'parent_id')->orderBy('sort_order');
    }

    public function products(): HasMany
    {
        return $this->hasMany(PharmacyProduct::class, 'category_id');
    }

    public function subcategoryProducts(): HasMany
    {
        return $this->hasMany(PharmacyProduct::class, 'sub_category_id');
    }

    // Accessors
    public function getNameAttribute()
    {
        $locale = app()->getLocale();

        return $locale === 'ar' ? $this->name_ar : $this->name_en;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
