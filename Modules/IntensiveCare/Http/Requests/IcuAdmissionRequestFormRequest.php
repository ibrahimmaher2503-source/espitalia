<?php

namespace Modules\IntensiveCare\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IcuAdmissionRequestFormRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'hospital_id' => 'required|exists:hospitals,id',
            'icu_department_id' => 'nullable|exists:icu_departments,id,hospital_id,'.$this->input('hospital_id'),
            'patient_name' => 'required|string|max:255',
            'patient_age' => 'required|integer|min:0|max:150',
            'patient_gender' => 'required|in:male,female',
            'national_id' => 'nullable|string|max:50',
            'insurance_number' => 'nullable|required_if:payment_method,insurance|string|max:100',
            'medical_condition_ar' => 'nullable|string|max:5000|required_without:medical_condition_en',
            'medical_condition_en' => 'nullable|string|max:5000|required_without:medical_condition_ar',
            'diagnosis_ar' => 'nullable|string|max:255',
            'diagnosis_en' => 'nullable|string|max:255',
            'case_type' => 'required|in:stroke,cardiac,post_operative,ventilator,neonatal,pediatric,burns,general',
            'urgency' => 'required|in:critical,urgent,standard',
            'needs_ventilator' => 'nullable|boolean',
            'needs_oxygen' => 'nullable|boolean',
            'current_location' => 'nullable|string|max:500',
            'needs_ambulance' => 'nullable|boolean',
            'contact_name' => 'required|string|max:255',
            'contact_phone' => 'required|string|max:50',
            'relationship_to_patient' => 'nullable|string|max:100',
            'payment_method' => 'required|in:insurance,cash',
            'insurance_provider' => 'nullable|required_if:payment_method,insurance|string|max:255',
            'medical_reports.*' => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
            'admin_notes_ar' => 'nullable|string',
            'admin_notes_en' => 'nullable|string',
            'rejection_reason_ar' => 'nullable|string',
            'rejection_reason_en' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'hospital_id.required' => __('intensive_care.lbl_hospital_name').' '.__('messages.field_is_required'),
            'hospital_id.exists' => __('intensive_care.lbl_hospital_name').' '.__('messages.is_invalid'),
            'patient_name.required' => __('intensive_care.lbl_patient_name').' '.__('messages.field_is_required'),
            'patient_age.required' => __('intensive_care.lbl_patient_age').' '.__('messages.field_is_required'),
            'patient_gender.required' => __('intensive_care.lbl_patient_gender').' '.__('messages.field_is_required'),
            'medical_condition_ar.required_without' => __('intensive_care.lbl_medical_condition_ar').' '.__('messages.field_is_required'),
            'medical_condition_en.required_without' => __('intensive_care.lbl_medical_condition_en').' '.__('messages.field_is_required'),
            'case_type.required' => __('intensive_care.lbl_case_type').' '.__('messages.field_is_required'),
            'case_type.in' => __('intensive_care.lbl_case_type').' '.__('messages.is_invalid'),
            'urgency.required' => __('intensive_care.lbl_urgency').' '.__('messages.field_is_required'),
            'urgency.in' => __('intensive_care.lbl_urgency').' '.__('messages.is_invalid'),
            'contact_name.required' => __('intensive_care.lbl_contact_name').' '.__('messages.field_is_required'),
            'contact_phone.required' => __('intensive_care.lbl_contact_phone').' '.__('messages.field_is_required'),
            'payment_method.required' => __('intensive_care.lbl_payment_method').' '.__('messages.field_is_required'),
            'payment_method.in' => __('intensive_care.lbl_payment_method').' '.__('messages.is_invalid'),
            'insurance_number.required_if' => __('intensive_care.lbl_insurance_number').' '.__('messages.field_is_required'),
            'insurance_provider.required_if' => __('intensive_care.lbl_insurance_provider').' '.__('messages.field_is_required'),
        ];
    }
}
