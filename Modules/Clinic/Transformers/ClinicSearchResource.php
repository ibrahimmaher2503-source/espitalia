<?php

namespace Modules\Clinic\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class ClinicSearchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'profile_image' => $this->file_url,
            'governorate' => $this->whenLoaded('governorate', function () {
                return [
                    'id' => $this->governorate->id,
                    'name' => $this->governorate->name,
                ];
            }),
            'city' => $this->whenLoaded('governorateCity', function () {
                return [
                    'id' => $this->governorateCity->id,
                    'name' => $this->governorateCity->name,
                ];
            }),
        ];
    }
}
