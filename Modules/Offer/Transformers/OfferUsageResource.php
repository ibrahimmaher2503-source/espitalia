<?php

namespace Modules\Offer\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class OfferUsageResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'offer' => [
                'id' => $this->offer_id,
                'title' => $this->offer?->title ?? '',
                'code' => $this->offer?->code,
            ],
            'user' => [
                'id' => $this->user_id,
                'name' => $this->user ? ($this->user->first_name.' '.$this->user->last_name) : '',
                'email' => $this->user?->email,
            ],
            'booking_type' => $this->booking_type,
            'booking_id' => $this->booking_id,
            'service_id' => $this->service_id,
            'branch_id' => $this->branch_id,
            'original_amount' => (float) $this->original_amount,
            'discount_amount' => (float) $this->discount_amount,
            'final_amount' => (float) $this->final_amount,
            'coupon_code' => $this->coupon_code,
            'status' => $this->status,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
