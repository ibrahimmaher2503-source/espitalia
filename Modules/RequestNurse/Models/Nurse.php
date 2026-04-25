<?php

namespace Modules\RequestNurse\Models;

use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Nurse extends BaseModel
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'nurses';

    protected $fillable = [
        'nurse_id',
        'vendor_id',
        'governorate_id',
        'city_id',
        'specialization_ar',
        'specialization_en',
        'experience_ar',
        'experience_en',
        'about_ar',
        'about_en',
        'availability_status',
        'service_area_ar',
        'service_area_en',
        'hourly_rate',
        'status',
    ];

    protected $casts = [
        'hourly_rate' => 'decimal:2',
        'status' => 'integer',
    ];

    const CUSTOM_FIELD_MODEL = 'Modules\RequestNurse\Models\Nurse';

    protected static function newFactory()
    {
        return \Modules\RequestNurse\Database\Factories\NurseFactory::new();
    }

    // ── Relationships ──────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class, 'nurse_id', 'id');
    }

    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id', 'id');
    }

    public function governorate()
    {
        return $this->belongsTo(\Modules\Location\Models\Governorate::class);
    }

    public function city()
    {
        return $this->belongsTo(\Modules\Location\Models\GovernorateCity::class, 'city_id');
    }

    public function nurseRequests()
    {
        return $this->hasMany(NurseRequest::class, 'nurse_id', 'nurse_id');
    }

    // ── Accessors ──────────────────────────────────────────────

    public function getFullNameAttribute()
    {
        return $this->user ? $this->user->full_name : '';
    }

    public function getSpecializationAttribute()
    {
        $locale = app()->getLocale();
        $localized = $locale === 'ar' ? $this->specialization_ar : $this->specialization_en;

        return $localized ?: ($locale === 'ar' ? $this->specialization_en : $this->specialization_ar) ?: '';
    }

    public function getExperienceAttribute()
    {
        $locale = app()->getLocale();
        $localized = $locale === 'ar' ? $this->experience_ar : $this->experience_en;

        return $localized ?: ($locale === 'ar' ? $this->experience_en : $this->experience_ar) ?: '';
    }

    public function getAboutAttribute()
    {
        $locale = app()->getLocale();
        $localized = $locale === 'ar' ? $this->about_ar : $this->about_en;

        return $localized ?: ($locale === 'ar' ? $this->about_en : $this->about_ar) ?: '';
    }

    public function getServiceAreaAttribute()
    {
        $locale = app()->getLocale();
        $localized = $locale === 'ar' ? $this->service_area_ar : $this->service_area_en;

        return $localized ?: ($locale === 'ar' ? $this->service_area_en : $this->service_area_ar) ?: '';
    }

    // ── Scopes ─────────────────────────────────────────────────

    public function scopeAvailable($query)
    {
        return $query->where('availability_status', 'available')->where('status', 1);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function specializations()
    {
        return $this->belongsToMany(\Modules\Specialization\Models\Specialization::class, 'nurse_specialization')->withTimestamps();
    }
}
