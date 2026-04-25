<?php

namespace Modules\Pharmacy\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class CouponResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'title' => $this->title,
            'title_ar' => $this->title_ar,
            'title_en' => $this->title_en,
            'description' => $this->description,
            'description_ar' => $this->description_ar,
            'description_en' => $this->description_en,
            'discount_type' => $this->discount_type,
            'discount_value' => $this->discount_value,
            'max_discount_amount' => $this->max_discount_amount,
            'min_order_amount' => $this->min_order_amount,
            'usage_limit' => $this->usage_limit,
            'usage_per_user' => $this->usage_per_user,
            'used_count' => $this->used_count,
            'starts_at' => $this->starts_at?->toISOString(),
            'expires_at' => $this->expires_at?->toISOString(),
            'is_active' => $this->is_active,
            'pharmacy' => $this->whenLoaded('pharmacy', fn () => [
                'id' => $this->pharmacy->id,
                'name' => $this->pharmacy->name,
            ]),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
