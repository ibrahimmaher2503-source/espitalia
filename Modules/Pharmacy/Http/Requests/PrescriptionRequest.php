<?php

namespace Modules\Pharmacy\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PrescriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Patient upload
        if ($this->isMethod('POST') && ! $this->has('status')) {
            return [
                'images' => 'required|array|min:1|max:5',
                'images.*' => 'image|max:5120',
                'doctor_name' => 'nullable|string|max:255',
                'doctor_license_number' => 'nullable|string|max:50',
                'patient_name' => 'nullable|string|max:255',
                'notes' => 'nullable|string|max:2000',
                'pharmacy_id' => 'nullable|exists:pharmacies,id',
            ];
        }

        // Pharmacist review
        return [
            'status' => 'required|in:approved,rejected',
            'rejection_reason' => 'required_if:status,rejected|nullable|string|max:2000',
        ];
    }
}
