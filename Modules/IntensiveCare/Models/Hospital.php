<?php

namespace Modules\IntensiveCare\Models;

use App\Models\BaseModel;
use App\Models\Traits\HasSlug;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Location\Models\Governorate;
use Modules\Location\Models\GovernorateCity;

class Hospital extends BaseModel
{
    use HasFactory, HasSlug;

    protected $table = 'hospitals';

    protected $fillable = [
        'slug',
        'name_ar',
        'name_en',
        'description_ar',
        'description_en',
        'hospital_type',
        'address_ar',
        'address_en',
        'governorate_id',
        'city_id',
        'city',
        'state',
        'country',
        'postal_code',
        'latitude',
        'longitude',
        'phone',
        'email',
        'website',
        'license_number',
        'accepted_insurance',
        'rating',
        'commission_id',
        'vendor_id',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'accepted_insurance' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'rating' => 'decimal:2',
        'status' => 'boolean',
    ];

    // ── Constants ────────────────────────────────────────────

    const HOSPITAL_TYPES = ['government', 'private', 'military', 'university'];

    // ── Bilingual Accessors ───────────────────────────────────

    public function getNameAttribute()
    {
        $locale = app()->getLocale();
        $localized = $locale === 'ar' ? $this->name_ar : $this->name_en;

        return $localized ?: ($locale === 'ar' ? $this->name_en : $this->name_ar) ?: '';
    }

    public function getDescriptionAttribute()
    {
        $locale = app()->getLocale();
        $localized = $locale === 'ar' ? $this->description_ar : $this->description_en;

        return $localized ?: ($locale === 'ar' ? $this->description_en : $this->description_ar) ?: '';
    }

    public function getAddressAttribute()
    {
        $locale = app()->getLocale();
        $localized = $locale === 'ar' ? $this->address_ar : $this->address_en;

        return $localized ?: ($locale === 'ar' ? $this->address_en : $this->address_ar) ?: '';
    }

    // ── Relationships ────────────────────────────────────────

    public function icuDepartments()
    {
        return $this->hasMany(IcuDepartment::class, 'hospital_id');
    }

    public function admissionRequests()
    {
        return $this->hasMany(IcuAdmissionRequest::class, 'hospital_id');
    }

    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function governorate()
    {
        return $this->belongsTo(Governorate::class);
    }

    public function governorateCity()
    {
        return $this->belongsTo(GovernorateCity::class, 'city_id');
    }

    public function commission()
    {
        return $this->belongsTo(IcuCommission::class, 'commission_id');
    }

    public function commissionEarnings()
    {
        return $this->hasMany(IcuCommissionEarning::class, 'hospital_id');
    }

    public function payouts()
    {
        return $this->hasMany(IcuPayout::class, 'hospital_id');
    }

    // ── Scopes ───────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeInCity($query, $city)
    {
        return $query->where('city', $city);
    }

    public function scopeHasInsurance($query, $provider)
    {
        return $query->whereJsonContains('accepted_insurance', $provider);
    }

    // ── Media ────────────────────────────────────────────────

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')->singleFile();
        $this->addMediaCollection('gallery');
    }

    protected static function newFactory()
    {
        return \Modules\IntensiveCare\Database\Factories\HospitalFactory::new();
    }
}
