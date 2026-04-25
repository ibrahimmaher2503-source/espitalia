<?php

namespace Modules\Pharmacy\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class PharmacyOrderDetailResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'pharmacy' => $this->pharmacy ? [
                'id' => $this->pharmacy->id,
                'name' => $this->pharmacy->name,
                'phone' => $this->pharmacy->phone,
                'address' => $this->pharmacy->address,
            ] : null,
            'items' => $this->items ? $this->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'line_total' => $item->quantity * $item->price,
                ];
            })->values() : [],
            'subtotal' => $this->subtotal,
            'delivery_fee' => $this->delivery_fee,
            'total' => $this->total,
            'payment_method' => $this->payment_method,
            'address' => $this->address ? [
                'address_line_1' => $this->address->address_line_1,
                'city' => $this->address->city,
            ] : null,
            'coupon_id' => $this->coupon_id,
            'coupon_discount' => $this->coupon_discount,
            'offer_id' => $this->offer_id,
            'offer_discount' => $this->offer_discount,
            'offer_code' => $this->offer_code,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
