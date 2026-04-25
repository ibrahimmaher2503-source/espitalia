<?php

namespace Modules\Branch\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\HasSlug;
use Spatie\Translatable\HasTranslations;

class Branch extends BaseModel
{
    use HasFactory, HasSlug, HasTranslations;

    protected $table = 'branches';

    public array $translatable = ['name', 'description'];

    const CUSTOM_FIELD_MODEL = 'Modules\Branch\Models\Branch';

    const TYPE_CLINIC = 'clinic';
    const TYPE_PHARMACY = 'pharmacy';
    const TYPE_LAB = 'lab';
    const TYPE_RADIOLOGY_CENTER = 'radiology_center';
    const TYPE_HOSPITAL = 'hospital';
    const TYPE_MULTI = 'multi';

    const TYPES = [
        self::TYPE_CLINIC,
        self::TYPE_PHARMACY,
        self::TYPE_LAB,
        self::TYPE_RADIOLOGY_CENTER,
        self::TYPE_HOSPITAL,
        self::TYPE_MULTI,
    ];

    protected $fillable = [
        'name',
        'slug',
        'vendor_id',
        'type',
        'email',
        'phone',
        'phone_secondary',
        'address',
        'governorate_id',
        'city_id',
        'latitude',
        'longitude',
        'description',
        'operating_hours',
        'is_active',
        'manager_id',
        'license_number',
        'tax_number',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'vendor_id' => 'integer',
        'manager_id' => 'integer',
        'governorate_id' => 'integer',
        'city_id' => 'integer',
        'is_active' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'operating_hours' => 'array',
    ];

    protected $appends = ['file_url'];

    // Relationships
    public function vendor()
    {
        return $this->belongsTo(\App\Models\User::class, 'vendor_id');
    }

    public function manager()
    {
        return $this->belongsTo(\App\Models\User::class, 'manager_id');
    }

    public function governorate()
    {
        return $this->belongsTo(\Modules\Location\Models\Governorate::class, 'governorate_id');
    }

    public function governorateCity()
    {
        return $this->belongsTo(\Modules\Location\Models\GovernorateCity::class, 'city_id');
    }

    public function clinics()
    {
        return $this->hasMany(\Modules\Clinic\Models\Clinics::class, 'branch_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function scopeByVendor($query, $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeSetRole($query, $user = null)
    {
        $user = $user ?? auth()->user();

        if ($user->hasRole('admin') || $user->hasRole('demo_admin')) {
            return $query;
        }

        if ($user->hasRole('vendor')) {
            return $query->where('vendor_id', $user->id);
        }

        return $query->where('id', 0); // no access for other roles
    }

    // Accessors
    public function getFileUrlAttribute()
    {
        $media = $this->getFirstMediaUrl('branch_image');
        return $media ?: default_file_url();
    }

    public function getStatusLabelAttribute()
    {
        return $this->is_active
            ? '<span class="badge bg-success">Active</span>'
            : '<span class="badge bg-danger">Inactive</span>';
    }

    public function getTypeLabelAttribute()
    {
        $labels = [
            'clinic' => 'Clinic',
            'pharmacy' => 'Pharmacy',
            'lab' => 'Laboratory',
            'radiology_center' => 'Radiology Center',
            'hospital' => 'Hospital',
            'multi' => 'Multi-Service',
        ];
        return $labels[$this->type] ?? ucfirst($this->type);
    }

    // Media
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('branch_image')
            ->singleFile();
    }
}
