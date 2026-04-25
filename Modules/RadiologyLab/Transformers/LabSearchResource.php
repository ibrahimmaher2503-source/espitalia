<?php

namespace Modules\RadiologyLab\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class LabSearchResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'governorate' => $this->whenLoaded('governorate', function () {
                return [
                    'id' => $this->governorate->id,
                    'name' => $this->governorate->name,
                ];
            }),
            'city' => $this->whenLoaded('city', function () {
                return [
                    'id' => $this->city->id,
                    'name' => $this->city->name,
                ];
            }),
        ];
    }
}
