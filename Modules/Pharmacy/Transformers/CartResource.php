<?php

namespace Modules\Pharmacy\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray($request)
    {
        $items = $this->items ?? [];

        return [
            'items' => $items->map(function ($item) {
                // Use pre-loaded inventories (eager-loaded with available stock filter)
                $availableInventories = $item->product->inventories ?? collect();
                $minPrice = $availableInventories->min('price');
                $isInStock = $availableInventories->isNotEmpty();

                return [
                    'id' => $item->id,
                    'product' => [
                        'id' => $item->product->id,
                        'name' => $item->product->getNameAttribute(),
                        'image' => filter_var($item->product->getFirstMediaUrl('product_image'), FILTER_VALIDATE_URL) ?: null,
                        'price_from' => $minPrice,
                        'is_in_stock' => $isInStock,
                    ],
                    'quantity' => $item->quantity,
                ];
            })->values(),
            'item_count' => count($items),
        ];
    }
}
