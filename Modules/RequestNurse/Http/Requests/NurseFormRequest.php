<?php

namespace Modules\RequestNurse\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NurseFormRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $userId = $this->user_id ?? null;

        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$userId,
            'mobile' => 'nullable|string|max:20',
            'specialization_ar' => 'nullable|string|max:255',
            'specialization_en' => 'nullable|string|max:255',
            'experience_ar' => 'nullable|string|max:255',
            'experience_en' => 'nullable|string|max:255',
            'about_ar' => 'nullable|string|max:2000',
            'about_en' => 'nullable|string|max:2000',
            'hourly_rate' => 'required|numeric|min:0',
            'service_area_ar' => 'nullable|string|max:255',
            'service_area_en' => 'nullable|string|max:255',
            'availability_status' => 'nullable|in:available,busy,off_duty',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'governorate_id' => 'nullable|exists:governorates,id',
            'city_id' => 'nullable|exists:governorate_cities,id',
        ];

        // Password rules differ for create vs update
        if ($this->isMethod('POST') && ! $this->id) {
            $rules['password'] = 'required|min:6';
        } else {
            $rules['password'] = 'nullable|min:6';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'first_name.required' => __('request_nurse.lbl_first_name').' '.__('messages.is_required'),
            'last_name.required' => __('request_nurse.lbl_last_name').' '.__('messages.is_required'),
            'email.required' => __('request_nurse.lbl_email').' '.__('messages.is_required'),
            'email.unique' => __('request_nurse.lbl_email').' has already been taken.',
            'hourly_rate.required' => __('request_nurse.lbl_hourly_rate').' '.__('messages.is_required'),
            'password.required' => __('request_nurse.lbl_password').' '.__('messages.is_required'),
        ];
    }
}
