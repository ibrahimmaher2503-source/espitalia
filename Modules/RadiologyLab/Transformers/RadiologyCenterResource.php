<?php

namespace Modules\RadiologyLab\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class RadiologyCenterResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'scan_type' => $this->scan_type,
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
