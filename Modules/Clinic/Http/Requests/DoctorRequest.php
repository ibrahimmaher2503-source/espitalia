<?php

namespace Modules\Clinic\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DoctorRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'first_name_en' => 'required_without:first_name_ar|nullable|string|max:255',
            'first_name_ar' => 'required_without:first_name_en|nullable|string|max:255',
            'last_name_en' => 'required_without:last_name_ar|nullable|string|max:255',
            'last_name_ar' => 'required_without:last_name_en|nullable|string|max:255',
            'experience_en' => 'nullable|string|max:255',
            'experience_ar' => 'nullable|string|max:255',
            'description_en' => 'nullable|string|max:255',
            'description_ar' => 'nullable|string|max:255',
            'expert_en' => 'nullable|string|max:255',
            'expert_ar' => 'nullable|string|max:255',
            'about_self_en' => 'nullable|string|max:2000',
            'about_self_ar' => 'nullable|string|max:2000',
            'email' => 'required|string|unique:users,email',
            'password' => [
                'required',
                'string',
                'min:6',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*#?&]/',
            ],
            'confirm_password' => 'required|same:password',
            'mobile' => 'required|string|max:20',
            'profile_image' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:2048',
            'commission_id' => 'required',
            'clinic_id' => 'required',
            'service_id' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'password.regex' => 'Password must contain at least one uppercase / one lowercase / one number and one symbol.',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
