<?php

namespace Modules\IntensiveCare\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IcuDepartment extends BaseModel
{
    use HasFactory;

    protected $table = 'icu_departments';

    protected $fillable = [
        'hospital_id',
        'name_ar',
        'name_en',
        'specialty_type',
        'total_beds',
        'available_beds',
        'has_ventilator',
        'has_oxygen',
        'equipment_level',
        'daily_price',
        'description_ar',
        'description_en',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'has_ventilator' => 'boolean',
        'has_oxygen' => 'boolean',
        'daily_price' => 'decimal:2',
        'total_beds' => 'integer',
        'available_beds' => 'integer',
        'status' => 'boolean',
    ];

    // ── Constants ────────────────────────────────────────────

    const SPECIALTY_TYPES = ['cardiac', 'neurology', 'pediatric', 'neonatal', 'burns', 'chest', 'surgical', 'general'];

    const EQUIPMENT_LEVELS = ['basic', 'advanced', 'full'];

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

    // ── Relationships ────────────────────────────────────────

    public function hospital()
    {
        return $this->belongsTo(Hospital::class, 'hospital_id');
    }

    public function admissionRequests()
    {
        return $this->hasMany(IcuAdmissionRequest::class, 'icu_department_id');
    }

    // ── Scopes ───────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeAvailable($query)
    {
        return $query->where('available_beds', '>', 0);
    }

    public function scopeWithVentilator($query)
    {
        return $query->where('has_ventilator', true);
    }

    public function scopeWithOxygen($query)
    {
        return $query->where('has_oxygen', true);
    }

    public function scopeBySpecialty($query, $type)
    {
        return $query->where('specialty_type', $type);
    }

    protected static function newFactory()
    {
        return \Modules\IntensiveCare\Database\Factories\IcuDepartmentFactory::new();
    }
}
