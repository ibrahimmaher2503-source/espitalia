<?php

namespace Modules\Clinic\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class DoctorIndependentServiceResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'doctor_id' => $this->doctor_id,
            'name' => $this->name,
            'description' => $this->description,
            'duration_min' => $this->duration_min,
            'time_slot' => $this->time_slot,
            'charges' => $this->charges,
            'discount' => $this->discount,
            'discount_type' => $this->discount_type,
            'discount_value' => $this->discount_value,
            'is_inclusive_tax' => $this->is_inclusive_tax,
            'inclusive_tax' => $this->inclusive_tax,
            'inclusive_tax_price' => $this->inclusive_tax_price,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
