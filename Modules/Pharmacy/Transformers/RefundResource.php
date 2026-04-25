<?php

namespace Modules\Pharmacy\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class RefundResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'refund_number' => $this->refund_number,
            'order' => $this->whenLoaded('order', fn () => [
                'id' => $this->order->id,
                'status' => $this->order->status,
                'total' => $this->order->total,
            ]),
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->first_name . ' ' . $this->user->last_name,
            ]),
            'reason' => $this->reason,
            'refund_type' => $this->refund_type,
            'refund_amount' => $this->refund_amount,
            'refund_method' => $this->refund_method,
            'status' => $this->status,
            'admin_notes' => auth()->check() && auth()->user()->hasAnyRole(['admin', 'pharmacy'])
                ? $this->admin_notes : null,
            'processor' => $this->whenLoaded('processor', fn () => [
                'id' => $this->processor->id,
                'name' => $this->processor->first_name . ' ' . $this->processor->last_name,
            ]),
            'processed_at' => $this->processed_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
