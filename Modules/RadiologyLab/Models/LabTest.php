<?php

namespace Modules\RadiologyLab\Models;

use App\Models\BaseModel;
use App\Models\Traits\HasSlug;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class LabTest extends BaseModel
{
    use HasFactory, HasSlug, HasTranslations;

    public array $translatable = ['name', 'description', 'preparation_instructions'];

    protected $table = 'lab_tests';

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'code',
        'description',
        'sample_type',
        'department',
        'preparation_instructions',
        'default_price',
        'turnaround_time',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'status' => 'boolean',
        'default_price' => 'decimal:2',
    ];

    const SAMPLE_TYPES = ['blood', 'urine', 'stool', 'tissue', 'imaging', 'swab', 'other'];
    const DEPARTMENTS = ['laboratory', 'radiology'];

    // Relationships
    public function category()
    {
        return $this->belongsTo(LabTestCategory::class, 'category_id');
    }

    public function orderItems()
    {
        return $this->hasMany(TestOrderItem::class, 'lab_test_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeLaboratory($query)
    {
        return $query->where('department', 'laboratory');
    }

    public function scopeRadiology($query)
    {
        return $query->where('department', 'radiology');
    }

    // Accessors
    public function getFormattedPriceAttribute()
    {
        return number_format($this->default_price, 2);
    }

    public function getDepartmentBadgeAttribute()
    {
        return match ($this->department) {
            'laboratory' => '<span class="badge bg-info">' . __('radiology_lab.dept_laboratory') . '</span>',
            'radiology' => '<span class="badge bg-warning text-dark">' . __('radiology_lab.dept_radiology') . '</span>',
            default => '<span class="badge bg-secondary">' . $this->department . '</span>',
        };
    }

    public function getSampleTypeLabelAttribute()
    {
        return match ($this->sample_type) {
            'blood' => __('radiology_lab.sample_blood'),
            'urine' => __('radiology_lab.sample_urine'),
            'stool' => __('radiology_lab.sample_stool'),
            'tissue' => __('radiology_lab.sample_tissue'),
            'imaging' => __('radiology_lab.sample_imaging'),
            'swab' => __('radiology_lab.sample_swab'),
            'other' => __('radiology_lab.sample_other'),
            default => $this->sample_type ?? '-',
        };
    }

    protected static function newFactory()
    {
        return \Modules\RadiologyLab\Database\Factories\LabTestFactory::new();
    }
}
