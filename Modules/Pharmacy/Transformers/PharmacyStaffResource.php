<?php

namespace Modules\Pharmacy\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class PharmacyStaffResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'pharmacy' => $this->whenLoaded('pharmacy', fn () => [
                'id' => $this->pharmacy->id,
                'name' => $this->pharmacy->name,
            ]),
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'first_name' => $this->user->first_name,
                'last_name' => $this->user->last_name,
                'email' => $this->user->email,
                'profile_image' => $this->user->profile_image,
            ]),
            'role' => $this->role,
            'role_label' => $this->role_label,
            'license_number' => $this->license_number,
            'is_active' => $this->is_active,
            'hired_date' => $this->hired_date?->format('Y-m-d'),
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
