<?php

namespace Modules\RadiologyLab\Transformers;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class FacilityBookingResource extends JsonResource
{
    public function toArray($request): array
    {
        $facility = $this->facility;

        return [
            'id' => $this->id,
            'booking_number' => $this->booking_number,
            'type' => $this->type,
            'facility' => $facility ? [
                'id' => $facility->id,
                'name' => $facility->name,
            ] : null,
            'scan_type' => $this->scan_type,
            'patient_name' => $this->patient_name,
            'patient_phone' => $this->patient_phone,
            'booking_date' => $this->booking_date ? $this->booking_date->format('Y-m-d') : null,
            'booking_time' => $this->booking_time ? Carbon::parse($this->booking_time)->format('H:i') : null,
            'status' => $this->status,
            'notes' => $this->notes,
            'admin_notes' => $this->when($this->admin_notes !== null, $this->admin_notes),
            'cancellation_reason' => $this->when($this->cancellation_reason !== null, $this->cancellation_reason),
            'cancelled_by' => $this->when($this->cancelled_by !== null, $this->cancelled_by),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
