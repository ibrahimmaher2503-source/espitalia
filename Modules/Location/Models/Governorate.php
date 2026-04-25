<?php

namespace Modules\Location\Models;

use App\Models\BaseModel;

class Governorate extends BaseModel
{
    protected $table = 'governorates';

    protected $fillable = ['name_ar', 'name_en'];

    public function getNameAttribute()
    {
        $locale = app()->getLocale();
        $localized = $locale === 'ar' ? $this->name_ar : $this->name_en;

        return $localized ?: ($locale === 'ar' ? $this->name_en : $this->name_ar) ?: '';
    }

    public function cities()
    {
        return $this->hasMany(GovernorateCity::class);
    }
}
