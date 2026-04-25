<?php

namespace Modules\Pharmacy\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class PrescriptionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'prescription_number' => $this->prescription_number,
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->first_name . ' ' . $this->user->last_name,
            ]),
            'pharmacy' => $this->whenLoaded('pharmacy', fn () => [
                'id' => $this->pharmacy->id,
                'name' => $this->pharmacy->name,
            ]),
            'doctor_name' => $this->doctor_name,
            'doctor_license_number' => $this->doctor_license_number,
            'patient_name' => $this->patient_name,
            'notes' => $this->notes,
            'status' => $this->status,
            'images' => $this->getMedia('prescription_images')->map(fn ($media) => $media->getUrl()),
            'reviewer' => $this->whenLoaded('reviewer', fn () => [
                'id' => $this->reviewer->id,
                'name' => $this->reviewer->first_name . ' ' . $this->reviewer->last_name,
            ]),
            'reviewed_at' => $this->reviewed_at?->toISOString(),
            'rejection_reason' => $this->rejection_reason,
            'order' => $this->whenLoaded('order', fn () => [
                'id' => $this->order->id,
                'status' => $this->order->status,
            ]),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
