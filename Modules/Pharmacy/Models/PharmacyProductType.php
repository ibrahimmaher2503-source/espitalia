<?php

namespace Modules\Pharmacy\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PharmacyProductType extends BaseModel
{
    protected $table = 'pharmacy_product_types';

    protected $fillable = ['name_ar', 'name_en'];

    // Relationships
    public function products(): HasMany
    {
        return $this->hasMany(PharmacyProduct::class, 'product_type_id');
    }

    // Accessors
    public function getNameAttribute()
    {
        $locale = app()->getLocale();

        return $locale === 'ar' ? $this->name_ar : $this->name_en;
    }
}
