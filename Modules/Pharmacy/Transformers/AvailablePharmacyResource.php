<?php

namespace Modules\Pharmacy\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class AvailablePharmacyResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'description' => $this->description,
            'description_ar' => $this->description_ar,
            'description_en' => $this->description_en,
            'logo' => $this->getFirstMediaUrl('logo'),
            'phone' => $this->phone,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'subtotal' => $this->subtotal ?? 0,
            'delivery_fee' => $this->delivery_fee,
            'total' => ($this->subtotal ?? 0) + $this->delivery_fee,
        ];
    }
}
