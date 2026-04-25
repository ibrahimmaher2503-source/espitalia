<?php

namespace Modules\Clinic\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class DoctorCallServiceResource extends JsonResource
{
    public function toArray($request)
    {
        $finalPrice = $this->charges;
        if ($this->discount == 1) {
            if ($this->discount_type == 'percentage') {
                $finalPrice = $finalPrice - ($finalPrice * $this->discount_value / 100);
            } else {
                $finalPrice = $finalPrice - $this->discount_value;
            }
        }

        return [
            'id' => $this->id,
            'doctor_id' => $this->doctor_id,
            'name' => $this->name,
            'description' => $this->description,
            'call_type' => $this->call_type,
            'duration_min' => $this->duration_min,
            'time_slot' => $this->time_slot,
            'charges' => $this->charges,
            'discount' => $this->discount,
            'discount_type' => $this->discount_type,
            'discount_value' => $this->discount_value,
            'final_price' => round($finalPrice, 2),
            'is_inclusive_tax' => $this->is_inclusive_tax,
            'inclusive_tax_price' => $this->inclusive_tax_price,
            'status' => $this->status,
        ];
    }
}
