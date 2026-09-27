<?php

namespace Modules\Specialization\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class SpecializationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'description' => $this->description,
            'description_ar' => $this->description_ar,
            'description_en' => $this->description_en,
            'slug' => $this->slug,
            'parent_id' => $this->parent_id,
            'parent' => $this->whenLoaded('parent', fn () => [
                'id' => $this->parent->id,
                'name' => $this->parent->name,
                'name_ar' => $this->parent->name_ar,
                'name_en' => $this->parent->name_en,
                'slug' => $this->parent->slug,
            ]),
            'icon' => $this->icon,
            'icon_image' => $this->icon_image ? url('storage/'.$this->icon_image) : null,
            'status' => $this->status,
            'show_on_home' => $this->show_on_home,
            'display_order' => $this->display_order,
            'doctors_count' => $this->whenCounted('doctors'),
            'nurses_count' => $this->whenCounted('nurses'),
            'children' => $this->whenLoaded('children', fn () => $this->children->map(fn ($child) => [
                'id' => $child->id,
                'name' => $child->name,
                'name_ar' => $child->name_ar,
                'name_en' => $child->name_en,
                'slug' => $child->slug,
                'doctors_count' => $child->doctors_count ?? null,
                'nurses_count' => $child->nurses_count ?? null,
            ])),
        ];
    }
}
