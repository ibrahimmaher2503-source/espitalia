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
