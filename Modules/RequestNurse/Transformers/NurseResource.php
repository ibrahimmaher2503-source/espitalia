<?php

namespace Modules\RequestNurse\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class NurseResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'nurse_id' => $this->nurse_id,
            'name' => optional($this->user)->first_name ? optional($this->user)->first_name.' '.optional($this->user)->last_name : '',
            'first_name' => optional($this->user)->first_name ?? '',
            'last_name' => optional($this->user)->last_name ?? '',
            'email' => optional($this->user)->email ?? '',
            'mobile' => optional($this->user)->mobile ?? '',
            'specialization' => $this->specialization,
            'specialization_ar' => $this->specialization_ar,
            'specialization_en' => $this->specialization_en,
            'experience' => $this->experience,
            'experience_ar' => $this->experience_ar,
            'experience_en' => $this->experience_en,
            'about' => $this->about,
            'about_ar' => $this->about_ar,
            'about_en' => $this->about_en,
            'hourly_rate' => $this->hourly_rate,
            'availability_status' => $this->availability_status,
            'service_area' => $this->service_area,
            'service_area_ar' => $this->service_area_ar,
            'service_area_en' => $this->service_area_en,
            'governorate' => $this->governorate ? ['id' => $this->governorate->id, 'name' => $this->governorate->name] : null,
            'city' => $this->city ? ['id' => $this->city->id, 'name' => $this->city->name] : null,
            'profile_image' => $this->getFirstMediaUrl('profile_image') ?: null,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
