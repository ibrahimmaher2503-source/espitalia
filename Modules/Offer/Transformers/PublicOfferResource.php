<?php

namespace Modules\Offer\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class PublicOfferResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'short_label' => $this->short_label,
            'description' => $this->description,
            'discount_type' => $this->discount_type,
            'discount_value' => (float) $this->discount_value,
            'banner_text' => $this->banner_text,
            'starts_at' => $this->starts_at?->toISOString(),
            'ends_at' => $this->ends_at?->toISOString(),
            'is_flash' => $this->is_flash,
            'terms' => $this->terms,
            'banner_image' => $this->getFirstMediaUrl('offer_banners'),
            'services_count' => $this->applies_to_all_services ? null : $this->services_count,
            'branches_count' => $this->applies_to_all_branches ? null : $this->branches_count,
        ];
    }
}
