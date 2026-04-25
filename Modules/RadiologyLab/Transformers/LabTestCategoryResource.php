<?php

namespace Modules\RadiologyLab\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class LabTestCategoryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'icon' => $this->icon,
            'display_order' => $this->display_order,
            'test_count' => $this->when($this->relationLoaded('labTests'), fn () => $this->labTests->count(), $this->lab_tests_count ?? 0),
            'status' => $this->status,
        ];
    }
}
