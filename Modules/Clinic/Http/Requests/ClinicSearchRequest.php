<?php

namespace Modules\Clinic\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClinicSearchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'governorate_id' => 'nullable|integer|exists:governorates,id',
            'city_id' => 'nullable|integer|exists:governorate_cities,id',
            'specialty_id' => 'nullable|integer',
            'name' => 'nullable|string|max:100',
            'per_page' => 'nullable|integer|min:1|max:50',
        ];
    }
}
