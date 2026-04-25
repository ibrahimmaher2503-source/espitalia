<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserProfile extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['about_self', 'expert', 'facebook_link', 'instagram_link', 'twitter_link', 'dribbble_link', 'expert_ar', 'expert_en', 'about_self_ar', 'about_self_en'];

    public function getExpertAttribute()
    {
        $locale = app()->getLocale();
        $localized = $locale === 'ar' ? $this->expert_ar : $this->expert_en;

        return $localized ?: ($locale === 'ar' ? $this->expert_en : $this->expert_ar) ?: $this->attributes['expert'] ?? '';
    }

    public function getAboutSelfAttribute()
    {
        $locale = app()->getLocale();
        $localized = $locale === 'ar' ? $this->about_self_ar : $this->about_self_en;

        return $localized ?: ($locale === 'ar' ? $this->about_self_en : $this->about_self_ar) ?: $this->attributes['about_self'] ?? '';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
