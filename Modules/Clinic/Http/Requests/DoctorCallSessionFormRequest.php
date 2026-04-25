<?php

namespace Modules\Clinic\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DoctorCallSessionFormRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'day' => 'required|string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'is_holiday' => 'nullable|boolean',
            'breaks' => 'nullable|array',
            'breaks.*.start_time' => 'required_with:breaks|date_format:H:i',
            'breaks.*.end_time' => 'required_with:breaks|date_format:H:i|after:breaks.*.start_time',
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
            'day.required' => __('clinic.lbl_week_days') . ' ' . __('messages.field_is_required'),
            'day.in' => __('clinic.lbl_week_days') . ' ' . __('messages.is_invalid'),
            'start_time.required' => __('messages.lbl_start_time') . ' ' . __('messages.field_is_required'),
            'end_time.required' => __('messages.lbl_end_time') . ' ' . __('messages.field_is_required'),
            'end_time.after' => __('messages.lbl_end_time') . ' ' . __('messages.must_be_after_start_time'),
            'doctor_id.required' => __('clinic.lbl_select_doctor') . ' ' . __('messages.field_is_required'),
            'doctor_id.exists' => __('clinic.lbl_select_doctor') . ' ' . __('messages.is_invalid'),
        ];
    }
}
