<?php

namespace Modules\Branch\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'name_translations' => $this->getTranslations('name'),
            'slug' => $this->slug,
            'type' => $this->type,
            'type_label' => $this->type_label,
            'email' => $this->email,
            'phone' => $this->phone,
            'phone_secondary' => $this->phone_secondary,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'description' => $this->description,
            'description_translations' => $this->getTranslations('description'),
            'operating_hours' => $this->operating_hours,
            'is_active' => $this->is_active,
            'license_number' => $this->license_number,
            'tax_number' => $this->tax_number,
            'file_url' => $this->file_url,
            'governorate' => $this->when($this->relationLoaded('governorate') && $this->governorate, function () {
                return [
                    'id' => $this->governorate->id,
                    'name' => $this->governorate->name,
                ];
            }),
            'city' => $this->when($this->relationLoaded('governorateCity') && $this->governorateCity, function () {
                return [
                    'id' => $this->governorateCity->id,
                    'name' => $this->governorateCity->name,
                ];
            }),
            'vendor' => $this->when($this->relationLoaded('vendor') && $this->vendor, function () {
                return [
                    'id' => $this->vendor->id,
                    'name' => $this->vendor->first_name . ' ' . $this->vendor->last_name,
                ];
            }),
            'manager' => $this->when($this->relationLoaded('manager') && $this->manager, function () {
                return [
                    'id' => $this->manager->id,
                    'name' => $this->manager->first_name . ' ' . $this->manager->last_name,
                ];
            }),
            'clinics_count' => $this->when($this->clinics_count !== null, $this->clinics_count),
            'created_at' => $this->created_at ? $this->created_at->toISOString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toISOString() : null,
        ];
    }
}
