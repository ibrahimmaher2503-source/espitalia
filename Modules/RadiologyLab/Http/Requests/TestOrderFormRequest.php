<?php

namespace Modules\RadiologyLab\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TestOrderFormRequest extends FormRequest
{
    public function authorize()
    {
        // Route middleware enforces permission — authorize() stays permissive to respect Gate::before admin override
        return auth()->check();
    }

    public function rules()
    {
        return [
            'patient_id' => 'required|exists:users,id',
            'doctor_id' => 'nullable|exists:users,id',
            'items' => 'required|array|min:1',
            'items.*.lab_test_id' => 'required|exists:lab_tests,id',
            // items.*.price intentionally omitted — server enforces the lab test default_price (see VL-06)
            'priority' => 'nullable|in:routine,urgent,stat',
            'clinical_notes' => 'nullable|string|max:2000',
            'admin_notes' => 'nullable|string|max:2000',
            // discount_amount intentionally omitted — server computes it from offer_code only (see FN-02)
            'offer_code' => 'nullable|string|max:50',
        ];
    }

    public function messages()
    {
        return [
            'patient_id.required' => __('radiology_lab.lbl_patient_name').' '.__('messages.field_is_required'),
            'patient_id.exists' => __('radiology_lab.lbl_patient_name').' '.__('messages.not_found'),
            'items.required' => __('radiology_lab.lbl_select_test').' '.__('messages.field_is_required'),
            'items.min' => __('radiology_lab.lbl_add_test_item'),
            'items.*.lab_test_id.required' => __('radiology_lab.lbl_select_test').' '.__('messages.field_is_required'),
            'items.*.lab_test_id.exists' => __('radiology_lab.lbl_select_test').' '.__('messages.not_found'),
        ];
    }
}
