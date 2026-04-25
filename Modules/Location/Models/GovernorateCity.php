<?php

namespace Modules\Location\Models;

use App\Models\BaseModel;

class GovernorateCity extends BaseModel
{
    protected $table = 'governorate_cities';

    protected $fillable = ['governorate_id', 'name_ar', 'name_en'];

    public function getNameAttribute()
    {
        $locale = app()->getLocale();
        $localized = $locale === 'ar' ? $this->name_ar : $this->name_en;

        return $localized ?: ($locale === 'ar' ? $this->name_en : $this->name_ar) ?: '';
    }

    public function governorate()
    {
        return $this->belongsTo(Governorate::class);
    }
}
