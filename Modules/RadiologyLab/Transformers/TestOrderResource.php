<?php

namespace Modules\RadiologyLab\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class TestOrderResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'patient' => $this->when($this->relationLoaded('patient'), function () {
                return [
                    'id' => $this->patient->id,
                    'name' => ($this->patient->first_name ?? '') . ' ' . ($this->patient->last_name ?? ''),
                    'email' => $this->patient->email,
                ];
            }),
            'doctor' => $this->when($this->relationLoaded('doctor') && $this->doctor, function () {
                return [
                    'id' => $this->doctor->id,
                    'name' => ($this->doctor->first_name ?? '') . ' ' . ($this->doctor->last_name ?? ''),
                ];
            }),
            'lab_technician' => $this->when($this->relationLoaded('labTechnician') && $this->labTechnician, function () {
                return [
                    'id' => $this->labTechnician->id,
                    'name' => ($this->labTechnician->first_name ?? '') . ' ' . ($this->labTechnician->last_name ?? ''),
                ];
            }),
            'items' => TestOrderItemResource::collection($this->whenLoaded('items')),
            'clinical_notes' => $this->clinical_notes,
            'priority' => $this->priority,
            'order_date' => $this->order_date ? $this->order_date->format('Y-m-d') : null,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'total_amount' => $this->total_amount,
            'discount_amount' => $this->discount_amount,
            'final_amount' => $this->final_amount,
            'offer_id' => $this->offer_id,
            'offer_discount' => $this->offer_discount,
            'offer_code' => $this->offer_code,
            'reports' => $this->when($this->relationLoaded('reports'), function () {
                return $this->reports->map(function ($report) {
                    return [
                        'id' => $report->id,
                        'title' => $report->title,
                        'report_type' => $report->report_type,
                        'report_date' => $report->report_date,
                        'status' => $report->status,
                        'file_url' => $report->file_url,
                    ];
                });
            }),
            'created_at' => $this->created_at ? $this->created_at->toISOString() : null,
        ];
    }
}
