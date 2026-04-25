<?php

namespace Modules\Offer\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class OfferResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title_en' => $this->title_en,
            'title_ar' => $this->title_ar,
            'title' => $this->title,
            'short_label_en' => $this->short_label_en,
            'short_label_ar' => $this->short_label_ar,
            'short_label' => $this->short_label,
            'slug' => $this->slug,
            'internal_name' => $this->internal_name,
            'discount_type' => $this->discount_type,
            'discount_value' => (float) $this->discount_value,
            'max_discount_amount' => $this->max_discount_amount ? (float) $this->max_discount_amount : null,
            'code' => $this->code,
            'requires_coupon' => $this->requires_coupon,
            'auto_apply' => $this->auto_apply,
            'target_scope' => $this->target_scope,
            'starts_at' => $this->starts_at?->toISOString(),
            'ends_at' => $this->ends_at?->toISOString(),
            'is_flash' => $this->is_flash,
            'usage' => [
                'used' => $this->used_count,
                'limit' => $this->usage_limit_total,
            ],
            'status' => $this->status,
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'vendor_id' => $this->vendor_id,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
