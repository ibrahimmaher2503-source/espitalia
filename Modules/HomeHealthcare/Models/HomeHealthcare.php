<?php

namespace Modules\HomeHealthcare\Models;

use App\Models\BaseModel;
use Modules\Location\Models\Governorate;
use Modules\Location\Models\GovernorateCity;

class HomeHealthcare extends BaseModel
{
    protected $table = 'home_healthcares';

    protected $fillable = ['name_ar', 'name_en', 'service_type', 'governorate_id', 'city_id', 'status'];

    public function getNameAttribute()
    {
        return app()->getLocale() === 'ar' ? $this->name_ar : $this->name_en;
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
