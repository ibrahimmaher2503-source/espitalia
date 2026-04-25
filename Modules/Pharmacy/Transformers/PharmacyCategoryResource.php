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
            'image' => $this->getFirstMediaUrl('category_image'),
            'sort_order' => $this->sort_order,
        ];
    }
}
