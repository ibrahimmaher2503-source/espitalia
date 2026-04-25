<?php

namespace Modules\RadiologyLab\Models;

use App\Models\BaseModel;
use App\Models\Traits\HasSlug;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class LabTestCategory extends BaseModel
{
    use HasFactory, HasSlug, HasTranslations;

    public array $translatable = ['name', 'description'];

    protected $table = 'lab_test_categories';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'display_order',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'status' => 'boolean',
        'display_order' => 'integer',
    ];

    // Relationships
    public function labTests()
    {
        return $this->hasMany(LabTest::class, 'category_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order', 'asc');
    }

    protected static function newFactory()
    {
        return \Modules\RadiologyLab\Database\Factories\LabTestCategoryFactory::new();
    }
}
