<?php

namespace Modules\HomeHealthcare\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class HomeHealthcareResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'service_type' => $this->service_type,
            'governorate' => [
                'id' => optional($this->governorate)->id,
                'name' => optional($this->governorate)->name,
            ],
            'city' => [
                'id' => optional($this->city)->id,
                'name' => optional($this->city)->name,
            ],
        ];
    }
}
