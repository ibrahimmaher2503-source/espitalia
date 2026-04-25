<?php

namespace Modules\RequestNurse\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class NurseSearchResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => optional($this->user)->first_name
                ? optional($this->user)->first_name.' '.optional($this->user)->last_name
                : '',
            'specialization' => $this->specialization,
            'gender' => optional($this->user)->gender,
            'availability_status' => $this->availability_status,
            'hourly_rate' => $this->hourly_rate,
            'profile_image' => optional($this->user)->profile_image,
            'governorate' => $this->governorate ? [
                'id' => $this->governorate->id,
                'name' => $this->governorate->name,
            ] : null,
            'city' => $this->city ? [
                'id' => $this->city->id,
                'name' => $this->city->name,
            ] : null,
        ];
    }
}
