<?php

namespace Modules\RequestNurse\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class NurseRequestResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            // The mobile client needs a stable, human-readable reference but
            // requests predate a persisted reference column.
            'reference_number' => 'NR-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT),
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
            'service_description_ar' => $this->service_description_ar,
            'service_description_en' => $this->service_description_en,
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
            'address_line_1' => $this->address_line_1,
            'address_line_2' => $this->address_line_2,
            'governorate_id' => $this->governorate_id,
            'city_id' => $this->city_id,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'postal_code' => $this->postal_code,
            'contact_number' => $this->contact_number,
            'contact_phone' => $this->contact_number,
            'assigned_nurse' => $this->nurse ? [
                'id' => $this->nurse->id,
                'display_name' => trim($this->nurse->first_name.' '.$this->nurse->last_name),
                'phone' => $this->nurse->mobile,
                'bio' => $this->nurseProfile?->about,
            ] : null,
            'status' => $this->status,
            'payment_status' => $this->payment_status ? 'paid' : 'unpaid',
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
