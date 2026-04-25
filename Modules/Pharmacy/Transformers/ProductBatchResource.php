<?php

namespace Modules\Pharmacy\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductBatchResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'inventory_id' => $this->inventory_id,
            'product' => $this->whenLoaded('inventory', fn () => [
                'id' => $this->inventory->product_id,
                'name' => $this->inventory->product?->name,
            ]),
            'batch_number' => $this->batch_number,
            'quantity' => $this->quantity,
            'manufacturing_date' => $this->manufacturing_date?->format('Y-m-d'),
            'expiry_date' => $this->expiry_date->format('Y-m-d'),
            'purchase_price' => auth()->check() && auth()->user()->hasAnyRole(['admin', 'pharmacy'])
                ? $this->purchase_price : null,
            'is_active' => $this->is_active,
            'is_expired' => $this->is_expired,
            'days_until_expiry' => $this->days_until_expiry,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
