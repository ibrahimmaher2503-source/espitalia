<?php

namespace Modules\Pharmacy\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class PharmacyCategoryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->getNameAttribute(),
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'image' => filter_var($this->getFirstMediaUrl('category_image'), FILTER_VALIDATE_URL) ?: null,
            'sort_order' => $this->sort_order,
        ];
    }
}
