<?php

namespace Modules\RadiologyLab\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class TestOrderItemResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'lab_test' => new LabTestResource($this->whenLoaded('labTest')),
            'price' => $this->price,
            'status' => $this->status,
            'result_value' => $this->result_value,
            'result_unit' => $this->result_unit,
            'reference_range' => $this->reference_range,
            'result_status' => $this->result_status,
            'result_notes' => $this->result_notes,
            'result_date' => $this->result_date ? $this->result_date->toISOString() : null,
        ];
    }
}
