<?php

namespace Modules\Pharmacy\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryZoneResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'governorate' => $this->whenLoaded('governorate', fn () => [
                'id' => $this->governorate->id,
                'name' => $this->governorate->name,
            ]),
            'city' => $this->whenLoaded('governorateCity', fn () => [
                'id' => $this->governorateCity->id,
                'name' => $this->governorateCity->name,
            ]),
            'delivery_fee' => $this->delivery_fee,
            'min_order_amount' => $this->min_order_amount,
            'estimated_delivery_minutes' => $this->estimated_delivery_minutes,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
