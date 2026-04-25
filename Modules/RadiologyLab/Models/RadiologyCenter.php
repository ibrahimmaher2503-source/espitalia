<?php

namespace Modules\RadiologyLab\Models;

use App\Models\BaseModel;
use Modules\Location\Models\Governorate;
use Modules\Location\Models\GovernorateCity;

class RadiologyCenter extends BaseModel
{
    protected $table = 'radiology_centers';

    protected $fillable = [
        'name_ar',
        'name_en',
        'scan_type',
        'governorate_id',
        'city_id',
        'status',
    ];

    /**
     * Locale-aware name accessor.
     */
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

    public function city()
    {
        return $this->belongsTo(GovernorateCity::class, 'city_id');
    }
}
