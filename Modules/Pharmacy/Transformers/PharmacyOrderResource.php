<?php

namespace Modules\Pharmacy\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class PharmacyOrderResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'pharmacy' => $this->pharmacy ? [
                'id' => $this->pharmacy->id,
                'name' => $this->pharmacy->name,
            ] : null,
            'total' => $this->total,
            'status' => $this->status,
            'item_count' => $this->items_count ?? ($this->items ? count($this->items) : 0),
            'offer_id' => $this->offer_id,
            'offer_discount' => $this->offer_discount,
            'offer_code' => $this->offer_code,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
