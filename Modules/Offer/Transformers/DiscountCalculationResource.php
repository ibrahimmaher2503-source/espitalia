<?php

namespace Modules\Offer\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class DiscountCalculationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'original_price' => (float) ($this['original_price'] ?? 0),
            'applied_offers' => $this['discount_breakdown'] ?? [],
            'total_discount' => (float) ($this['discount_amount'] ?? 0),
            'discounted_price' => (float) ($this['discounted_price'] ?? 0),
        ];
    }
}
