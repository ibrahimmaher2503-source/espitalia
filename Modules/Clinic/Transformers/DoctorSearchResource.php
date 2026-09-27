<?php

namespace Modules\Clinic\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class DoctorSearchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => trim(optional($this->user)->first_name.' '.optional($this->user)->last_name),
            'first_name_ar' => optional($this->user)->first_name_ar,
            'first_name_en' => optional($this->user)->first_name_en,
            'last_name_ar' => optional($this->user)->last_name_ar,
            'last_name_en' => optional($this->user)->last_name_en,
            'gender' => optional($this->user)->gender,
            'experience' => $this->experience,
            'profile_image' => optional($this->user)->profile_image,
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
            'specialty' => $this->whenLoaded('doctorService', fn () => optional($this->doctorService->first())->clinicservice?->name),
            'consultation_price' => $this->whenLoaded('doctorService', fn () => optional($this->doctorService->first())->charges),
            'has_active_offer' => $this->whenLoaded('doctorService', function () {
                if (! class_exists(\Modules\Offer\Models\Offer::class)) {
                    return false;
                }
                $serviceId = optional($this->doctorService->first())->service_id;
                return $serviceId ? ! empty(\Modules\Offer\Models\Offer::getActiveOffersForService($serviceId)) : false;
            }),
        ];
    }
}
