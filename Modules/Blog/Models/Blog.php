<?php

namespace Modules\Blog\Models;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Blog extends BaseModel
{
    // use HasFactory;
    use HasFactory;
    use SoftDeletes;
  

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title', 'title_ar', 'title_en',
        'description', 'description_ar', 'description_en',
        'total_views', 'author_id', 'status', 'is_featured', 'tags',
    ];

    public function getTitleAttribute()
    {
        $locale = app()->getLocale();
        $localized = $locale === 'ar' ? $this->title_ar : $this->title_en;
        return $localized ?: ($locale === 'ar' ? $this->title_en : $this->title_ar) ?: '';
    }

    public function getDescriptionAttribute()
    {
        $locale = app()->getLocale();
        $localized = $locale === 'ar' ? $this->description_ar : $this->description_en;
        return $localized ?: ($locale === 'ar' ? $this->description_en : $this->description_ar) ?: '';
    }

    public function author(){
        return $this->belongsTo('App\Models\User','author_id','id')->withTrashed();
    }

}
