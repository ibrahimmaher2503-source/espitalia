<?php

namespace Modules\Page\Models;

use App\Models\BaseModel;
use App\Models\Traits\HasSlug;
use App\Trait\CustomFieldsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends BaseModel
{
    use HasFactory;
    use SoftDeletes;
    use HasSlug;
    use CustomFieldsTrait;

    protected $table = 'pages';

    protected $fillable = ['slug', 'name', 'name_ar', 'name_en', 'description', 'description_ar', 'description_en', 'sequence', 'status'];

    public function getNameAttribute()
    {
        $locale = app()->getLocale();
        $localized = $locale === 'ar' ? $this->name_ar : $this->name_en;
        return $localized ?: ($locale === 'ar' ? $this->name_en : $this->name_ar) ?: $this->attributes['name'] ?? '';
    }

    public function getDescriptionAttribute()
    {
        $locale = app()->getLocale();
        $localized = $locale === 'ar' ? $this->description_ar : $this->description_en;
        return $localized ?: ($locale === 'ar' ? $this->description_en : $this->description_ar) ?: $this->attributes['description'] ?? '';
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return \Modules\Page\database\factories\PageFactory::new();
    }
}
