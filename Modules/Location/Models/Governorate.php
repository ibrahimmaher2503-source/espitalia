<?php

namespace Modules\Location\Models;

use App\Models\BaseModel;

class Governorate extends BaseModel
{
    protected $table = 'governorates';

    protected $fillable = ['name_ar', 'name_en'];

    public function getNameAttribute()
    {
        return app()->getLocale() === 'ar' ? $this->name_ar : $this->name_en;
    }

    public function cities()
    {
        return $this->hasMany(GovernorateCity::class);
    }
}
