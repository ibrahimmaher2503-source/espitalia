<?php

namespace Modules\RequestNurse\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NurseRequestFormRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'patient_id' => 'required|exists:users,id',
            'service_description_ar' => 'nullable|string|max:2000',
            'service_description_en' => 'nullable|string|max:2000',
            'preferred_date' => 'required|date|after_or_equal:today',
            'preferred_time' => 'nullable|date_format:H:i',
            'duration_hours' => 'required|numeric|min:1|max:24',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'contact_number' => 'required|string|max:20',
            'nurse_id' => 'nullable|exists:users,id',
            'patient_notes' => 'nullable|string|max:1000',
            'admin_notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages()
    {
        return [
            'service_description_ar.string' => __('request_nurse.lbl_service_description_ar').' '.__('messages.is_required'),
            'service_description_en.string' => __('request_nurse.lbl_service_description_en').' '.__('messages.is_required'),
            'preferred_date.required' => __('request_nurse.lbl_preferred_date').' '.__('messages.is_required'),
            'preferred_date.after_or_equal' => __('request_nurse.lbl_preferred_date').' must be today or a future date.',
            'address_line_1.required' => __('request_nurse.lbl_address_line_1').' '.__('messages.is_required'),
            'city.required' => __('request_nurse.lbl_city').' '.__('messages.is_required'),
            'contact_number.required' => __('request_nurse.lbl_contact_number').' '.__('messages.is_required'),
        ];
    }
}
