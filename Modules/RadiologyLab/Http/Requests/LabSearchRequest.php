<?php

namespace Modules\RadiologyLab\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LabSearchRequest extends FormRequest
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
            'test_name' => 'nullable|string|max:100',
        ];
    }
}
