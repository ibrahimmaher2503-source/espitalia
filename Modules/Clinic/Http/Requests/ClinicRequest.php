<?php

namespace Modules\Clinic\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClinicRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $id = request()->id;

        return [
            'name_ar' => 'nullable|string|max:255|required_without:name_en',
            'name_en' => 'nullable|string|max:255|required_without:name_ar',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'email' => 'required|string|unique:clinic,email',
            // 'address' => 'required|string',
            // 'pincode' => 'required',
            // 'contact_number' => 'required|string',
            // 'email' => 'required|email',

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
