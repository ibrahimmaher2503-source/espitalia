<?php

namespace Modules\RadiologyLab\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class LabTestResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'slug' => $this->slug,
            'category' => new LabTestCategoryResource($this->whenLoaded('category')),
            'department' => $this->department,
            'sample_type' => $this->sample_type,
            'description' => $this->description,
            'preparation_instructions' => $this->preparation_instructions,
            'default_price' => $this->default_price,
            'turnaround_time' => $this->turnaround_time,
            'status' => $this->status,
        ];
    }
}
