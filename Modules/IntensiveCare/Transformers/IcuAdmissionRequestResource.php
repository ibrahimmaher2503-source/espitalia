<?php

namespace Modules\IntensiveCare\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class IcuAdmissionRequestResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'request_number' => $this->request_number,
            'patient_name' => $this->patient_name,
            'patient_age' => $this->patient_age,
            'patient_gender' => $this->patient_gender,
            'hospital' => $this->when($this->relationLoaded('hospital') && $this->hospital, function () {
                return [
                    'id' => $this->hospital->id,
                    'name' => $this->hospital->name,
                    'name_ar' => $this->hospital->name_ar,
                    'name_en' => $this->hospital->name_en,
                ];
            }),
            'department' => $this->when($this->relationLoaded('icuDepartment') && $this->icuDepartment, function () {
                return [
                    'id' => $this->icuDepartment->id,
                    'name' => $this->icuDepartment->name,
                    'name_ar' => $this->icuDepartment->name_ar,
                    'name_en' => $this->icuDepartment->name_en,
                    'specialty_type' => $this->icuDepartment->specialty_type,
                ];
            }),
            'medical_condition' => $this->medical_condition,
            'medical_condition_ar' => $this->medical_condition_ar,
            'medical_condition_en' => $this->medical_condition_en,
            'diagnosis' => $this->diagnosis,
            'diagnosis_ar' => $this->diagnosis_ar,
            'diagnosis_en' => $this->diagnosis_en,
            'case_type' => $this->case_type,
            'urgency' => $this->urgency,
            'needs_ventilator' => $this->needs_ventilator,
            'needs_oxygen' => $this->needs_oxygen,
            'needs_ambulance' => $this->needs_ambulance,
            'contact_name' => $this->contact_name,
            'contact_phone' => $this->contact_phone,
            'relationship_to_patient' => $this->relationship_to_patient,
            'payment_method' => $this->payment_method,
            'insurance_provider' => $this->insurance_provider,
            'status' => $this->status,
            'status_badge' => $this->status_badge,
            'urgency_badge' => $this->urgency_badge,
            'admin_notes' => $this->admin_notes,
            'admin_notes_ar' => $this->admin_notes_ar,
            'admin_notes_en' => $this->admin_notes_en,
            'rejection_reason' => $this->rejection_reason,
            'rejection_reason_ar' => $this->rejection_reason_ar,
            'rejection_reason_en' => $this->rejection_reason_en,
            'response_date' => $this->response_date ? $this->response_date->toISOString() : null,
            'medical_reports' => $this->when($this->relationLoaded('media'), function () {
                return $this->getMedia('medical_reports')->map(function ($media) {
                    return [
                        'id' => $media->id,
                        'name' => $media->file_name,
                        'url' => $media->getUrl(),
                        'size' => $media->size,
                        'mime_type' => $media->mime_type,
                    ];
                });
            }),
            'created_at' => $this->created_at ? $this->created_at->toISOString() : null,
        ];
    }
}
