<?php

namespace Modules\FAQ\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\FAQ\Database\factories\FaqsFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class faqs extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['question', 'question_ar', 'question_en', 'answer', 'answer_ar', 'answer_en', 'status'];

    public function getQuestionAttribute()
    {
        $locale = app()->getLocale();
        $localized = $locale === 'ar' ? $this->question_ar : $this->question_en;
        return $localized ?: ($locale === 'ar' ? $this->question_en : $this->question_ar) ?: '';
    }

    public function getAnswerAttribute()
    {
        $locale = app()->getLocale();
        $localized = $locale === 'ar' ? $this->answer_ar : $this->answer_en;
        return $localized ?: ($locale === 'ar' ? $this->answer_en : $this->answer_ar) ?: '';
    }

    protected static function newFactory(): FaqsFactory
    {
        return \Modules\FAQ\database\factories\FAQFactory::new();
    }
}
