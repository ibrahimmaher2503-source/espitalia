<?php

namespace App\Models\Traits;

use Illuminate\Support\Str;

trait HasSlug
{
    public static function bootHasSlug()
    {
        static::saving(function ($model) {
            if ($model->isDirty('name') && \Schema::hasColumn($model->getTable(), 'slug')) {
                $name = $model->name;

                // For translatable models, get the English name for slug
                if (method_exists($model, 'getTranslatableAttributes') && in_array('name', $model->getTranslatableAttributes())) {
                    $name = $model->getTranslation('name', 'en', false) ?? '';
                } elseif (is_array($name)) {
                    $name = $name['en'] ?? reset($name) ?? '';
                }

                if (!empty($name)) {
                    $model->attributes['slug'] = Str::slug($name);
                }
            }
        });
    }
}
