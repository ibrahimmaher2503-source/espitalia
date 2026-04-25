<?php

namespace Modules\IntensiveCare\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class IcuDepartmentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'hospital' => $this->when($this->relationLoaded('hospital') && $this->hospital, function () {
                return [
                    'id' => $this->hospital->id,
                    'name' => $this->hospital->name,
                ];
            }),
            'name' => $this->name,
            'description' => $this->description,
            'specialty_type' => $this->specialty_type,
            'total_beds' => $this->total_beds,
            'available_beds' => $this->available_beds,
            'has_ventilator' => $this->has_ventilator,
            'has_oxygen' => $this->has_oxygen,
            'equipment_level' => $this->equipment_level,
            'daily_price' => $this->daily_price,
            'status' => $this->status,
        ];
    }
}
