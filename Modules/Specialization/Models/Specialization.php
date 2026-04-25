<?php

namespace Modules\Specialization\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Modules\Clinic\Models\Doctor;
use Modules\RequestNurse\Models\Nurse;

class Specialization extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'name_ar',
        'name_en',
        'description_ar',
        'description_en',
        'slug',
        'parent_id',
        'icon',
        'icon_image',
        'status',
        'show_on_home',
        'display_order',
    ];

    protected $casts = [
        'show_on_home' => 'boolean',
        'display_order' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->isDirty('name_en') && ! empty($model->name_en)) {
                $model->slug = Str::slug($model->name_en);
            }
        });
    }

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

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function doctors()
    {
        return $this->belongsToMany(Doctor::class, 'doctor_specialization')->withTimestamps();
    }

    public function nurses()
    {
        return $this->belongsToMany(Nurse::class, 'nurse_specialization')->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }
}
