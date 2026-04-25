<?php

namespace Modules\Clinic\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DoctorIndependentServiceFormRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'charges' => 'required|numeric|min:0',
            'duration_min' => 'nullable|integer|min:1',
            'time_slot' => 'nullable|integer|min:5',
            'discount' => 'nullable|in:0,1',
            'discount_type' => 'nullable|in:percentage,fixed',
            'discount_value' => 'nullable|numeric|min:0',
            'is_inclusive_tax' => 'nullable|in:0,1',
            'inclusive_tax' => 'nullable|numeric|min:0',
            'inclusive_tax_price' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:0,1',
        ];

        // Admin can set doctor_id, doctors cannot
        if (auth()->user()->hasAnyRole(['admin', 'demo_admin'])) {
            $rules['doctor_id'] = 'required|exists:users,id';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'name.required' => __('messages.lbl_name') . ' ' . __('messages.field_is_required'),
            'charges.required' => __('messages.lbl_charges') . ' ' . __('messages.field_is_required'),
            'charges.numeric' => __('messages.lbl_charges') . ' ' . __('messages.must_be_number'),
            'doctor_id.required' => __('clinic.lbl_select_doctor') . ' ' . __('messages.field_is_required'),
            'doctor_id.exists' => __('clinic.lbl_select_doctor') . ' ' . __('messages.is_invalid'),
        ];
    }
}
