<?php

namespace Modules\Pharmacy\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class PharmacyProductResource extends JsonResource
{
    public function toArray($request)
    {
        // Use pre-loaded inventories to avoid N+1 queries
        $availableInventories = $this->relationLoaded('inventories')
            ? $this->inventories->where('stock_qty', '>', 0)->where('is_available', true)
            : collect();

        return [
            'id' => $this->id,
            'name' => $this->getNameAttribute(),
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'image' => $this->getFirstMediaUrl('product_image'),
            'product_type' => $this->productType ? [
                'id' => $this->productType->id,
                'name' => $this->productType->getNameAttribute(),
            ] : null,
            'brand' => $this->brand ? [
                'id' => $this->brand->id,
                'name' => $this->brand->name,
            ] : null,
            'price_from' => $availableInventories->min('price'),
            'is_in_stock' => $availableInventories->isNotEmpty(),
            'requires_prescription' => $this->requires_prescription,
        ];
    }
}
