<?php

namespace Modules\DoctorVisit\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class DoctorVisitRequestResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                   => $this->id,
            'reference_number'     => $this->reference_number,
            'visit_type'           => $this->visit_type,
            'visit_reason'         => $this->visit_reason,
            'preferred_date'       => $this->preferred_date,
            'contact_phone'        => $this->contact_phone,
            'additional_notes'     => $this->additional_notes,
            'status'               => $this->status,
            'admin_notes'          => auth()->check() && auth()->user()->hasAnyRole(['admin', 'receptionist', 'doctor'])
                                        ? $this->admin_notes : null,
            'cancellation_reason'  => $this->cancellation_reason,
            'completed_at'         => $this->completed_at?->toIso8601String(),
            'patient'              => $this->patient ? [
                'id'    => $this->patient->id,
                'name'  => $this->patient->full_name,
                'email' => $this->patient->email,
            ] : null,
            'preferred_doctor'     => $this->preferredDoctor ? [
                'id'   => $this->preferredDoctor->id,
                'name' => optional($this->preferredDoctor->user)->full_name,
            ] : null,
            'assigned_doctor'      => $this->assignedDoctor ? [
                'id'   => $this->assignedDoctor->id,
                'name' => optional($this->assignedDoctor->user)->full_name,
            ] : null,
            'status_histories'     => $this->whenLoaded('statusHistories', function () {
                return $this->statusHistories->map(fn ($h) => [
                    'old_status' => $h->old_status,
                    'new_status' => $h->new_status,
                    'changed_by' => optional($h->changedBy)->full_name,
                    'note'       => $h->note,
                    'changed_at' => $h->created_at->toIso8601String(),
                ]);
            }),
            'offer_id'             => $this->offer_id,
            'offer_discount'       => $this->offer_discount,
            'offer_code'           => $this->offer_code,
            'created_at'           => $this->created_at->toIso8601String(),
            'updated_at'           => $this->updated_at->toIso8601String(),
        ];
    }
}
