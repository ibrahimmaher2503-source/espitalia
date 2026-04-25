<?php

namespace Modules\RequestNurse\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class NurseRequestResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'patient' => $this->patient ? [
                'id' => $this->patient->id,
                'name' => $this->patient->first_name.' '.$this->patient->last_name,
                'email' => $this->patient->email,
                'mobile' => $this->patient->mobile,
            ] : null,
            'nurse' => $this->nurse ? [
                'id' => $this->nurse->id,
                'name' => $this->nurse->first_name.' '.$this->nurse->last_name,
                'specialization' => $this->nurseProfile->specialization ?? null,
            ] : null,
            'service_description' => $this->service_description,
            'request_date' => $this->request_date,
            'preferred_date' => $this->preferred_date ? $this->preferred_date->format('Y-m-d') : null,
            'preferred_time' => $this->preferred_time,
            'duration_hours' => $this->duration_hours,
            'address' => [
                'address_line_1' => $this->address_line_1,
                'address_line_2' => $this->address_line_2,
                'city' => $this->city,
                'state' => $this->state,
                'country' => $this->country,
                'postal_code' => $this->postal_code,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'full_address' => $this->full_address,
            ],
            'contact_number' => $this->contact_number,
            'status' => $this->status,
            'payment_status' => (bool) $this->payment_status,
            'total_amount' => $this->total_amount,
            'patient_notes' => $this->patient_notes,
            'admin_notes' => $this->admin_notes,
            'cancelled_by' => $this->cancelled_by,
            'cancellation_reason' => $this->cancellation_reason,
            'offer_id' => $this->offer_id,
            'offer_discount' => $this->offer_discount,
            'offer_code' => $this->offer_code,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
